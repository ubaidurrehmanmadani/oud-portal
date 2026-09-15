<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManualTest extends TestCase
{
    use RefreshDatabase;

    public function test_manuals_require_an_approved_active_account(): void
    {
        foreach (['/help/manuals', '/help/manuals/en.pdf', '/help/manuals/ar.pdf?download=1'] as $path) {
            $this->get($path)->assertRedirect(route('login'));
        }
        foreach ([['approval_status' => 'pending'], ['suspended_at' => now()]] as $attributes) {
            $this->actingAs(User::factory()->create($attributes))->get('/help/manuals/en.pdf')->assertForbidden();
        }
    }

    public function test_all_roles_can_find_open_and_download_both_manuals(): void
    {
        foreach (UserRole::cases() as $role) {
            $user = User::factory()->create(['role' => $role]);
            foreach (['en' => 'User manual', 'ar' => 'دليل المستخدم'] as $locale => $label) {
                $this->withSession(['locale' => $locale])->actingAs($user)->get(route($user->dashboardRouteName()))->assertOk()->assertSee(route('manuals.index'));
                $this->get('/help/manuals')->assertOk()->assertSee($label)->assertSee(route('manuals.pdf', $locale));
                $this->get('/help/manuals/'.$locale.'.pdf')->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertHeader('X-Content-Type-Options', 'nosniff');
                $this->get('/help/manuals/'.$locale.'.pdf?download=1')->assertDownload('oud-user-manual-'.$locale.'.pdf');
            }
        }
        $this->get('/help/manuals/fr.pdf')->assertNotFound();
    }
}
