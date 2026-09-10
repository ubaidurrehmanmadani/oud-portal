<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->assertDatabaseCount('users', 1);
        $this->assertSame($original, $user->fresh()->getAttributes());
        $this->assertDatabaseCount('roles', count(UserRole::cases()));
        $this->assertSame('Customized employee role', $role->fresh()->name);
    }

    public function test_seeding_does_not_create_demo_accounts(): void
    {
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('roles', count(UserRole::cases()));
    }
}
