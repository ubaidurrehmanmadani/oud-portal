<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_privileged_requests_require_approval_before_login(): void
    {
        foreach (['admin', 'department_manager'] as $role) {
            $this->post('/sign-up', ['name' => 'Applicant', 'email' => $role.'@example.com', 'role' => $role, 'password' => 'password123', 'password_confirmation' => 'password123', 'approval_status' => 'approved', 'can_submit_financial_reports' => true])->assertRedirect(route('login'));
            $this->assertGuest();
            $account = User::where('email', $role.'@example.com')->firstOrFail();
            $this->assertSame('pending', $account->approval_status);
            $this->assertFalse($account->can_submit_financial_reports);
            $this->post('/login', ['email' => $account->email, 'password' => 'password123'])->assertSessionHasErrors('email');
            $this->assertGuest();
        }
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $account = User::where('email', 'admin@example.com')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.account-requests.index'))->assertOk()->assertSee($account->email);
        $this->post(route('admin.account-requests.decide', $account), ['decision' => 'approved'])->assertSessionHasNoErrors();
        $this->assertSame('approved', $account->fresh()->approval_status);
        $this->assertEquals($admin->id, $account->fresh()->approved_by);
        $this->post(route('admin.account-requests.decide', $account), ['decision' => 'rejected'])->assertStatus(409);
        $this->post('/logout');
        $this->post('/login', ['email' => $account->email, 'password' => 'password123'])->assertRedirect(route('dashboard.admin', absolute: false));
        $this->assertAuthenticatedAs($account);
    }

    public function test_pending_sessions_and_non_admins_cannot_approve_accounts(): void
    {
        $pending = User::factory()->create(['role' => UserRole::ADMIN]);
        $pending->forceFill(['approval_status' => 'pending'])->save();
        $this->actingAs($pending)->get('/dashboard/admin')->assertForbidden();
        $this->get('/content')->assertForbidden();
        $this->get(route('admin.account-requests.index'))->assertForbidden();
        $this->post(route('admin.account-requests.decide', $pending), ['decision' => 'approved'])->assertForbidden();
        foreach ([UserRole::EMPLOYEE, UserRole::LANDLORD, UserRole::DEPARTMENT_MANAGER] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))->post(route('admin.account-requests.decide', $pending), ['decision' => 'approved'])->assertForbidden();
        }
        $this->assertSame('pending', $pending->fresh()->approval_status);
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $this->actingAs($admin)->post(route('admin.account-requests.decide', $pending), ['decision' => 'rejected'])->assertSessionHasNoErrors();
        $this->assertSame('rejected', $pending->fresh()->approval_status);
        $this->post('/logout');
        $this->post('/login', ['email' => $pending->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_registration_and_review_screens_are_bilingual(): void
    {
        foreach (['en', 'ar'] as $locale) {
            $this->withSession(['locale' => $locale])->get('/sign-up')->assertOk()->assertSee('value="admin"', false)->assertSee('value="department_manager"', false)->assertDontSee('portal.account_');
        }
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        foreach (['en', 'ar'] as $locale) {
            $this->actingAs($admin)->withSession(['locale' => $locale])->get(route('admin.account-requests.index'))->assertOk()->assertDontSee('portal.account_');
        }
    }
}
