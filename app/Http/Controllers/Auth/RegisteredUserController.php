<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\LoginEvent;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'roles' => User::roles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(UserRole::values())],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $role = Role::query()->where('code', $validated['role'])->first();

        $user = new User([
            ...$validated,
            'role_id' => $role?->id,
        ]);

        if (in_array($user->role, [UserRole::ADMIN, UserRole::DEPARTMENT_MANAGER], true)) {
            $user->forceFill(['approval_status' => 'pending']);
        }
        $user->save();

        $user->profile()->create([
            'preferred_locale' => app()->getLocale(),
        ]);

        LoginEvent::create([
            'user_id' => $user->id,
            'event' => 'registered',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($user->approval_status === 'pending') {
            return redirect()->route('login')->with('status', __('portal.account_request_saved'));
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route($user->dashboardRouteName());
    }
}
