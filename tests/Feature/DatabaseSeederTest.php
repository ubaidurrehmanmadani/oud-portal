<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_seeding_is_repeatable_and_preserves_accounts(): void
    {
        $user = User::factory()->create();
        $original = $user->fresh()->getAttributes();
        $role = Role::where('code', UserRole::EMPLOYEE->value)->firstOrFail();
        $role->update(['name' => 'Customized employee role']);
        $this->app['env'] = 'production';

        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();

        $this->assertDatabaseCount('users', 7);
        $this->assertSame($original, $user->fresh()->getAttributes());
        $this->assertDatabaseCount('roles', count(UserRole::cases()));
        $this->assertDatabaseHas('users', ['email' => 'ubaid+landlord@gmail.com', 'role' => UserRole::LANDLORD->value]);
        $this->assertDatabaseHas('users', ['email' => 'ubaid+employee@gmail.com', 'role' => UserRole::EMPLOYEE->value]);
        $this->assertSame('Customized employee role', $role->fresh()->name);
    }

    public function test_seeding_creates_the_demo_accounts(): void
    {
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();

        $this->assertDatabaseCount('users', 6);
        $this->assertDatabaseCount('roles', count(UserRole::cases()));
    }

    public function test_deployment_accounts_can_login_and_reseeding_preserves_password_changes(): void
    {
        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);

        foreach ([
            'admin@gmail.com' => UserRole::ADMIN,
            'manager@gmail.com' => UserRole::DEPARTMENT_MANAGER,
            'landlord@gmail.com' => UserRole::LANDLORD,
            'employee@gmail.com' => UserRole::EMPLOYEE,
        ] as $email => $role) {
            $user = User::where('email', $email)->firstOrFail();
            $this->assertSame($role, $user->role);
            $this->assertSame($role, $user->accountRole->code);
            $this->assertSame('approved', $user->approval_status);
            $this->assertNotNull($user->profile);
            $this->assertTrue(Hash::check('Test#12345', $user->password));
            $this->post('/login', ['email' => $email, 'password' => 'Test#12345'])
                ->assertRedirect(route($role->dashboardRouteName(), absolute: false));
            $this->assertAuthenticatedAs($user);
            $this->post('/logout');
            $user->update(['password' => 'Changed#67890']);
        }

        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('users', 6);
        foreach (['admin', 'manager', 'landlord', 'employee'] as $name) {
            $this->assertTrue(Hash::check('Changed#67890', User::where('email', $name.'@gmail.com')->firstOrFail()->password));
        }
    }

    public function test_existing_landlords_receive_the_reference_workspace_data(): void
    {
        Storage::fake('local');
        $landlord = User::factory()->create(['role' => UserRole::LANDLORD]);

        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();

        $this->assertCount(5, $landlord->fresh()->properties);
        $this->assertDatabaseHas('workspace_items', ['kind' => 'report', 'title' => 'Monthly performance report']);
        $this->assertDatabaseHas('workspace_items', ['kind' => 'document', 'title' => 'OUD Reserve lease register']);
        $this->assertDatabaseCount('workspace_items', 86);
        Storage::disk('local')->assertExists('workspace/demo/oud-reserve-lease-register.pdf');
    }
}
