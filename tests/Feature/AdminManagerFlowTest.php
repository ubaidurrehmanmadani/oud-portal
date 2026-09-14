<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Jobs\DeleteUnreferencedUpload;
use App\Jobs\NotifyWorkspacePublication;
use App\Jobs\ProcessPrivateUpload;
use App\Models\Department;
use App\Models\Property;
use App\Models\ReportSubmission;
use App\Models\User;
use App\Models\WorkspaceItem;
use App\Notifications\WorkspacePublished;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminManagerFlowTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        Storage::fake('local');
        Queue::fake();
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $department = Department::create(['name' => 'Property Management']);
        $manager = User::factory()->create(['role' => UserRole::DEPARTMENT_MANAGER, 'department_id' => $department->id, 'can_submit_financial_reports' => true]);
        $property = Property::create(['name' => 'Reporting property']);
        $manager->properties()->attach($property);
        Storage::disk('local')->put('report-submissions/report.pdf', '%PDF-1.4');
        $submission = ReportSubmission::create(['user_id' => $manager->id, 'department_id' => $department->id, 'property_id' => $property->id, 'title' => 'Monthly figures', 'report_month' => '2026-09-01', 'status' => 'pending', 'submitted_at' => now(), 'file_path' => 'report-submissions/report.pdf', 'file_name' => 'report.pdf', 'metrics' => ['occupancy' => 0, 'net_revenue' => -100]]);

        return [$admin, $manager, $property, $submission];
    }

    public function test_return_resubmit_approve_and_immutable_publication(): void
    {
        [$admin,$manager,$property,$report] = $this->fixture();
        $url = route('admin.report-reviews.decide', $report);
        $this->actingAs($admin)->post($url, ['decision' => 'returned'])->assertSessionHasErrors('comment');
        $this->post($url, ['decision' => 'returned', 'comment' => 'Correct the figures'])->assertSessionHasNoErrors();
        $this->assertSame('returned', $report->fresh()->status);
        $this->actingAs($manager)->get(route('manager.reports.edit', $report))->assertSee('Correct the figures');
        $this->put(route('manager.reports.update', $report), ['title' => $report->title, 'property_id' => $property->id, 'report_month' => '2026-09', 'action' => 'submit', 'metrics' => ['occupancy' => 0, 'net_revenue' => -100]])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('workspace_items', 0);
        $this->actingAs($admin)->post($url, ['decision' => 'approved'])->assertSessionHasNoErrors();
        $item = WorkspaceItem::findOrFail($report->fresh()->published_item_id);
        $this->assertSame('published', $item->status);
        $this->assertSame('0.00', $item->occupancy);
        $this->assertSame('-100.00', $item->net_revenue);
        $this->assertDatabaseCount('report_reviews', 2);
        Queue::assertPushed(NotifyWorkspacePublication::class);
        $this->post($url, ['decision' => 'approved'])->assertConflict();
        $this->put(route('content.update', $item), [])->assertConflict();
        $this->actingAs($manager)->put(route('manager.reports.update', $report), [])->assertConflict();
        $owner = User::factory()->create(['role' => UserRole::LANDLORD]);
        $owner->properties()->attach($property);
        $this->actingAs($owner)->followingRedirects()->get(route('workspace.show', $item))->assertOk();
        $this->get($url)->assertStatus(405);
    }

    public function test_review_permissions_rejection_and_duplicate_month_are_enforced(): void
    {
        [$admin,$manager,$property,$report] = $this->fixture();
        $url = route('admin.report-reviews.decide', $report);
        $this->actingAs($manager)->post($url, ['decision' => 'approved'])->assertForbidden();
        WorkspaceItem::create(['kind' => 'report', 'title' => 'Existing', 'property_id' => $property->id, 'report_month' => '2026-09-01', 'audience' => 'landlord', 'status' => 'published']);
        $this->actingAs($admin)->post($url, ['decision' => 'approved'])->assertConflict();
        $this->assertSame('pending', $report->fresh()->status);
        $this->post($url, ['decision' => 'rejected', 'comment' => 'Not accepted'])->assertSessionHasNoErrors();
        $this->actingAs($manager)->put(route('manager.reports.update', $report), [])->assertConflict();
        foreach (['en', 'ar'] as $locale) {
            $this->withSession(['locale' => $locale])->actingAs($admin)->get(route('admin.report-reviews.index', ['status' => 'rejected']))->assertOk()->assertSee('Not accepted');
        }
    }

    public function test_archive_blocks_manager_submission_and_history_blocks_deletion(): void
    {
        [$admin,$manager,$property,$report] = $this->fixture();
        $url = route('setup.lifecycle', ['kind' => 'property', 'id' => $property->id]);
        $this->actingAs($admin)->post($url, ['action' => 'archive', 'current_password' => 'password'])->assertSessionHasNoErrors();
        $this->post($url, ['action' => 'delete', 'current_password' => 'password'])->assertConflict();
        $report->update(['status' => 'draft']);
        $this->actingAs($manager)->put(route('manager.reports.update', $report), ['title' => 'Blocked', 'property_id' => $property->id, 'report_month' => '2026-09', 'action' => 'submit'])->assertSessionHasErrors('property_id');
        $this->actingAs($admin)->post($url, ['action' => 'restore', 'current_password' => 'password'])->assertSessionHasNoErrors();
        $unused = Department::create(['name' => 'Unused']);
        $url = route('setup.lifecycle', ['kind' => 'department', 'id' => $unused->id]);
        $this->post($url, ['action' => 'archive', 'current_password' => 'password'])->assertSessionHasNoErrors();
        $this->post($url, ['action' => 'delete', 'current_password' => 'password'])->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('departments', ['id' => $unused->id]);
    }

    public function test_upload_processing_gates_visibility_and_ignores_stale_jobs(): void
    {
        [$admin, $manager, $property, $report] = $this->fixture();
        $owner = User::factory()->create(['role' => UserRole::LANDLORD]);
        $owner->properties()->attach($property);
        Storage::disk('local')->put('workspace/new.pdf', '%PDF-new');
        $item = WorkspaceItem::create(['title' => 'Queued file', 'kind' => 'document', 'audience' => 'landlord', 'property_id' => $property->id, 'status' => 'published', 'file_path' => 'workspace/new.pdf', 'file_processing_required' => true]);
        $this->assertFalse(WorkspaceItem::visibleTo($owner)->whereKey($item->id)->exists());
        (new ProcessPrivateUpload('workspace', $item->id, 'workspace/stale.pdf'))->handle();
        $this->assertNull($item->fresh()->file_processed_at);
        (new ProcessPrivateUpload('workspace', $item->id, 'workspace/new.pdf'))->handle();
        $this->assertTrue(WorkspaceItem::visibleTo($owner)->whereKey($item->id)->exists());
        $this->assertSame(hash('sha256', '%PDF-new'), $item->fresh()->file_sha256);
        (new ProcessPrivateUpload('workspace', $item->id, 'workspace/new.pdf'))->handle();
        Queue::assertPushed(NotifyWorkspacePublication::class, 1);
        (new DeleteUnreferencedUpload('workspace/new.pdf'))->handle();
        Storage::disk('local')->assertExists('workspace/new.pdf');
        $report->update(['file_processing_required' => true]);
        $this->actingAs($admin)->post(route('admin.report-reviews.decide', $report), ['decision' => 'approved'])->assertConflict();
    }

    public function test_notifications_recheck_recipient_scope(): void
    {
        [$admin,$manager,$property,$report] = $this->fixture();
        Notification::fake();
        $owner = User::factory()->create(['role' => UserRole::LANDLORD]);
        $other = User::factory()->create(['role' => UserRole::LANDLORD]);
        $owner->properties()->attach($property);
        $item = WorkspaceItem::create(['kind' => 'report', 'title' => 'Published', 'audience' => 'landlord', 'property_id' => $property->id, 'status' => 'published']);
        (new NotifyWorkspacePublication($item->id))->handle();
        Notification::assertSentTo($owner, WorkspacePublished::class);
        Notification::assertNotSentTo($other, WorkspacePublished::class);
        $notice = new WorkspacePublished($item->id);
        $this->assertTrue($notice->shouldSend($owner, 'mail'));
        $owner->properties()->detach();
        $this->assertFalse($notice->shouldSend($owner, 'mail'));
    }
}
