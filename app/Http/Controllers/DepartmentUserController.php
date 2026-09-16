<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AuditEvent;
use App\Models\Role;
use App\Models\User;
use App\Services\RemoveUnusedUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DepartmentUserController extends Controller
{
    private function authorize(Request $request): void
    {
        abort_unless($request->user()->role === UserRole::DEPARTMENT_MANAGER && $request->user()->allows('manage_employees') && $request->user()->department_id && ! $request->user()->department?->archived_at, 403);
    }

    private function members(Request $request)
    {
        return User::where('role', UserRole::EMPLOYEE)->where('department_id', $request->user()->department_id);
    }

    private function context(): array
    {
        return ['title' => __('permissions.manage_employees'), 'isLandlord' => false, 'properties' => collect(), 'selectedProperty' => null];
    }

    public function index(Request $request)
    {
        $this->authorize($request);

        return view('manager.employees', $this->context() + ['members' => $this->members($request)->orderBy('name')->paginate(20)]);
    }

    public function edit(Request $request, int $user)
    {
        $this->authorize($request);

        return view('manager.employee', $this->context() + ['member' => $this->members($request)->findOrFail($user)]);
    }

    public function store(Request $request)
    {
        $this->authorize($request);
        $data = $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users,email', 'password' => 'required|string|min:12|max:255']);
        DB::transaction(function () use ($request, $data) {
            $member = User::create($data + ['role' => UserRole::EMPLOYEE, 'role_id' => Role::where('code', UserRole::EMPLOYEE)->value('id'), 'department_id' => $request->user()->department_id]);
            $member->profile()->create(['preferred_locale' => app()->getLocale(), 'timezone' => 'Asia/Riyadh']);
            $this->audit($request, $member, 'created');
        });

        return back()->with('status', __('workspace.saved'));
    }

    public function update(Request $request, int $user)
    {
        $this->authorize($request);
        DB::transaction(function () use ($request, $user) {
            $member = $this->members($request)->lockForUpdate()->findOrFail($user);
            $data = $request->validate(['name' => 'required|string|max:255', 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($member->id)]]);
            $member->update($data);
            $this->audit($request, $member, 'updated');
        });

        return back()->with('status', __('workspace.saved'));
    }

    public function action(Request $request, int $user)
    {
        $this->authorize($request);
        $data = $request->validate(['action' => ['required', Rule::in(['suspend', 'restore', 'reset_password', 'delete'])], 'current_password' => ['required', 'current_password:web'], 'confirm_delete' => 'exclude_unless:action,delete|accepted']);
        DB::transaction(function () use ($request, $user, $data) {
            $member = $this->members($request)->lockForUpdate()->findOrFail($user);
            if ($data['action'] === 'suspend') {
                abort_if($member->suspended_at, 409);
                $member->forceFill(['suspended_at' => now(), 'session_generation' => $member->session_generation + 1, 'remember_token' => Str::random(60)])->save();
            } elseif ($data['action'] === 'restore') {
                abort_unless($member->suspended_at, 409);
                $member->forceFill(['suspended_at' => null])->save();
            } elseif ($data['action'] === 'delete') {
                // Department assignment is part of delegated scope, not historical activity.
                $member->department_id = null;
                app(RemoveUnusedUser::class)->remove($member);
            } else {
                abort_unless(! $member->suspended_at && $member->approval_status === 'approved', 422);
                $status = Password::sendResetLink(['email' => $member->email]);
                abort_unless($status === Password::ResetLinkSent, 429, __($status));
            }
            $this->audit($request, $member, $data['action']);
        });

        return redirect()->route('manager.employees.index')->with('status', __('workspace.saved'));
    }

    private function audit(Request $request, User $member, string $event): void
    {
        AuditEvent::create(['user_id' => $request->user()->id, 'event' => 'department_employee.'.$event.':'.$member->id, 'ip_address' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 500)]);
    }
}
