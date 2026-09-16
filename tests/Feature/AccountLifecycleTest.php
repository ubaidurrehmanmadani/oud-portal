<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AccountLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_suspend_and_restore_without_erasing_history(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user = User::factory()->create();
        $url = route('accounts.lifecycle', $user);
        $this->actingAs($admin)->post($url, ['action' => 'suspend', 'current_password' => 'password'])->assertSessionHasNoErrors();
        $this->assertNotNull($user->fresh()->suspended_at);
        $this->assertDatabaseHas('audit_events', ['user_id' => $admin->id, 'event' => 'account.suspend:'.$user->id]);
        $this->actingAs($user->fresh())->get('/dashboard/employee')->assertForbidden();
        $this->actingAs($admin)->post($url, ['action' => 'restore', 'current_password' => 'password'])->assertSessionHasNoErrors();
        $this->assertNull($user->fresh()->suspended_at);
        $this->actingAs($user->fresh())->get('/dashboard/employee')->assertRedirect(route('login'));
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasNoErrors();
        $this->get('/dashboard/employee')->assertOk();
    }

    public function test_lifecycle_requires_admin_password_and_prevents_self_suspension(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('accounts.lifecycle', $admin), ['action' => 'suspend', 'current_password' => 'password'])->assertForbidden();
        $this->actingAs($admin)->post(route('accounts.lifecycle', $user), ['action' => 'suspend', 'current_password' => 'wrong'])->assertSessionHasErrors('current_password');
        $this->actingAs($admin)->post(route('accounts.lifecycle', $admin), ['action' => 'suspend', 'current_password' => 'password'])->assertUnprocessable();
        $this->assertDatabaseCount('audit_events', 0);
        $this->assertNull($user->fresh()->suspended_at);
    }

    public function test_restore_does_not_bypass_pending_approval_and_duplicate_actions_conflict(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user = User::factory()->create(['approval_status' => 'pending', 'suspended_at' => now()]);
        $this->actingAs($admin)->post(route('accounts.lifecycle', $user), ['action' => 'restore', 'current_password' => 'password'])->assertSessionHasNoErrors();
        $this->assertSame('pending', $user->fresh()->approval_status);
        $this->actingAs($admin)->post(route('accounts.lifecycle', $user), ['action' => 'restore', 'current_password' => 'password'])->assertConflict();
        $this->actingAs($user->fresh())->get('/dashboard/employee')->assertForbidden();
    }

    public function test_admin_reset_is_queued_audited_and_does_not_set_or_expose_password(): void
    {
        Queue::fake();
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user = User::factory()->create();
        $old = $user->password;
        $this->actingAs($admin)->post(route('accounts.lifecycle', $user), ['action' => 'reset_password', 'current_password' => 'password'])->assertSessionHasNoErrors();
        Queue::assertPushed(SendQueuedNotifications::class, fn ($job) => $job->notifiables->first()->is($user) && $job->queue === 'notifications' && $job->shouldBeEncrypted);
        $this->assertSame($old, $user->fresh()->password);
        $this->assertDatabaseHas('audit_events', ['event' => 'account.reset_password:'.$user->id]);
        $this->assertStringNotContainsString($old, AuditEvent::first()->toJson());
    }

    public function test_suspended_user_cannot_login_with_valid_password(): void
    {
        $user = User::factory()->create(['suspended_at' => now()]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_setup_creations_are_audited_with_hashed_password_and_relations(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        foreach ([['kind' => 'department', 'name' => 'Operations'], ['kind' => 'property', 'name' => 'Property A', 'total_units' => 3], ['kind' => 'user', 'name' => 'New user', 'email' => 'new@example.com', 'role' => 'employee', 'password' => 'New-Password123']] as $data) {
            $this->actingAs($admin)->post('/content', $data)->assertSessionHasNoErrors();
        }
        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('New-Password123', $user->password));
        $this->assertNotNull($user->profile);
        $this->assertNotNull($user->accountRole);
        $this->assertDatabaseCount('audit_events', 3);
        $this->assertDatabaseHas('audit_events', ['event' => 'account.user.created:'.$user->id]);
        $this->assertStringNotContainsString('New-Password123', AuditEvent::all()->toJson());
    }

    public function test_admin_can_inspect_change_audit_and_non_admin_cannot(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user = User::factory()->create();
        AuditEvent::create(['user_id' => $admin->id, 'event' => 'account.user.updated:'.$user->id, 'changes' => ['before' => ['name' => 'Original'], 'after' => ['name' => 'Updated']]]);
        $this->actingAs($admin)->get('/admin/audit-logs/view-audit-logs?source=changes')->assertOk()->assertSee('Original')->assertSee('Updated');
        $this->actingAs($user)->get('/admin/audit-logs/view-audit-logs?source=changes')->assertForbidden();
    }

    public function test_lifecycle_form_is_available_in_both_languages(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user = User::factory()->create();
        foreach (['en' => 'Account access and recovery', 'ar' => 'الوصول إلى الحساب واستعادته'] as $locale => $label) {
            $this->withSession(['locale' => $locale])->actingAs($admin)->get(route('accounts.edit', ['kind' => 'user', 'id' => $user->id]))->assertOk()->assertSee($label)->assertSee(route('accounts.lifecycle', $user));
        }
    }
}
