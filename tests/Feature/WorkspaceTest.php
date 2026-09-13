<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Property;
use App\Models\User;
use App\Models\WorkspaceItem;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private function item(array $attributes = []): WorkspaceItem
    {
        return WorkspaceItem::create($attributes + ['kind' => 'document', 'title' => 'Shared handbook', 'audience' => 'staff', 'status' => 'published']);
    }

    public function test_all_role_dashboards_require_the_matching_role(): void
    {
        foreach (UserRole::cases() as $role) {
            $user = User::factory()->create(['role' => $role]);
            foreach (UserRole::cases() as $target) {
                $response = $this->actingAs($user)->get(route($target->dashboardRouteName()));
                $role === $target ? $response->assertOk()->assertSee('oud/styles.css') : $response->assertForbidden();
            }
        }
    }

    public function test_landlord_navigation_contains_only_landlord_sections(): void
    {
        $owner = User::factory()->create(['role' => UserRole::LANDLORD]);

        $this->actingAs($owner)->get(route('dashboard.landlord'))
            ->assertOk()
            ->assertSee('Property workspace')
            ->assertSee(route('landlord.index', ['section' => 'properties']), false)
            ->assertSee(route('landlord.index', ['section' => 'reports']), false)
            ->assertSee(route('landlord.index', ['section' => 'documents']), false)
            ->assertSee(route('landlord.index', ['section' => 'approvals']), false)
            ->assertDontSee('/dashboard/admin')
            ->assertDontSee('/staff/training')
            ->assertDontSee('/admin/users');
    }

    public function test_all_staff_and_landlord_pages_render_in_both_languages(): void
    {
        foreach (['en', 'ar'] as $locale) {
            foreach ([UserRole::EMPLOYEE, UserRole::DEPARTMENT_MANAGER, UserRole::LANDLORD] as $role) {
                $user = User::factory()->create(['role' => $role]);
                $landlord = $role === UserRole::LANDLORD;
                foreach ($landlord ? ['properties', 'reports', 'documents', 'approvals'] : ['documents', 'training', 'announcements', 'search'] as $section) {
                    $this->withSession(['locale' => $locale])->actingAs($user)
                        ->get(route($landlord ? 'landlord.index' : 'staff.index', ['section' => $section]))
                        ->assertOk()->assertSee('oud/styles.css')->assertDontSee('workspace.'.$section)->assertDontSee('portal.');
                }
            }
        }
    }

    public function test_seeded_landlord_can_open_every_reference_screen(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['role' => UserRole::LANDLORD]);
        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();

        foreach ([
            ['dashboard.landlord', []],
            ['landlord.index', ['section' => 'properties']],
            ['landlord.index', ['section' => 'reports']],
            ['landlord.index', ['section' => 'documents']],
            ['landlord.index', ['section' => 'approvals']],
        ] as [$routeName, $parameters]) {
            $this->actingAs($owner)->get(route($routeName, $parameters))->assertOk();
        }

        $report = WorkspaceItem::where('title', 'Monthly performance report')->firstOrFail();
        $approval = WorkspaceItem::where('title', 'Lobby maintenance budget')->firstOrFail();
        $document = WorkspaceItem::where('title', 'OUD Reserve lease register')->firstOrFail();

        $this->actingAs($owner)->get(route('workspace.show', $report))->assertOk()->assertSee($report->title);
        $this->get(route('workspace.download', $report))->assertDownload($report->file_name);
        $this->get(route('workspace.show', $approval))->assertOk()->assertSee($approval->title);
        $this->get(route('workspace.download', $document))->assertDownload($document->file_name);
    }

    public function test_guests_and_wrong_roles_cannot_access_workspace_routes(): void
    {
        $this->get('/staff/documents')->assertRedirect(route('login'));
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);
        $this->actingAs($employee)->get('/landlord/reports')->assertForbidden();
        $this->get('/content')->assertForbidden();
        $this->get('/accounts/user/'.$employee->id.'/edit')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => UserRole::LANDLORD]))->get('/staff/search')->assertForbidden();
    }

    public function test_department_content_is_scoped_in_lists_search_and_direct_access(): void
    {
        $department = Department::create(['name' => 'Hospitality']);
        $other = Department::create(['name' => 'Finance']);
        $user = User::factory()->create(['role' => UserRole::EMPLOYEE, 'department_id' => $department->id]);
        $own = $this->item(['title' => 'Own handbook', 'department_id' => $department->id]);
        $private = $this->item(['title' => 'Private handbook', 'department_id' => $other->id]);
        $draft = $this->item(['title' => 'Draft handbook', 'status' => 'draft']);
        $future = $this->item(['title' => 'Future handbook', 'published_at' => now()->addDay()]);
        $global = $this->item(['title' => 'Global handbook']);
        foreach (['documents', 'search'] as $section) {
            $this->actingAs($user)->get('/staff/'.$section.'?q=handbook')->assertOk()
                ->assertSee($own->title)->assertSee($global->title)->assertDontSee($private->title)->assertDontSee($draft->title)->assertDontSee($future->title);
        }
        foreach ([$private, $draft, $future] as $record) {
            $this->get(route('workspace.show', $record))->assertNotFound();
            $this->get(route('workspace.download', $record))->assertNotFound();
        }
        $this->get(route('workspace.show', $own))->assertOk();
    }

    public function test_property_assignment_controls_reports_downloads_and_decisions(): void
    {
        Storage::fake('local');
        $property = Property::create(['name' => 'Assigned property']);
        $other = Property::create(['name' => 'Other property']);
        $owner = User::factory()->create(['role' => UserRole::LANDLORD]);
        $owner->properties()->attach($property);
        Storage::disk('local')->put('workspace/report.txt', 'Actual report');
        $report = $this->item(['kind' => 'report', 'title' => 'Assigned report', 'audience' => 'landlord', 'property_id' => $property->id, 'file_path' => 'workspace/report.txt', 'file_name' => 'report.txt', 'occupancy' => 86]);
        $private = $this->item(['kind' => 'report', 'title' => 'Private report', 'audience' => 'landlord', 'property_id' => $other->id]);
        $this->actingAs($owner)->get('/landlord/reports')->assertSee($report->title)->assertDontSee($private->title);
        $this->get(route('workspace.download', $report))->assertDownload('report.txt');
        $this->get(route('workspace.show', $report))->assertOk()->assertSee('86.00');
        $this->get(route('workspace.show', $private))->assertNotFound();
        $this->get('/landlord/reports?property='.$other->id)->assertNotFound();
        $this->get('/dashboard/landlord?property='.$property->id)->assertOk()->assertSee('Property financial reports')->assertSee($report->title)->assertDontSee($private->title);
        $request = $this->item(['kind' => 'approval', 'audience' => 'landlord', 'property_id' => $property->id, 'status' => 'pending']);
        $this->get(route('workspace.show', $request))->assertOk();
        $this->post(route('workspace.decide', $request), ['decision' => 'approved', 'comment' => 'Proceed'])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('workspace_items', ['id' => $request->id, 'status' => 'approved', 'decision_comment' => 'Proceed', 'decided_by' => $owner->id]);
        $this->post(route('workspace.decide', $request), ['decision' => 'rejected'])->assertStatus(409);
        $this->actingAs(User::factory()->create(['role' => UserRole::LANDLORD]))->post(route('workspace.decide', $request), ['decision' => 'approved'])->assertNotFound();
    }

    public function test_manager_can_upload_only_to_their_department(): void
    {
        Storage::fake('local');
        $department = Department::create(['name' => 'Hospitality']);
        $other = Department::create(['name' => 'Finance']);
        $manager = User::factory()->create(['role' => UserRole::DEPARTMENT_MANAGER, 'department_id' => $department->id]);
        $this->actingAs($manager)->post('/content', [
            'kind' => 'document', 'title' => 'Uploaded manual', 'audience' => 'staff', 'status' => 'published',
            'department_id' => $other->id, 'file' => UploadedFile::fake()->createWithContent('manual.txt', 'Useful instructions'),
        ])->assertRedirect(route('content.index'))->assertSessionHasNoErrors();
        $record = WorkspaceItem::where('title', 'Uploaded manual')->firstOrFail();
        $this->assertEquals($department->id, $record->department_id);
        Storage::disk('local')->assertExists($record->file_path);
        $this->get(route('workspace.download', $record))->assertDownload('manual.txt');
        $this->post('/content', ['kind' => 'report'])->assertForbidden();
        $this->get('/content/create?kind=property')->assertForbidden();
        $private = $this->item(['department_id' => $other->id]);
        $this->get(route('content.edit', $private))->assertNotFound();
        $this->put(route('content.update', $private), ['title' => 'Changed'])->assertNotFound();
        $this->actingAs(User::factory()->create(['role' => UserRole::DEPARTMENT_MANAGER]))->get('/content')->assertForbidden();
    }

    public function test_admin_can_create_edit_and_assign_workspace_records(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $landlord = User::factory()->create(['role' => UserRole::LANDLORD]);
        $this->actingAs($admin)->post('/content', ['kind' => 'department', 'name' => 'Operations'])->assertSessionHasNoErrors();
        $department = Department::where('name', 'Operations')->firstOrFail();
        $this->post('/content', ['kind' => 'property', 'name' => 'OUD Test', 'total_units' => 20, 'landlords' => [$landlord->id]])->assertSessionHasNoErrors();
        $property = Property::where('name', 'OUD Test')->firstOrFail();
        $this->assertTrue($landlord->properties()->whereKey($property->id)->exists());
        $this->post('/content', ['kind' => 'user', 'name' => 'Manager', 'email' => 'manager@test.example', 'password' => 'secure-password', 'role' => 'department_manager', 'department_id' => $department->id])->assertSessionHasNoErrors();
        $manager = User::where('email', 'manager@test.example')->firstOrFail();
        $this->assertEquals($department->id, $manager->department_id);
        $this->post('/content', ['kind' => 'report', 'title' => 'Monthly results', 'audience' => 'landlord', 'property_id' => $property->id, 'status' => 'published', 'occupancy' => 95, 'period' => 'September 2026'])->assertSessionHasNoErrors();
        $report = WorkspaceItem::where('title', 'Monthly results')->firstOrFail();
        $this->get(route('content.edit', $report))->assertOk();
        $this->put(route('content.update', $report), ['title' => 'Updated results', 'audience' => 'landlord', 'property_id' => $property->id, 'status' => 'published'])->assertSessionHasNoErrors();
        $this->assertEquals('Updated results', $report->fresh()->title);
        foreach (['user' => $manager, 'property' => $property, 'department' => $department] as $kind => $record) {
            $this->get(route('accounts.edit', ['kind' => $kind, 'id' => $record->id]))->assertOk();
        }
        $this->put(route('accounts.update', ['kind' => 'property', 'id' => $property->id]), ['name' => 'Renamed property', 'total_units' => 25, 'landlords' => []])->assertSessionHasNoErrors();
        $this->assertFalse($landlord->properties()->whereKey($property->id)->exists());
    }

    public function test_all_content_forms_render_and_invalid_content_is_rejected(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::ADMIN]));
        foreach (['document', 'training', 'announcement', 'report', 'approval', 'user', 'property', 'department'] as $kind) {
            $this->get('/content/create?kind='.$kind)->assertOk()->assertSee('name="_token"', false)->assertSee('method="POST"', false);
        }
        $this->post('/content', ['kind' => 'report', 'title' => 'Invalid', 'audience' => 'landlord', 'status' => 'published'])->assertSessionHasErrors('property_id');
        $this->post('/content', ['kind' => 'document', 'title' => 'Invalid', 'audience' => 'staff', 'status' => 'published', 'occupancy' => 101])->assertSessionHasErrors('occupancy');
        $this->assertDatabaseCount('workspace_items', 0);
    }

    public function test_public_registration_cannot_grant_privileged_roles(): void
    {
        foreach (['admin', 'department_manager'] as $role) {
            $this->post('/sign-up', ['name' => 'Attempt', 'email' => $role.'@example.com', 'role' => $role, 'password' => 'password123', 'password_confirmation' => 'password123', 'approval_status' => 'approved'])->assertRedirect(route('login'));
            $this->assertGuest();
            $this->assertDatabaseHas('users', ['email' => $role.'@example.com', 'approval_status' => 'pending']);
        }
        $this->assertDatabaseCount('users', 2);
    }
}
