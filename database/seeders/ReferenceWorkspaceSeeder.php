<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Property;
use App\Models\User;
use App\Models\WorkspaceItem;
use App\Support\ReferenceFiles;
use App\Support\ReferenceReports;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ReferenceWorkspaceSeeder extends Seeder
{
    public function run(): void
    {
        $source = new ReferenceReports;
        $properties = collect();
        foreach (ReferenceReports::PROPERTIES as $slug => $name) {
            $property = Property::firstOrCreate(['name' => $name], ['location' => 'Riyadh, Saudi Arabia', 'type' => 'Mixed use', 'total_units' => ['reserve' => 54, 'square' => 38, 'dunes' => 26][$slug] ?? 0]);
            $properties->put($name, $property);
            foreach (range(1, 12) as $month) {
                $date = sprintf('2027-%02d-01', $month);
                if (WorkspaceItem::where('property_id', $property->id)->whereDate('report_month', $date)->exists()) {
                    continue;
                }
                $figures = $source->figures($slug, $month);
                WorkspaceItem::create(['kind' => 'report', 'title' => $name.' · '.Carbon::parse($date)->format('F Y'), 'property_id' => $property->id, 'report_month' => $date, 'period' => Carbon::parse($date)->format('F Y'), 'audience' => 'landlord', 'status' => 'published', 'published_at' => now(), 'category' => 'reference-financial', 'financial_data' => $figures, 'body' => 'Supplied OUD reference workbook preview. The separately labelled profit and loss example is illustrative.']);
            }
        }
        foreach (User::where('role', UserRole::LANDLORD)->get() as $landlord) {
            $landlord->properties()->syncWithoutDetaching($properties->pluck('id'));
        }
        foreach ([
            ['report', 'Monthly performance report', 'OUD Reserve', 'August 2026', 'Occupancy, revenue, and operational summary.'],
            ['report', 'Property budget review', 'OUD Reserve', 'Q3 2026', 'Quarterly budget allocation and forecast review.'],
            ['report', 'Maintenance summary', 'OUD Reserve', 'August 2026', 'Completed maintenance activity and open work orders.'],
            ['report', 'Market positioning report', 'OUD Square', 'Q2 2026', 'Market context and positioning across the mixed-use destination.'],
            ['report', 'Destination progress report', 'OUD Dunes', 'August 2026', 'Progress overview for the completed mixed-use destination.'],
            ['report', 'Development update', 'La Perle East', 'August 2026', 'Development progress and current delivery milestones.'],
            ['document', 'OUD Reserve lease register', 'OUD Reserve', 'Updated this month', 'Current lease register and occupancy records for OUD Reserve.'],
            ['document', 'Property insurance certificate', 'OUD Reserve', 'Updated last month', 'Current property insurance certificate and coverage details.'],
            ['document', 'Tenant contract register', 'OUD Reserve', 'Updated last month', 'Tenant contract register for the assigned property portfolio.'],
        ] as [$kind, $title, $propertyName, $period, $body]) {
            $record = WorkspaceItem::firstOrCreate(['kind' => $kind, 'title' => $title], ['property_id' => $properties[$propertyName]->id, 'period' => $period, 'body' => $body, 'audience' => 'landlord', 'status' => 'published', 'published_at' => now(), 'category' => $kind === 'document' ? (str_contains($title, 'certificate') ? 'Certificates' : 'Contracts') : 'Property reports']);
            (new ReferenceFiles)->attach($record, Str::slug($title).($title === 'Tenant contract register' ? '.xlsx' : '.pdf'));
            if ($record->wasRecentlyCreated && $kind === 'report') {
                $record->update(['occupancy' => 86, 'net_revenue' => 2400000, 'leased_area' => 18420]);
            }
        }
        foreach ([
            ['Lobby maintenance budget', 'OUD Reserve', 145000, 'Maintenance budget', 'Property Management', '2 days ago'],
            ['Tenant fit-out request', 'OUD Reserve', 82000, 'Tenant fit-out', 'Property Management', '5 days ago'],
            ['Special event approval', 'OUD Square', 28500, 'Special event', 'Hospitality Management', '1 week ago'],
            ['Landscape maintenance renewal', 'OUD Dunes', 64000, 'Landscape maintenance', 'Property Management', '3 days ago'],
            ['Retail signage request', 'OUD Square', 39500, 'Retail signage', 'Commercial Division', '6 days ago'],
            ['Construction milestone review', 'La Perle East', 310000, 'Construction milestone', 'Development Management', '9 days ago'],
        ] as [$title, $propertyName, $amount, $category, $submittedBy, $period]) {
            $record = WorkspaceItem::firstOrCreate(['kind' => 'approval', 'title' => $title], ['property_id' => $properties[$propertyName]->id, 'amount' => $amount, 'category' => $category, 'period' => 'Submitted '.$period, 'body' => 'Review the '.strtolower($category).' request and supporting document before making a decision. Submitted by '.$submittedBy.'.', 'audience' => 'landlord', 'status' => 'pending', 'published_at' => now()]);
            (new ReferenceFiles)->attach($record, $category.' proposal.pdf');
            if ($record->category === null && $record->status === 'pending') {
                $record->update(['category' => $category, 'body' => $record->body.' Submitted by '.$submittedBy.'.']);
            }
        }
    }
}
