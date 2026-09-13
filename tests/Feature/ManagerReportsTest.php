<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Property;
use App\Models\ReportSubmission;
use App\Models\User;
use App\Models\WorkspaceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManagerReportsTest extends TestCase
{
    use RefreshDatabase;

    private function manager(): User
    {
        $department = Department::create(['name' => 'Property Management']);
        $manager = User::factory()->create(['role' => UserRole::DEPARTMENT_MANAGER, 'department_id' => $department->id]);
        $manager->forceFill(['can_submit_financial_reports' => true])->save();
        $property = Property::create(['name' => 'Assigned property']);
        $manager->properties()->attach($property);

        return $manager;
    }

    private function payload(User $manager): array
    {
        return ['title' => 'September management report', 'property_id' => $manager->properties()->first()->id,
            'report_month' => '2026-09', 'action' => 'draft',
            'file' => UploadedFile::fake()->create('report.xlsx', 20, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            'metrics' => ['occupancy' => '0', 'net_revenue' => '-100']];
    }

    public function test_manager_can_save_replace_and_submit_without_publishing(): void
    {
        Storage::fake('local');
        $manager = $this->manager();
        $this->actingAs($manager)->post(route('manager.reports.store'), $this->payload($manager))->assertSessionHasNoErrors()->assertRedirect(route('manager.reports.index'));
        $record = ReportSubmission::firstOrFail();
        $oldPath = $record->file_path;
        Storage::disk('local')->assertExists($oldPath);
        $this->assertSame('0', $record->metrics['occupancy']);
        $this->assertSame('-100', $record->metrics['net_revenue']);
        $this->put(route('manager.reports.update', $record), $this->payload($manager))->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($oldPath);
        $this->get(route('manager.reports.download', $record))->assertDownload('report.xlsx');
        $data = $this->payload($manager);
        unset($data['file']);
        $data['action'] = 'submit';
        $this->put(route('manager.reports.update', $record), $data)->assertSessionHasNoErrors();
        $this->assertSame('pending', $record->fresh()->status);
        $this->assertNotNull($record->fresh()->submitted_at);
        $this->assertSame(0, WorkspaceItem::count());
        $this->put(route('manager.reports.update', $record), $data)->assertStatus(409);
        $this->get(route('manager.reports.edit', $record))->assertOk()->assertSee('disabled', false);
        $this->get('/manager/reports?status=pending&q=September')->assertSee($record->title);
        $this->get('/manager/reports?status=draft')->assertDontSee($record->title);
    }

    public function test_roles_permissions_assignments_and_ownership_are_enforced(): void
    {
        Storage::fake('local');
        $manager = $this->manager();
        $this->actingAs($manager)->post(route('manager.reports.store'), $this->payload($manager));
        $record = ReportSubmission::firstOrFail();
        foreach ([UserRole::EMPLOYEE, UserRole::LANDLORD, UserRole::ADMIN] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))->get(route('manager.reports.index'))->assertForbidden();
            $this->get(route('manager.reports.download', $record))->assertForbidden();
        }
        $other = $this->manager();
        $this->actingAs($other)->get(route('manager.reports.edit', $record))->assertNotFound();
        $this->get(route('manager.reports.download', $record))->assertNotFound();
        $this->actingAs($manager)->post(route('manager.reports.store'), array_replace($this->payload($manager), ['property_id' => $other->properties()->first()->id]))->assertSessionHasErrors('property_id');
        $manager->properties()->detach();
        $this->get(route('manager.reports.download', $record))->assertNotFound();
        $this->get(route('manager.reports.create'))->assertSee('No reporting properties assigned');
        $manager->forceFill(['can_submit_financial_reports' => false])->save();
        $this->actingAs($manager)->get(route('manager.reports.create'))->assertForbidden();
    }

    public function test_upload_validation_and_duplicate_month_do_not_leave_files(): void
    {
        Storage::fake('local');
        $manager = $this->manager();
        $this->actingAs($manager)->post(route('manager.reports.store'), array_replace($this->payload($manager), ['file' => UploadedFile::fake()->create('bad.txt', 1, 'text/plain')]))->assertSessionHasErrors('file');
        $this->post(route('manager.reports.store'), array_replace($this->payload($manager), ['file' => UploadedFile::fake()->create('big.pdf', 20481, 'application/pdf')]))->assertSessionHasErrors('file');
        $this->post(route('manager.reports.store'), array_replace($this->payload($manager), ['action' => 'published', 'report_month' => '2026-13']))->assertSessionHasErrors(['action', 'report_month']);
        $this->post(route('manager.reports.store'), $this->payload($manager))->assertSessionHasNoErrors();
        $this->post(route('manager.reports.store'), $this->payload($manager))->assertSessionHasErrors('report_month');
        $this->assertCount(1, Storage::disk('local')->allFiles('report-submissions'));
    }

    public function test_admin_can_grant_permission_and_property_updates_preserve_manager_assignment(): void
    {
        $manager = $this->manager();
        $property = $manager->properties()->first();
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $data = ['name' => $manager->name, 'email' => $manager->email, 'role' => 'department_manager', 'department_id' => $manager->department_id, 'properties' => [$property->id], 'can_submit_financial_reports' => '1'];
        $this->actingAs($admin)->put(route('accounts.update', ['kind' => 'user', 'id' => $manager->id]), $data)->assertSessionHasNoErrors();
        $this->assertTrue($manager->fresh()->canSubmitFinancialReports());
        $this->put(route('accounts.update', ['kind' => 'property', 'id' => $property->id]), ['name' => $property->name, 'total_units' => 1])->assertSessionHasNoErrors();
        $this->assertCount(1, $manager->fresh()->properties);
        unset($data['can_submit_financial_reports']);
        $this->put(route('accounts.update', ['kind' => 'user', 'id' => $manager->id]), $data)->assertSessionHasNoErrors();
        $this->assertFalse($manager->fresh()->canSubmitFinancialReports());
        $this->assertCount(0, $manager->fresh()->properties);
    }

    public function test_manager_screens_render_in_both_locales_and_content_removal_is_scoped(): void
    {
        Storage::fake('local');
        $manager = $this->manager();
        $this->actingAs($manager);
        foreach (['en', 'ar'] as $locale) {
            $this->withSession(['locale' => $locale]);
            foreach (['dashboard.manager', 'manager.reports.index', 'manager.reports.create'] as $route) {
                $this->get(route($route))->assertOk()->assertDontSee('portal.manager_');
            }
        }
        Storage::disk('local')->put('workspace/department.pdf', 'test');
        $record = WorkspaceItem::create(['title' => 'Department file', 'kind' => 'document', 'audience' => 'staff', 'department_id' => $manager->department_id, 'status' => 'published', 'file_path' => 'workspace/department.pdf']);
        $other = $this->manager();
        $this->actingAs($other)->delete(route('content.destroy', $record))->assertNotFound();
        $this->actingAs($manager)->delete(route('content.destroy', $record))->assertRedirect();
        Storage::disk('local')->assertMissing('workspace/department.pdf');
        $this->assertModelMissing($record);
    }
}
