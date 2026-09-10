<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->assertDatabaseCount('users', 3);
        $this->assertSame($original, $user->fresh()->getAttributes());
        $this->assertDatabaseCount('roles', count(UserRole::cases()));
        $this->assertDatabaseHas('users', ['email' => 'ubaid+landlord@gmail.com', 'role' => UserRole::LANDLORD->value]);
        $this->assertDatabaseHas('users', ['email' => 'ubaid+employee@gmail.com', 'role' => UserRole::EMPLOYEE->value]);
        $this->assertSame('Customized employee role', $role->fresh()->name);
    }

    public function test_seeding_creates_the_demo_accounts(): void
    {
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('roles', count(UserRole::cases()));
    }

    public function test_existing_landlords_receive_the_reference_workspace_data(): void
    {
        Storage::fake('local');
        $landlord = User::factory()->create(['role' => UserRole::LANDLORD]);

        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();

        $this->assertCount(3, $landlord->fresh()->properties);
        $this->assertDatabaseHas('workspace_items', ['kind' => 'report', 'title' => 'Monthly performance report']);
        $this->assertDatabaseHas('workspace_items', ['kind' => 'document', 'title' => 'OUD Reserve lease register']);
        $this->assertDatabaseCount('workspace_items', 26);
        Storage::disk('local')->assertExists('workspace/demo/oud-reserve-lease-register.pdf');
    }
}
