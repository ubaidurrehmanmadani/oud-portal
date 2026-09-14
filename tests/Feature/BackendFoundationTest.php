<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AuditEvent;
use App\Models\User;
use App\Notifications\QueuedResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BackendFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_attempts_are_limited_and_recover_after_window(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'blocked@example.com', 'password' => 'incorrect'])->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => 'blocked@example.com', 'password' => 'incorrect'])->assertStatus(429);
        $this->travel(61)->seconds();
        $this->post('/login', ['email' => 'blocked@example.com', 'password' => 'incorrect'])->assertStatus(302);
    }

    public function test_malformed_email_is_validated_without_breaking_rate_limiter(): void
    {
        $this->postJson('/login', ['email' => ['invalid'], 'password' => 'incorrect'])
            ->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_ip_limit_blocks_rotating_email_addresses(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->post('/login', ['email' => "attempt{$i}@example.com", 'password' => 'incorrect'])->assertStatus(302);
        }
        $this->post('/login', ['email' => 'another@example.com', 'password' => 'incorrect'])->assertStatus(429);
    }

    public function test_failed_audit_rolls_back_account_edit(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $employee = User::factory()->create(['name' => 'Original']);
        AuditEvent::creating(function () {
            throw new \RuntimeException('Simulated audit storage failure');
        });
        $this->withoutExceptionHandling();
        try {
            $this->actingAs($admin)->put(route('accounts.update', ['kind' => 'user', 'id' => $employee->id]), [
                'name' => 'Changed', 'email' => $employee->email, 'role' => 'employee',
            ]);
            $this->fail('Expected audit failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Simulated audit storage failure', $exception->getMessage());
        } finally {
            AuditEvent::flushEventListeners();
        }
        $this->assertSame('Original', $employee->fresh()->name);
        $this->assertDatabaseCount('audit_events', 0);
    }

    public function test_reset_responses_do_not_reveal_account_existence_and_mail_is_queued(): void
    {
        Queue::fake();
        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHasNoErrors();
        $message = session('status');
        $this->post('/forgot-password', ['email' => 'absent@example.com'])->assertSessionHasNoErrors()->assertSessionHas('status', $message);
        Queue::assertPushed(SendQueuedNotifications::class, function ($job) use ($user) {
            return $job->notification instanceof QueuedResetPassword
                && $job->notifiables->first()->is($user)
                && $job->queue === 'notifications'
                && $job->shouldBeEncrypted
                && $job->afterCommit
                && $job->tries === 3;
        });
        Queue::assertPushed(SendQueuedNotifications::class, 1);
    }

    public function test_admin_changes_are_audited_and_other_roles_cannot_change_accounts(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $data = ['name' => 'Updated Employee', 'email' => $employee->email, 'role' => 'employee'];
        $this->actingAs($employee)->put(route('accounts.update', ['kind' => 'user', 'id' => $employee->id]), $data)->assertForbidden();
        $this->assertDatabaseCount('audit_events', 0);
        $this->actingAs($admin)->put(route('accounts.update', ['kind' => 'user', 'id' => $employee->id]), $data)->assertSessionHasNoErrors();
        $this->assertSame('Updated Employee', $employee->fresh()->name);
        $this->assertDatabaseHas('audit_events', ['user_id' => $admin->id, 'event' => 'account.user.updated:'.$employee->id]);
        $this->assertStringNotContainsString($employee->password, AuditEvent::first()->toJson());
    }
}
