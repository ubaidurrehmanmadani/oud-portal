<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Jobs\NotifyWorkspacePublication;
use App\Models\Department;
use App\Models\Property;
use App\Models\User;
use App\Models\WorkspaceItem;
use App\Notifications\WorkspacePublished;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdminCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_targeted_announcements_enforce_people_groups_and_publication(): void
    {
        Queue::fake();
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $department = Department::create(['name' => 'Team']);
        $employee = User::factory()->create(['department_id' => $department->id]);
        $other = User::factory()->create();
        $landlord = User::factory()->create(['role' => UserRole::LANDLORD]);
        $property = Property::create(['name' => 'Assigned']);
        $landlord->properties()->attach($property);
        foreach (['users' => ['target_users' => [$employee->id]], 'departments' => ['target_departments' => [$department->id]], 'properties' => ['target_properties' => [$property->id]], 'all' => []] as $mode => $targets) {
            $this->actingAs($admin)->post('/content', ['kind' => 'announcement', 'title' => 'Target '.$mode, 'audience' => 'staff', 'status' => 'published', 'target_mode' => $mode] + $targets)->assertSessionHasNoErrors();
            $item = WorkspaceItem::latest('id')->first();
            $this->assertSame($mode, $item->target_mode);
            $recipient = $mode === 'properties' ? $landlord : $employee;
            $this->actingAs($recipient)->get(route('workspace.show', $item))->assertOk();
            if ($mode !== 'all') {
                $this->actingAs($other)->get(route('workspace.show', $item))->assertNotFound();
            }
            $item->update(['published_at' => now()->addDay()]);
            $this->actingAs($recipient)->get(route('workspace.show', $item))->assertNotFound();
        }
        Queue::assertPushed(NotifyWorkspacePublication::class, 4);
    }

    public function test_target_changes_remove_old_recipients_and_notifications_recheck_scope(): void
    {
        Queue::fake();
        Notification::fake();
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $first = User::factory()->create();
        $second = User::factory()->create();
        $payload = ['kind' => 'announcement', 'title' => 'Private', 'audience' => 'staff', 'status' => 'published', 'target_mode' => 'users', 'target_users' => [$first->id]];
        $this->actingAs($admin)->post('/content', $payload)->assertSessionHasNoErrors();
        $item = WorkspaceItem::first();
        $this->put(route('content.update', $item), array_replace($payload, ['target_users' => [$second->id]]))->assertSessionHasNoErrors();
        $this->assertFalse(WorkspaceItem::visibleTo($first)->whereKey($item->id)->exists());
        (new NotifyWorkspacePublication($item->id))->handle();
        Notification::assertNotSentTo($first, WorkspacePublished::class);
        Notification::assertSentTo($second, WorkspacePublished::class);
        $this->post('/content', array_replace($payload, ['target_users' => []]))->assertSessionHasErrors('target_users');
        $manager = User::factory()->create(['role' => UserRole::DEPARTMENT_MANAGER, 'department_id' => Department::create(['name' => 'Manager team'])->id]);
        $this->actingAs($manager)->post('/content', $payload)->assertForbidden();
    }

    public function test_overrides_are_admin_only_and_restrict_manager_and_landlord_actions(): void
    {
        Queue::fake();
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $manager = User::factory()->create(['role' => UserRole::DEPARTMENT_MANAGER, 'department_id' => Department::create(['name' => 'Managed'])->id]);
        $url = route('admin.users.permissions', $manager);
        $this->actingAs($manager)->post($url, ['current_password' => 'password', 'overrides' => ['manage_documents' => 0]])->assertForbidden();
        $this->actingAs($admin)->post($url, ['current_password' => 'wrong', 'overrides' => ['manage_documents' => 0]])->assertSessionHasErrors('current_password');
        $this->post($url, ['current_password' => 'password', 'overrides' => ['invented' => 1]])->assertSessionHasErrors('overrides');
        $this->post($url, ['current_password' => 'password', 'overrides' => ['manage_documents' => 0]])->assertSessionHasNoErrors();
        $this->actingAs($manager->fresh())->post('/content', ['kind' => 'document', 'title' => 'Denied', 'audience' => 'staff', 'status' => 'published'])->assertForbidden();
        $landlord = User::factory()->create(['role' => UserRole::LANDLORD, 'permission_overrides' => ['view_reports' => false, 'download_files' => false, 'decide_approvals' => false]]);
        $property = Property::create(['name' => 'Assigned']);
        $landlord->properties()->attach($property);
        $report = WorkspaceItem::create(['kind' => 'report', 'title' => 'Hidden', 'audience' => 'landlord', 'property_id' => $property->id, 'status' => 'published']);
        $this->actingAs($landlord)->get(route('workspace.show', $report))->assertNotFound();
        $this->get(route('workspace.download', $report))->assertForbidden();
        $this->post(route('workspace.decide', $report), ['decision' => 'approved'])->assertForbidden();
        $this->actingAs($admin)->get(route('admin.users.preview', $landlord))->assertOk()->assertDontSee('Hidden');
    }

    public function test_delegated_employee_lifecycle_preserves_history_and_requires_password(): void
    {
        Queue::fake();
        $department = Department::create(['name' => 'Delegated']);
        $manager = User::factory()->create(['role' => UserRole::DEPARTMENT_MANAGER, 'department_id' => $department->id, 'permission_overrides' => ['manage_employees' => true]]);
        $employee = User::factory()->create(['department_id' => $department->id]);
        $url = route('manager.employees.action', $employee);
        $this->actingAs($manager)->post($url, ['action' => 'suspend', 'current_password' => 'wrong'])->assertSessionHasErrors('current_password');
        $this->post($url, ['action' => 'reset_password', 'current_password' => 'password'])->assertSessionHasNoErrors();
        Queue::assertPushed(SendQueuedNotifications::class);
        $this->post($url, ['action' => 'suspend', 'current_password' => 'password'])->assertSessionHasNoErrors();
        $this->assertNotNull($employee->fresh()->suspended_at);
        $this->assertSame(1, (int) $employee->fresh()->session_generation);
        $this->post($url, ['action' => 'restore', 'current_password' => 'password'])->assertSessionHasNoErrors();
        $this->post($url, ['action' => 'suspend', 'current_password' => 'password'])->assertSessionHasNoErrors();
        $this->post($url, ['action' => 'delete', 'current_password' => 'password', 'confirm_delete' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('users', ['id' => $employee->id]);
        $retained = User::factory()->create(['department_id' => $department->id, 'suspended_at' => now()]);
        $retained->loginEvents()->create(['event' => 'login']);
        $this->post(route('manager.employees.action', $retained), ['action' => 'delete', 'current_password' => 'password', 'confirm_delete' => 1])->assertConflict();
        $this->assertDatabaseHas('users', ['id' => $retained->id, 'department_id' => $department->id]);
    }

    public function test_new_admin_screens_and_landlord_announcements_render_in_both_languages(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $manager = User::factory()->create(['role' => UserRole::DEPARTMENT_MANAGER, 'department_id' => Department::create(['name' => 'Team'])->id, 'permission_overrides' => ['manage_employees' => true]]);
        foreach (['en', 'ar'] as $locale) {
            $this->withSession(['locale' => $locale])->actingAs($admin)->get(route('accounts.edit', ['kind' => 'user', 'id' => $manager->id]))->assertOk()->assertSee(route('admin.users.permissions', $manager));
            $this->get(route('content.create', ['kind' => 'announcement']))->assertOk()->assertSee('target_users[]', false);
            $this->actingAs($manager)->get(route('manager.employees.index'))->assertOk();
            $this->actingAs(User::factory()->create(['role' => UserRole::LANDLORD]))->get('/landlord/announcements')->assertOk();
        }
    }

    public function test_delegation_cannot_escalate_roles_or_cross_departments_and_revokes_immediately(): void
    {
        $department = Department::create(['name' => 'Team']);
        $manager = User::factory()->create(['role' => UserRole::DEPARTMENT_MANAGER, 'department_id' => $department->id, 'permission_overrides' => ['manage_employees' => true]]);
        $other = User::factory()->create();
        $this->actingAs($manager)->post(route('manager.employees.store'), ['name' => 'Created', 'email' => 'created@example.com', 'password' => 'SafePassword123', 'role' => 'admin', 'permission_overrides' => ['manage_employees' => true]])->assertSessionHasNoErrors();
        $created = User::where('email', 'created@example.com')->firstOrFail();
        $this->assertSame(UserRole::EMPLOYEE, $created->role);
        $this->assertSame($department->id, $created->department_id);
        $this->assertTrue(Hash::check('SafePassword123', $created->password));
        $this->assertNull($created->permission_overrides);
        $this->put(route('manager.employees.update', $other), ['name' => 'Intrusion', 'email' => $other->email])->assertNotFound();
        $this->put(route('manager.employees.update', $created), ['name' => 'Updated', 'email' => $created->email, 'role' => 'admin'])->assertSessionHasNoErrors();
        $this->assertSame(UserRole::EMPLOYEE, $created->fresh()->role);
        $manager->forceFill(['permission_overrides' => ['manage_employees' => false]])->save();
        $this->actingAs($manager->fresh())->get(route('manager.employees.index'))->assertForbidden();
    }
}
