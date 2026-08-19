<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\LoginEvent;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_screens_can_be_rendered(): void
    {
        $this->get('/login')->assertOk()->assertSee('Login to your account');
        $this->get('/sign-up')->assertOk()->assertSee('Sign up');
        $this->get('/forgot-password')->assertOk()->assertSee('Forgot password');
    }

    public function test_users_can_register_and_are_redirected_to_their_role_dashboard(): void
    {
        $response = $this->post('/sign-up', [
            'name' => 'Property Owner',
            'email' => 'owner@example.com',
            'role' => UserRole::LANDLORD->value,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'owner@example.com')->first();

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertNotNull($user);
        $this->assertAuthenticatedAs($user);
        $this->assertSame(UserRole::LANDLORD, $user->role);
        $this->assertSame(UserRole::LANDLORD, $user->accountRole->code);
        $this->assertNotNull($user->profile);
        $this->assertTrue(LoginEvent::where('user_id', $user->id)->where('event', 'registered')->exists());
        $response->assertRedirect(route('dashboard.landlord'));
    }

    public function test_users_can_login_and_are_redirected_by_role(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => UserRole::ADMIN,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(LoginEvent::where('user_id', $user->id)->where('event', 'login')->exists());
        $this->assertNotNull($user->fresh()->last_login_at);
        $response->assertRedirect(route('dashboard.admin', absolute: false));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $this->assertTrue(LoginEvent::where('user_id', $user->id)->where('event', 'logout')->exists());
        $response->assertRedirect(route('login'));
    }

    public function test_password_reset_links_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'employee@example.com',
        ]);

        $this->post('/forgot-password', [
            'email' => 'employee@example.com',
        ])->assertSessionHasNoErrors();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_users_can_switch_the_interface_language(): void
    {
        $this->post('/language/ar')
            ->assertRedirect()
            ->assertSessionHas('locale', 'ar');

        $this->withSession(['locale' => 'ar'])
            ->get('/login')
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('تسجيل الدخول إلى حسابك');
    }

    public function test_login_system_roles_are_seeded_by_migration(): void
    {
        foreach (UserRole::cases() as $role) {
            $this->assertTrue(
                Role::where('code', $role)->exists(),
                "Expected {$role->value} role to exist."
            );
        }
    }

    public function test_authenticated_dashboard_renders_professional_shell(): void
    {
        $user = User::factory()->create([
            'name' => 'OUD Admin',
            'email' => 'oud-admin@example.com',
            'role' => UserRole::ADMIN,
        ]);

        $this->actingAs($user)
            ->get('/dashboard/admin')
            ->assertOk()
            ->assertSee('dashboard-sidebar')
            ->assertSee('mobile-menu')
            ->assertSee('profile-menu')
            ->assertSee('Profile settings')
            ->assertSee('Search documents, reports, approvals')
            ->assertSee('Admin Dashboard');
    }

    public function test_authenticated_dashboard_renders_arabic_translations(): void
    {
        $user = User::factory()->create([
            'name' => 'OUD Admin',
            'email' => 'oud-admin@example.com',
            'role' => UserRole::ADMIN,
        ]);

        $this->withSession(['locale' => 'ar'])
            ->actingAs($user)
            ->get('/dashboard/admin')
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('لوحة تحكم المشرف')
            ->assertSee('إعدادات الملف الشخصي')
            ->assertSee('البحث في المستندات والتقارير والموافقات');
    }
}
