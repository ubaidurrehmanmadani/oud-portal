<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_credentials_can_be_disabled_in_production(): void
    {
        $this->app->instance('env', 'production');
        config(['demo-access.enabled' => false]);
        $response = $this->get('/login')->assertOk()->assertDontSee('data-demo-access', false);
        foreach (config('demo-access.accounts') as $email) {
            $response->assertDontSee($email);
        }
        $response->assertDontSee(config('demo-access.password'));
    }

    public function test_production_login_shows_seeded_credentials_by_default(): void
    {
        $this->app->instance('env', 'production');
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();
        $response = $this->get('/login')->assertOk()
            ->assertSee('data-demo-access', false)
            ->assertSee('Test#12345')
            ->assertDontSee('Account not ready')
            ->assertDontSee('Local testing only');
        foreach (['admin@gmail.com', 'manager@gmail.com', 'landlord@gmail.com', 'employee@gmail.com'] as $email) {
            $response->assertSee($email);
        }
        $this->withSession(['locale' => 'ar'])->get('/login')
            ->assertSee('الدخول التجريبي')->assertDontSee('portal.demo_');
    }

    public function test_local_demo_box_checks_roles_passwords_and_approval(): void
    {
        $this->app->instance('env', 'local');
        foreach (config('demo-access.accounts') as $role => $email) {
            User::factory()->create(['email' => $email, 'role' => $role, 'password' => config('demo-access.password')]);
        }
        $response = $this->get('/login')->assertOk()->assertSee('data-demo-access', false)->assertDontSee('Account not ready');
        foreach (config('demo-access.accounts') as $email) {
            $response->assertSee($email);
        }
        User::where('email', config('demo-access.accounts.admin'))->update(['approval_status' => 'pending']);
        $this->get('/login')->assertSee('Account not ready');
        $this->withSession(['locale' => 'ar'])->get('/login')->assertSee('الدخول التجريبي')->assertDontSee('portal.demo_');
    }
}
