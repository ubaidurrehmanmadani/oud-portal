<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAccountApproved
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->user()->approval_status !== 'approved' && ! $request->is('logout')) {
            abort(403, __('portal.account_approval_required'));
        }

        return $next($request);
    }
}
