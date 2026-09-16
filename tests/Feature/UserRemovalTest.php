<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditEvent;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserRemovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_unused_suspended_user_can_be_removed_with_tokens_and_profile(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user = User::factory()->create(['suspended_at' => now()]);
        $user->profile()->create(['preferred_locale' => 'en', 'timezone' => 'Asia/Riyadh']);
        DB::table('password_reset_tokens')->insert(['email' => $user->email, 'token' => 'test-hashed-token', 'created_at' => now()]);
        $this->actingAs($admin)->post(route('accounts.lifecycle', $user), ['action' => 'delete', 'current_password' => 'password', 'confirm_delete' => 1])->assertRedirect(route('admin.users.view'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('user_profiles', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
        $this->assertDatabaseHas('audit_events', ['user_id' => $admin->id, 'event' => 'account.delete:'.$user->id]);
    }

    public function test_removal_requires_admin_password_confirmation_and_suspension(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user = User::factory()->create();
        $payload = ['action' => 'delete', 'current_password' => 'password', 'confirm_delete' => 1];
        $this->actingAs($user)->post(route('accounts.lifecycle', $admin), $payload)->assertForbidden();
        $this->actingAs($admin)->post(route('accounts.lifecycle', $user), $payload)->assertConflict();
        $user->forceFill(['suspended_at' => now()])->save();
        $this->post(route('accounts.lifecycle', $user), array_replace($payload, ['current_password' => 'wrong']))->assertSessionHasErrors('current_password');
        $this->post(route('accounts.lifecycle', $user), array_replace($payload, ['confirm_delete' => 0]))->assertSessionHasErrors('confirm_delete');
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_activity_history_is_preserved_and_delete_form_is_bilingual(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user = User::factory()->create(['suspended_at' => now()]);
        $user->loginEvents()->create(['event' => 'login']);
        $this->actingAs($admin)->post(route('accounts.lifecycle', $user), ['action' => 'delete', 'current_password' => 'password', 'confirm_delete' => 1])->assertConflict();
        $this->assertDatabaseHas('users', ['id' => $user->id]);
        $this->assertDatabaseCount('login_events', 1);
        foreach (['en' => 'Delete unused account', 'ar' => 'حذف الحساب غير المستخدم'] as $locale => $label) {
            $this->withSession(['locale' => $locale])->get(route('accounts.edit', ['kind' => 'user', 'id' => $user->id]))->assertOk()->assertSee($label);
        }
    }

    public function test_assignments_and_account_approval_decisions_block_removal(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $department = Department::create(['name' => 'Retained']);
        foreach ([['department_id' => $department->id], ['approved_by' => $admin->id, 'approval_decided_at' => now()]] as $attributes) {
            $user = User::factory()->create($attributes + ['suspended_at' => now()]);
            $this->actingAs($admin)->post(route('accounts.lifecycle', $user), ['action' => 'delete', 'current_password' => 'password', 'confirm_delete' => 1])->assertConflict();
            $this->assertDatabaseHas('users', ['id' => $user->id]);
        }
    }

    public function test_audit_failure_rolls_back_removal(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $user = User::factory()->create(['suspended_at' => now()]);
        AuditEvent::creating(fn () => throw new \RuntimeException('Audit unavailable'));
        $this->withoutExceptionHandling();
        try {
            $this->actingAs($admin)->post(route('accounts.lifecycle', $user), ['action' => 'delete', 'current_password' => 'password', 'confirm_delete' => 1]);
            $this->fail('Expected failure');
        } catch (\RuntimeException $error) {
            $this->assertSame('Audit unavailable', $error->getMessage());
        } finally {
            AuditEvent::flushEventListeners();
        }
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
