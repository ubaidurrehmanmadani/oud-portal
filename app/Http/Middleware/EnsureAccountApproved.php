<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureAccountApproved
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && ($request->user()->approval_status !== 'approved' || $request->user()->suspended_at !== null) && ! $request->is('logout')) {
            abort(403, __('portal.account_approval_required'));
        }

        if ($user = $request->user()) {
            $key = 'auth_generation.'.$user->id;
            if ((int) $request->session()->get($key, 0) !== (int) $user->session_generation) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login');
            }
        }

        return $next($request);
    }
}
