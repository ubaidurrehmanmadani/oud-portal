<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\Department;
use App\Models\Property;
use App\Models\ReportSubmission;
use App\Models\User;
use App\Models\WorkspaceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SetupLifecycleController extends Controller
{
    public function update(Request $request, string $kind, int $id)
    {
        $data = $request->validate(['action' => ['required', Rule::in(['archive', 'restore', 'delete'])], 'current_password' => ['required', 'current_password:web']]);
        DB::transaction(function () use ($request, $kind, $id, $data) {
            $record = ($kind === 'department' ? Department::query() : Property::query())->lockForUpdate()->findOrFail($id);
            if ($data['action'] === 'delete') {
                abort_unless($record->archived_at, 409, __('setup.archive_first'));
                $column = $kind.'_id';
                $assigned = $kind === 'department' ? User::where('department_id', $id)->exists() : $record->users()->exists();
                abort_if(DB::table('announcement_'.$kind)->where($column, $id)->exists() || $assigned || WorkspaceItem::where($column, $id)->exists() || ReportSubmission::where($column, $id)->exists(), 409, __('setup.in_use'));
                $record->delete();
            } else {
                abort_if(($data['action'] === 'archive') === ($record->archived_at !== null), 409);
                $record->forceFill(['archived_at' => $data['action'] === 'archive' ? now() : null])->save();
            }
            AuditEvent::create(['user_id' => $request->user()->id, 'event' => 'setup.'.$kind.'.'.$data['action'].':'.$id, 'ip_address' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 500)]);
        });

        return redirect()->route('admin.'.($kind === 'department' ? 'departments' : 'properties').'.view')->with('status', __('workspace.saved'));
    }
}
