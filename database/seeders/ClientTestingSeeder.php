<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientTestingSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            if (! DB::table('seed_checkpoints')->insertOrIgnore(['name' => 'client-testing-v1', 'completed_at' => now()])) {
                return;
            }
            $departments = collect(['Property Management', 'Hospitality Management', 'HR & Shared Services', 'Investment Management'])
                ->mapWithKeys(fn ($name) => [$name => Department::firstOrCreate(['name' => $name])]);
            $properties = collect(['OUD Reserve' => 54, 'OUD Square' => 38, 'OUD Dunes' => 26, 'La Perle East' => 0, 'La Perle West' => 0])
                ->map(fn ($units, $name) => Property::firstOrCreate(['name' => $name], ['location' => 'Riyadh, Saudi Arabia', 'type' => 'Mixed use', 'total_units' => $units]));

            // Correct an older deployment's spelling without creating a duplicate account.
            $old = User::where('email', 'ubaid+propert_manager@gmail.com')->lockForUpdate()->first();
            if ($old && ! User::where('email', 'ubaid+property_manager@gmail.com')->exists()) {
                $old->update(['email' => 'ubaid+property_manager@gmail.com']);
            }
            $accounts = [
                ['Ubaid Property Manager', 'ubaid+property_manager@gmail.com', UserRole::DEPARTMENT_MANAGER, 'Property Management', ['OUD Reserve', 'OUD Square'], true],
                ['Manager', 'manager@gmail.com', UserRole::DEPARTMENT_MANAGER, 'Hospitality Management', ['OUD Dunes'], false],
                ['Ubaid Employee', 'ubaid+employee@gmail.com', UserRole::EMPLOYEE, 'Property Management', [], false],
                ['Employee', 'employee@gmail.com', UserRole::EMPLOYEE, 'Hospitality Management', [], false],
                ['Ubaid Landlord', 'ubaid+landlord@gmail.com', UserRole::LANDLORD, null, $properties->keys()->all(), false],
                ['Saad Landlord', 'saad+landlord@gmail.com', UserRole::LANDLORD, null, $properties->keys()->all(), false],
                ['Landlord', 'landlord@gmail.com', UserRole::LANDLORD, null, $properties->keys()->all(), false],
                ['TEST Restricted Landlord', 'ubaid+restricted_landlord@gmail.com', UserRole::LANDLORD, null, ['OUD Reserve'], false],
            ];
            foreach ($accounts as [$name, $email, $role, $department, $assigned, $delegate]) {
                $user = User::firstOrCreate(['email' => $email], [
                    'name' => $name, 'role' => $role, 'role_id' => Role::where('code', $role->value)->value('id'),
                    'password' => env('CLIENT_TEST_PASSWORD', 'Test#12345'), 'approval_status' => 'approved', 'email_verified_at' => now(),
                ]);
                // Do not reinterpret an existing account that has a different role.
                if ($user->role !== $role) {
                    continue;
                }
                $user->profile()->firstOrCreate([], ['preferred_locale' => 'en', 'timezone' => 'Asia/Riyadh']);
                if ($department && ! $user->department_id) {
                    $user->department_id = $departments[$department]->id;
                }
                if ($role === UserRole::DEPARTMENT_MANAGER && $user->department_id === $departments[$department]->id) {
                    $user->can_submit_financial_reports = true;
                    if ($delegate && ! array_key_exists('manage_employees', $user->permission_overrides ?? [])) {
                        $user->permission_overrides = ($user->permission_overrides ?? []) + ['manage_employees' => true];
                    }
                }
                $user->save();
                $user->properties()->syncWithoutDetaching($properties->only($assigned)->pluck('id'));
                if ($role === UserRole::LANDLORD) {
                    DB::table('seed_checkpoints')->insertOrIgnore(['name' => 'reference-landlord:'.$user->id, 'completed_at' => now()]);
                }
            }
        });
    }
}
