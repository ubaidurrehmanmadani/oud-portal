<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\LoginEvent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        $demoAccounts = [];
        if (app()->environment('local')) {
            $users = User::whereIn('email', config('demo-access.accounts'))->get()->keyBy('email');
            foreach (config('demo-access.accounts') as $role => $email) {
                $user = $users->get($email);
                $demoAccounts[] = ['email' => $email, 'label' => UserRole::from($role)->label(),
                    'ready' => $user && $user->role->value === $role && $user->approval_status === 'approved'
                        && Hash::check(config('demo-access.password'), $user->password)];
            }
        }

        return view('auth.login', compact('demoAccounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        if (Auth::user()->approval_status !== 'approved') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            throw ValidationException::withMessages(['email' => __('portal.account_approval_required')]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        LoginEvent::create([
            'user_id' => $user->id,
            'event' => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->intended(route($user->dashboardRouteName(), absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        if ($request->user()) {
            LoginEvent::create([
                'user_id' => $request->user()->id,
                'event' => 'logout',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
