<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AccountLifecycleController extends Controller
{
    public function update(Request $request, int $user)
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['suspend', 'restore', 'reset_password'])],
            'current_password' => ['required', 'current_password:web'],
        ]);
        DB::transaction(function () use ($request, $user, $data) {
            // Serialize administrator lifecycle changes so competing requests cannot remove all admins.
            $admins = User::where('role', UserRole::ADMIN)->orderBy('id')->lockForUpdate()->get();
            $actor = $admins->firstWhere('id', $request->user()->id);
            abort_unless($actor && $actor->approval_status === 'approved' && $actor->suspended_at === null, 403);
            $account = User::lockForUpdate()->findOrFail($user);
            if ($data['action'] === 'suspend') {
                abort_if($account->id === $request->user()->id, 422, __('lifecycle.self_suspend'));
                abort_if($account->suspended_at !== null, 409);
                abort_if($account->role === UserRole::ADMIN && $account->approval_status === 'approved'
                    && $admins->whereNull('suspended_at')->where('approval_status', 'approved')->count() <= 1, 422);
                $account->forceFill(['suspended_at' => now(), 'remember_token' => Str::random(60)])->save();
                if (config('session.driver') === 'database') {
                    DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))->where('user_id', $account->id)->delete();
                }
            } elseif ($data['action'] === 'restore') {
                abort_if($account->suspended_at === null, 409);
                $account->forceFill(['suspended_at' => null])->save();
            } else {
                abort_unless($account->approval_status === 'approved' && $account->suspended_at === null, 422);
                $status = Password::sendResetLink(['email' => $account->email]);
                abort_unless($status === Password::ResetLinkSent, 429, __($status));
            }
            AuditEvent::create([
                'user_id' => $request->user()->id,
                'event' => 'account.'.$data['action'].':'.$account->id,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
            ]);
        });

        return back()->with('status', __('lifecycle.saved'));
    }
}
