<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkspaceItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

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

        $landlords = User::query()->where('role', UserRole::LANDLORD)->get();
        if ($landlords->isEmpty()) {
            return;
        }

        $properties = collect([
            ['name' => 'OUD Reserve', 'location' => 'Riyadh, Saudi Arabia', 'type' => 'Mixed use', 'total_units' => 54, 'description' => 'A mixed-use property with active retail, office, and hospitality units.'],
            ['name' => 'OUD Square', 'location' => 'Riyadh, Saudi Arabia', 'type' => 'Mixed use', 'total_units' => 38, 'description' => 'A connected mixed-use destination in the OUD portfolio.'],
            ['name' => 'OUD Dunes', 'location' => 'Riyadh, Saudi Arabia', 'type' => 'Hospitality', 'total_units' => 26, 'description' => 'A hospitality property with seasonal operating activity.'],
        ])->mapWithKeys(fn (array $attributes) => [$attributes['name'] => Property::updateOrCreate(['name' => $attributes['name']], $attributes)]);

        foreach ($landlords as $landlord) {
            $landlord->properties()->syncWithoutDetaching($properties->pluck('id'));
        }

        $reserve = $properties['OUD Reserve'];
        $square = $properties['OUD Square'];
        $dunes = $properties['OUD Dunes'];
        $reports = [
            ['title' => 'Monthly performance report', 'property_id' => $reserve->id, 'period' => 'August 2026', 'occupancy' => 86, 'net_revenue' => 2400000, 'leased_area' => 18420, 'body' => 'OUD Reserve property performance, occupancy, revenue, and operational summary.'],
            ['title' => 'Property budget review', 'property_id' => $reserve->id, 'period' => 'Q3 2026', 'body' => 'Quarterly budget position and forecast for OUD Reserve.'],
            ['title' => 'Maintenance summary', 'property_id' => $reserve->id, 'period' => 'August 2026', 'body' => 'Maintenance completion, open works, and operational notes.'],
            ['title' => 'Market positioning report', 'property_id' => $square->id, 'period' => 'Q2 2026', 'body' => 'Market positioning and tenant demand summary for OUD Square.'],
            ['title' => 'Destination progress report', 'property_id' => $dunes->id, 'period' => 'August 2026', 'body' => 'Destination progress and operating summary for OUD Dunes.'],
            ['title' => 'Development update', 'property_id' => $dunes->id, 'period' => 'August 2026', 'body' => 'Development milestones and upcoming delivery activities.'],
        ];
        foreach ($reports as $index => $report) {
            WorkspaceItem::updateOrCreate(
                ['kind' => 'report', 'title' => $report['title'], 'property_id' => $report['property_id']],
                $report + ['audience' => 'landlord', 'status' => 'published', 'published_at' => now()->subDays($index + 1)],
            );
        }

        foreach ([38, 40, 42, 40, 43, 46, 45, 49, 52, 50, 55] as $index => $occupancy) {
            WorkspaceItem::updateOrCreate(
                ['kind' => 'report', 'title' => 'Performance trend '.($index + 1), 'property_id' => $reserve->id],
                [
                    'audience' => 'landlord', 'status' => 'published', 'category' => 'dashboard-history',
                    'period' => now()->subMonths(11 - $index)->format('F Y'), 'occupancy' => $occupancy,
                    'net_revenue' => 1800000 + ($index * 60000), 'leased_area' => 15000 + ($index * 342),
                    'body' => 'Historical performance data for the OUD Reserve dashboard trend.',
                    'published_at' => now()->subMonths(11 - $index),
                ],
            );
        }

        foreach ([
            ['title' => 'OUD Reserve lease register', 'property_id' => $reserve->id, 'category' => 'Contracts', 'file_name' => 'oud-reserve-lease-register.pdf', 'body' => 'Current lease register and occupancy records for OUD Reserve.'],
            ['title' => 'Property insurance certificate', 'property_id' => $reserve->id, 'category' => 'Certificates', 'file_name' => 'property-insurance-certificate.pdf', 'body' => 'Current property insurance certificate and coverage details.'],
            ['title' => 'Tenant contract register', 'property_id' => $reserve->id, 'category' => 'Contracts', 'file_name' => 'tenant-contract-register.xlsx', 'body' => 'Tenant contract register for the assigned property portfolio.'],
        ] as $document) {
            $path = 'workspace/demo/'.$document['file_name'];
            Storage::disk('local')->put($path, 'OUD demo document: '.$document['title']);
            WorkspaceItem::updateOrCreate(
                ['kind' => 'document', 'title' => $document['title'], 'property_id' => $document['property_id']],
                $document + ['audience' => 'landlord', 'status' => 'published', 'published_at' => now(), 'file_path' => $path],
            );
        }

        foreach ([
            ['title' => 'Lobby maintenance budget', 'property_id' => $reserve->id, 'amount' => 145000, 'period' => 'Submitted 2 days ago', 'body' => 'Review the lobby maintenance budget and supporting operational request.'],
            ['title' => 'Tenant fit-out request', 'property_id' => $reserve->id, 'amount' => 85000, 'period' => 'Submitted 5 days ago', 'body' => 'Tenant fit-out request awaiting landlord review.'],
            ['title' => 'Special event approval', 'property_id' => $square->id, 'amount' => 32000, 'period' => 'Submitted 1 week ago', 'body' => 'Special event request for OUD Square.'],
            ['title' => 'Landscape maintenance renewal', 'property_id' => $dunes->id, 'amount' => 54000, 'period' => 'Submitted 3 days ago', 'body' => 'Landscape maintenance renewal request for OUD Dunes.'],
            ['title' => 'Retail signage request', 'property_id' => $square->id, 'amount' => 18000, 'period' => 'Submitted 6 days ago', 'body' => 'Retail signage request awaiting review.'],
            ['title' => 'Construction milestone review', 'property_id' => $dunes->id, 'amount' => 210000, 'period' => 'Submitted 9 days ago', 'body' => 'Construction milestone review and payment request.'],
        ] as $approval) {
            WorkspaceItem::updateOrCreate(
                ['kind' => 'approval', 'title' => $approval['title'], 'property_id' => $approval['property_id']],
                $approval + ['audience' => 'landlord', 'status' => 'pending', 'published_at' => now()],
            );
        }
    }
}
