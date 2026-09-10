<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\ContentController;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\LoginEvent;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkspaceItem;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function create(string $module)
    {
        $kind = ['users' => 'user', 'departments' => 'department', 'properties' => 'property', 'documents' => 'document', 'academy' => 'training', 'reports' => 'report', 'approvals' => 'approval', 'announcements' => 'announcement'][$module] ?? null;
        if ($kind) {
            request()->query->set('kind', $kind);
            $data = app(ContentController::class)->create(request())->getData();
            $page = ['users' => 'create-user', 'departments' => 'create-department', 'properties' => 'create-property', 'documents' => 'upload-document', 'academy' => 'upload-training', 'reports' => 'create-report', 'approvals' => 'create-approval', 'announcements' => 'create-announcement'][$module];

            return view('admin.'.$module.'.'.$page, $data);
        }
        abort_unless(in_array($module, ['permissions', 'notifications', 'integrations', 'settings']), 404);

        return redirect()->route('admin.'.$module.'.view');
    }

    public function users(): View
    {
        return $this->screen('users', 'admin.users.view-users');
    }

    public function permissions(): View
    {
        return $this->screen('permissions', 'admin.permissions.view-permissions');
    }

    public function departments(): View
    {
        return $this->screen('departments', 'admin.departments.view-departments');
    }

    public function properties(): View
    {
        return $this->screen('properties', 'admin.properties.view-properties');
    }

    public function documents(): View
    {
        return $this->screen('documents', 'admin.documents.view-documents');
    }

    public function academy(): View
    {
        return $this->screen('academy', 'admin.academy.view-training');
    }

    public function reports(): View
    {
        return $this->screen('reports', 'admin.reports.view-reports');
    }

    public function approvals(): View
    {
        return $this->screen('approvals', 'admin.approvals.view-approvals');
    }

    public function announcements(): View
    {
        return $this->screen('announcements', 'admin.announcements.view-announcements');
    }

    public function notifications(): View
    {
        return $this->screen('notifications', 'admin.notifications.view-notifications');
    }

    public function auditLogs(): View
    {
        return $this->screen('audit', 'admin.audit.view-audit-logs');
    }

    public function integrations(): View
    {
        return $this->screen('integrations', 'admin.integrations.view-integrations');
    }

    public function settings(): View
    {
        return $this->screen('settings', 'admin.settings.view-settings');
    }

    private function screen(string $module, string $view): View
    {
        $kind = ['documents' => 'document', 'academy' => 'training', 'reports' => 'report', 'approvals' => 'approval', 'announcements' => 'announcement'][$module] ?? null;
        $query = match ($module) {
            'users' => User::with('department'),
            'departments' => Department::query(),
            'properties' => Property::query(),
            'permissions' => Role::with('permissions'),
            'audit' => LoginEvent::with('user'),
            default => $kind ? WorkspaceItem::where('kind', $kind)->with(['department', 'property']) : null,
        };
        request()->validate(['q' => 'nullable|string|max:200']);
        if ($query && request()->filled('q')) {
            $column = $kind ? 'title' : ($module === 'audit' ? 'event' : 'name');
            $query->where($column, 'like', '%'.request('q').'%');
        }
        $records = $query?->latest()->paginate(20)->withQueryString();
        $columns = match ($module) {
            'users' => [__('workspace.name'), __('workspace.email'), __('workspace.role'), __('workspace.department')],
            'departments' => [__('workspace.name'), __('workspace.body')],
            'properties' => [__('workspace.name'), __('workspace.location'), __('workspace.units')],
            'permissions' => [__('workspace.role'), __('portal.permissions')],
            'audit' => [__('workspace.email'), __('workspace.action'), __('workspace.updated')],
            default => [__('workspace.title'), __('workspace.audience'), __('workspace.status')],
        };
        $rows = $records?->getCollection()->map(fn ($record) => match ($module) {
            'users' => [$record->name, $record->email, $record->role->label(), $record->department?->name ?? '—'],
            'departments' => [$record->name, $record->description],
            'properties' => [$record->name, $record->location, $record->total_units],
            'permissions' => [$record->code->label(), $record->permissions->pluck('name')->implode(', ') ?: __('workspace.role_policy')],
            'audit' => [$record->user?->email ?? '—', $record->event, $record->created_at->format('d M Y H:i')],
            default => [$record->title, $record->property?->name ?? $record->department?->name ?? __('workspace.'.$record->audience), __('workspace.'.$record->status)],
        })->all() ?? [];
        if ($module === 'settings') {
            $columns = [__('workspace.name'), __('workspace.details')];
            $rows = [[__('workspace.language'), config('app.locale')], [__('workspace.timezone'), config('app.timezone')], [__('workspace.database'), config('database.default')], [__('workspace.session'), config('session.driver')]];
        }

        return view($view, [
            'title' => __('portal.'.match ($module) {
                'audit' => 'audit_logs', 'academy' => 'oud_academy', default => $module
            }),
            'section' => 'admin_'.$module, 'module' => $module, 'records' => $records,
            'columns' => $columns, 'rows' => $rows,
            'createKind' => $kind ?? ['users' => 'user', 'departments' => 'department', 'properties' => 'property'][$module] ?? null,
        ]);
    }
}
