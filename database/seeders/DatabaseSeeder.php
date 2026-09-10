<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Production deployments omit Faker and other development dependencies.
        // Seed application reference data without creating demo login accounts.
        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(
                ['code' => $role->value],
                ['name' => $role->label(), 'description' => $role->label().' portal access role.'],
            );
        }
    }
}
