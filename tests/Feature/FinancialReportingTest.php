<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Property;
use App\Models\User;
use App\Models\WorkspaceItem;
use App\Support\FinancialReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialReportingTest extends TestCase
{
    use RefreshDatabase;

    private function payload(Property $property, array $overrides = []): array
    {
        return array_replace_recursive([
            'kind' => 'report', 'title' => 'Monthly financial results', 'audience' => 'landlord', 'property_id' => $property->id,
            'status' => 'published', 'report_month' => '2027-01',
            'financial_data' => [
                'components' => ['office' => ['area' => 200, 'rate' => 5, 'rent' => 1000, 'service' => 100], 'retail' => ['area' => 100, 'rate' => 5, 'rent' => 500, 'service' => 50]],
                'office_occupancy' => 80, 'retail_occupancy' => 60, 'rent_due' => 2000, 'collected' => 1500, 'saved_total' => 1800,
                'office_forecast' => 12000, 'retail_forecast' => 6000, 'profit_revenue' => 1650, 'operations' => 1000, 'maintenance' => 500, 'administration' => 300,
                'source_name' => 'Verified workbook.xlsx',
                'monthly_rows' => [['label' => 'Grand total', 'reference' => 'Jan!D42', 'values' => ['1800.00', '', '', '', '', '']]],
                'annual_rows' => [['label' => 'Annual total', 'reference' => 'Summary!D40', 'values' => ['19800.00']]],
            ],
        ], $overrides);
    }

    private function owner(Property $property): User
    {
        $owner = User::factory()->create(['role' => UserRole::LANDLORD]);
        $owner->properties()->attach($property);

        return $owner;
    }

    public function test_financial_records_save_calculate_and_render_both_locales(): void
    {
        $property = Property::create(['name' => 'Assigned financial property']);
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $this->actingAs($admin)->post('/content', $this->payload($property))->assertSessionHasNoErrors()->assertRedirect();
        $record = WorkspaceItem::whereNotNull('report_month')->firstOrFail();
        $report = new FinancialReport($record);
        $this->assertSame('2027-01-01', $record->report_month->toDateString());
        $this->assertSame(1650.0, $report->totalRevenue());
        $this->assertSame(75.0, $report->collectionRate());
        $this->assertSame(-150.0, $report->profit());
        $this->assertSame('1800.00', $record->financial_data['monthly_rows'][0]['values'][0]);
        $this->get(route('content.edit', $record))->assertOk()->assertSee('2027-01')->assertSee('Verified workbook.xlsx');
        $this->actingAs($this->owner($property));
        foreach (['en', 'ar'] as $locale) {
            $response = $this->withSession(['locale' => $locale])->get(route('landlord.financials', $property));
            $response->assertOk()->assertSee('1,650.00')->assertSee('75.00%')->assertSee('-150.00')->assertSee('Jan!D42')->assertSee('Summary!D40')
                ->assertSee('report-disclosure')->assertSee('oud-role-dialog')->assertDontSee('Demo')->assertDontSee('excel_report_');
        }
        $this->get(route('workspace.show', $record))->assertRedirect(route('landlord.financials', ['property' => $property->id, 'year' => 2027, 'month' => 1]));
    }

    public function test_financial_pages_enforce_assignment_role_and_publication(): void
    {
        $property = Property::create(['name' => 'Assigned']);
        $other = Property::create(['name' => 'Other private property']);
        $owner = $this->owner($property);
        $url = route('landlord.financials', $property);
        $this->get($url)->assertRedirect(route('login'));
        foreach ([UserRole::EMPLOYEE, UserRole::DEPARTMENT_MANAGER, UserRole::ADMIN] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))->get($url)->assertForbidden();
        }
        $this->actingAs($owner)->get(route('landlord.financials', $other))->assertNotFound();
        foreach (['draft', 'pending', 'approved', 'rejected'] as $index => $status) {
            $record = WorkspaceItem::create(['kind' => 'report', 'title' => 'Unpublished private report', 'audience' => 'landlord', 'status' => $status, 'property_id' => $property->id, 'report_month' => '2027-'.str_pad($index + 1, 2, '0', STR_PAD_LEFT).'-01', 'financial_data' => ['source_name' => 'Private source']]);
            $this->get(route('workspace.show', $record))->assertNotFound();
            $this->get($url.'?year=2027&month='.($index + 1))->assertOk()->assertDontSee('Private source')->assertSee('No published financial report');
        }
        $future = WorkspaceItem::create(['kind' => 'report', 'title' => 'Future report', 'audience' => 'landlord', 'status' => 'published', 'property_id' => $property->id, 'report_month' => '2028-01-01', 'published_at' => now()->addYear()]);
        $this->get(route('workspace.show', $future))->assertNotFound();
        $this->get($url.'?year=2028&month=1')->assertOk()->assertDontSee('Future report');
        $this->get(route('dashboard.landlord'))->assertDontSee('Other private property');
        $owner->properties()->detach($property);
        $this->get($url)->assertNotFound();
    }

    public function test_all_property_month_and_language_variants_and_missing_months_render(): void
    {
        $owner = User::factory()->create(['role' => UserRole::LANDLORD]);
        foreach (['OUD Reserve', 'OUD Square', 'OUD Dunes', 'La Perle East', 'La Perle West'] as $name) {
            $property = Property::create(['name' => $name]);
            $owner->properties()->attach($property);
            foreach (['en', 'ar'] as $locale) {
                foreach (range(1, 12) as $month) {
                    $this->actingAs($owner)->withSession(['locale' => $locale])->get(route('landlord.financials', ['property' => $property, 'year' => 2027, 'month' => $month]))
                        ->assertOk()->assertSee($name)->assertSee('2027')->assertDontSee('NaN')->assertDontSee('Infinity');
                }
            }
        }
    }

    public function test_invalid_months_duplicate_reports_and_untrusted_financial_fields_are_rejected(): void
    {
        $property = Property::create(['name' => 'Property']);
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $this->actingAs($admin)->post('/content', $this->payload($property))->assertSessionHasNoErrors();
        $this->post('/content', $this->payload($property))->assertSessionHasErrors('report_month');
        $record = WorkspaceItem::firstOrFail();
        $this->put(route('content.update', $record), $this->payload($property, ['financial_data' => ['collected' => 0]]))->assertSessionHasNoErrors();
        $this->assertSame(0.0, (new FinancialReport($record->fresh()))->collectionRate());
        $this->post('/content', $this->payload($property, ['report_month' => null]))->assertSessionHasErrors('report_month');
        $this->post('/content', $this->payload($property, ['report_month' => '2027-13']))->assertSessionHasErrors('report_month');
        $this->post('/content', $this->payload($property, ['report_month' => '2027-02', 'financial_data' => ['office_occupancy' => 101]]))->assertSessionHasErrors('financial_data.office_occupancy');
        $this->post('/content', $this->payload($property, ['report_month' => '2027-02', 'financial_data' => ['components' => ['office' => ['rent' => -1]]]]))->assertSessionHasErrors('financial_data.components.office.rent');
        $this->post('/content', $this->payload($property, ['report_month' => '2027-02', 'financial_data' => ['status' => 'published']]))->assertSessionHasErrors('financial_data');
        $this->actingAs($this->owner($property))->post('/content', $this->payload($property))->assertForbidden();
        $this->getJson(route('landlord.financials', $property).'?month=13')->assertUnprocessable();
        $this->getJson(route('landlord.financials', $property).'?year=2101')->assertUnprocessable();
    }

    public function test_missing_figures_are_distinct_from_zero_and_source_text_is_escaped(): void
    {
        $empty = new FinancialReport(null);
        $this->assertNull($empty->totalRevenue());
        $this->assertNull($empty->collectionRate());
        $this->assertNull($empty->profit());
        $property = Property::create(['name' => 'Property']);
        $record = WorkspaceItem::create(['kind' => 'report', 'title' => 'Zero report', 'audience' => 'landlord', 'status' => 'published', 'property_id' => $property->id, 'report_month' => '2027-01-01', 'financial_data' => ['components' => ['office' => ['rent' => 0, 'service' => 0]], 'rent_due' => 0, 'collected' => 0, 'source_notes' => '<script>alert(1)</script>']]);
        $this->assertSame(0.0, (new FinancialReport($record))->totalRevenue());
        $this->assertNull((new FinancialReport($record))->collectionRate());
        $this->actingAs($this->owner($property))->get(route('landlord.financials', $property))->assertOk()->assertSee('0.00')->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_admin_can_preview_draft_financial_reports_without_publishing_them(): void
    {
        $property = Property::create(['name' => 'Draft property']);
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $this->actingAs($admin)->post('/content', $this->payload($property, ['status' => 'draft']))->assertSessionHasNoErrors();
        $record = WorkspaceItem::firstOrFail();
        $url = route('admin.financials', ['property' => $property, 'year' => 2027, 'month' => 1]);
        $this->get(route('workspace.show', $record))->assertRedirect($url);
        $this->get($url)->assertOk()->assertSee('1,650.00')->assertSee('Draft')->assertSee('/admin/properties/')->assertDontSee('/landlord/properties/');
        $this->actingAs($this->owner($property))->get($url)->assertForbidden();
        $this->get(route('landlord.financials', $property).'?year=2027&month=1')->assertOk()->assertDontSee('1,650.00');
    }
}
