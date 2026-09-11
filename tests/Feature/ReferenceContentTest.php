<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\WorkspaceItem;
use App\Support\ReferenceReports;
use Database\Seeders\ReferenceWorkspaceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReferenceContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_reference_import_populates_all_pages_and_preserves_decisions(): void
    {
        $owner = User::factory()->create(['role' => UserRole::LANDLORD]);
        $this->seed(ReferenceWorkspaceSeeder::class);
        $this->assertCount(5, $owner->fresh()->properties);
        $this->assertSame(60, WorkspaceItem::whereNotNull('report_month')->count());
        $this->actingAs($owner)->get(route('dashboard.landlord'))->assertOk()->assertSee('89,765,300.00')->assertSee('4,184,000.00')->assertSee('Demo P&amp;L', false);
        foreach (['properties' => 'OUD Reserve', 'reports' => 'Monthly performance report', 'documents' => 'Tenant contract register', 'approvals' => 'Lobby maintenance budget'] as $section => $text) {
            $this->get(route('landlord.index', ['section' => $section]))->assertOk()->assertSee($text)->assertSee('Clear filters');
        }
        $approval = WorkspaceItem::where('kind', 'approval')->firstOrFail();
        $this->post(route('workspace.decide', $approval), ['decision' => 'approved', 'comment' => 'Keep my decision'])->assertSessionHasNoErrors();
        $this->seed(ReferenceWorkspaceSeeder::class);
        $this->assertSame('approved', $approval->fresh()->status);
        $this->assertSame('Keep my decision', $approval->fresh()->decision_comment);
        $this->assertSame(75, WorkspaceItem::count());
        $document = WorkspaceItem::where('kind', 'document')->where('title', 'Tenant contract register')->firstOrFail();
        $this->assertStringStartsWith('PK', Storage::disk('local')->get($document->file_path));
        $this->get(route('workspace.download', $document))->assertDownload($document->file_name);
        $this->assertStringStartsWith('%PDF-', Storage::disk('local')->get($approval->fresh()->file_path));
        $this->get('/landlord/approvals?status=approved')->assertSee($approval->title)->assertDontSee('Tenant fit-out request');
        $this->get('/landlord/documents?category=Certificates')->assertSee('Property insurance certificate')->assertDontSee('Tenant contract register');
        $this->get('/landlord/reports?q=Market+positioning')->assertSee('Market positioning report')->assertDontSee('Monthly performance report');
    }

    public function test_every_original_report_renders_its_complete_content_and_secure_links(): void
    {
        $owner = User::factory()->create(['role' => UserRole::LANDLORD]);
        $this->seed(ReferenceWorkspaceSeeder::class);
        foreach ($owner->fresh()->properties as $property) {
            foreach (['en', 'ar'] as $locale) {
                foreach (range(1, 12) as $month) {
                    $this->actingAs($owner)->withSession(['locale' => $locale])->get(route('landlord.financials', ['property' => $property, 'year' => 2027, 'month' => $month]))
                        ->assertOk()->assertSee('data-source=', false)->assertSee('demo-pl')->assertSee('annual-source')->assertDontSee('href="excel_report_', false)->assertDontSee('user_login.html')->assertDontSee('No records available');
                }
            }
        }
        $owner->properties()->detach($owner->properties()->where('name', 'OUD Square')->first()->id);
        $this->withSession(['locale' => 'en'])->get(route('landlord.financials', $owner->properties()->first()))->assertDontSee('>OUD Square</a>', false);
    }

    public function test_reference_figures_include_exact_source_rows_and_demo_values(): void
    {
        $source = new ReferenceReports;
        $reserve = $source->figures('reserve', 1);
        $this->assertSame(75000000.0, $reserve['components']['office']['rent']);
        $this->assertSame(7500000.0, $reserve['collected']);
        $this->assertSame(1000000.0, $reserve['profit_revenue']);
        $this->assertSame('Jan 27!D100', $reserve['monthly_rows'][0]['reference']);
        $this->assertSame('Summary 27!D98', $reserve['annual_rows'][0]['reference']);
        $dunes = $source->figures('dunes', 1);
        $this->assertNull($dunes['components']['retail']['rent']);
        $this->assertSame(3960000.0, $dunes['saved_total']);
    }
}
