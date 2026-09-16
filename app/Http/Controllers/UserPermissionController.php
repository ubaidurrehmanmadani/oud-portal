<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\User;
use App\Models\WorkspaceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserPermissionController extends Controller
{
    public function update(Request $request, int $user)
    {
        $request->validate(['current_password' => ['required', 'current_password:web']]);
        DB::transaction(function () use ($request, $user) {
            $account = User::lockForUpdate()->findOrFail($user);
            $keys = array_keys($account->capabilityDefaults());
            abort_if($keys === [], 422);
            $rules = ['overrides' => ['nullable', 'array:'.implode(',', $keys)]];
            foreach ($keys as $key) {
                $rules['overrides.'.$key] = 'nullable|boolean';
            }
            $data = $request->validate($rules);
            $before = $account->permission_overrides;
            $overrides = array_map(fn ($value) => (bool) $value, array_filter($data['overrides'] ?? [], fn ($value) => $value !== null && $value !== ''));
            $account->forceFill(['permission_overrides' => $overrides])->save();
            AuditEvent::create(['user_id' => $request->user()->id, 'event' => 'permissions.updated:'.$account->id, 'changes' => ['before' => $before, 'after' => $overrides], 'ip_address' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 500)]);
        });

        return back()->with('status', __('workspace.saved'));
    }

    public function preview(int $user)
    {
        $account = User::findOrFail($user);
        $items = WorkspaceItem::visibleTo($account)->with(['department', 'property'])->when($account->suspended_at || $account->approval_status !== 'approved', fn ($q) => $q->whereRaw('1=0'))->latest()->paginate(20);

        return view('admin.users.preview', ['title' => __('permissions.preview'), 'account' => $account, 'items' => $items, 'isLandlord' => false, 'properties' => collect(), 'selectedProperty' => null]);
    }
}
