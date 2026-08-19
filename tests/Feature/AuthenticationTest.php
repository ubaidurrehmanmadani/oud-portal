<?php

namespace Tests\Feature;

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
            'role' => User::ROLE_LANDLORD,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'owner@example.com')->first();

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $this->assertNotNull($user);
        $this->assertAuthenticatedAs($user);
        $this->assertSame(User::ROLE_LANDLORD, $user->role);
        $response->assertRedirect(route('dashboard.landlord'));
    }

    public function test_users_can_login_and_are_redirected_by_role(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard.admin', absolute: false));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
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
}
