<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AccountApprovalController extends Controller
{
    public function index()
    {
        return view('admin.users.account-requests', ['title' => __('portal.account_requests'),
            'isLandlord' => false, 'properties' => collect(), 'selectedProperty' => null,
            'requests' => User::where('approval_status', 'pending')->oldest()->paginate(20)]);
    }

    public function decide(Request $request, int $user)
    {
        $data = $request->validate(['decision' => ['required', Rule::in(['approved', 'rejected'])]]);
        DB::transaction(function () use ($request, $user, $data) {
            $account = User::lockForUpdate()->findOrFail($user);
            abort_if($account->id === $request->user()->id, 403);
            abort_unless($account->approval_status === 'pending', 409);
            $account->forceFill(['approval_status' => $data['decision'], 'approved_by' => $request->user()->id, 'approval_decided_at' => now()])->save();
            AuditEvent::create(['user_id' => $request->user()->id, 'event' => 'account.'.$data['decision'].':'.$account->id, 'ip_address' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 500)]);
        });

        return back()->with('status', __('portal.account_decision_saved'));
    }
}
