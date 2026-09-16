<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\User;
use App\Models\WorkspaceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_unassigned_manager_gets_setup_guidance_without_content_or_write_access(): void
    {
        $manager = User::factory()->create(['role' => UserRole::DEPARTMENT_MANAGER]);
        WorkspaceItem::create(['title' => 'Unassigned private draft', 'kind' => 'document', 'audience' => 'staff', 'status' => 'draft']);
        foreach (['en' => 'Department assignment required', 'ar' => 'يلزم تعيين قسم'] as $locale => $message) {
            $this->actingAs($manager)->withSession(['locale' => $locale])->get('/content')->assertOk()
                ->assertSee($message)->assertDontSee('Unassigned private draft')
                ->assertDontSee(route('content.create', ['kind' => 'document']));
        }
        $this->get('/content/create?kind=document')->assertForbidden();
        $this->post('/content', ['kind' => 'document', 'title' => 'Blocked'])->assertForbidden();
        $department = Department::create(['name' => 'Assigned team']);
        $manager->update(['department_id' => $department->id]);
        $this->actingAs($manager->fresh())->get('/content')->assertOk()
            ->assertSee(route('content.create', ['kind' => 'document']))->assertDontSee('Unassigned private draft');
    }

    public function test_manager_creation_links_follow_permissions_and_other_roles_remain_blocked(): void
    {
        $department = Department::create(['name' => 'Team']);
        $manager = User::factory()->create(['role' => UserRole::DEPARTMENT_MANAGER, 'department_id' => $department->id,
            'permission_overrides' => ['manage_documents' => false, 'manage_training' => true, 'manage_announcements' => false]]);
        $this->actingAs($manager)->get('/content')->assertOk()
            ->assertSee(route('content.create', ['kind' => 'training']))
            ->assertDontSee(route('content.create', ['kind' => 'document']))
            ->assertDontSee(route('content.create', ['kind' => 'announcement']));
        foreach ([UserRole::EMPLOYEE, UserRole::LANDLORD] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))->get('/content')->assertForbidden();
        }
        $this->actingAs(User::factory()->create(['role' => UserRole::ADMIN]))->get('/content')->assertOk()
            ->assertSee(route('content.create', ['kind' => 'document']));
    }
}
