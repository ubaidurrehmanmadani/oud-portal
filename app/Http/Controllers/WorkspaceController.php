<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\WorkspaceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class WorkspaceController extends Controller
{
    public function dashboard(Request $request)
    {
        $expected = ['dashboard.admin' => UserRole::ADMIN, 'dashboard.manager' => UserRole::DEPARTMENT_MANAGER, 'dashboard.employee' => UserRole::EMPLOYEE, 'dashboard.landlord' => UserRole::LANDLORD][$request->route()->getName()];
        abort_unless($request->user()->role === $expected, 403);
        $data = $this->context($request);
        $query = WorkspaceItem::visibleTo($request->user());
        if ($data['selectedProperty']) {
            $query->where('property_id', $data['selectedProperty']->id);
        }
        $counts = (clone $query)->selectRaw('kind, count(*) as total')->groupBy('kind')->pluck('total', 'kind');

        return view('workspace.dashboard', $data + [
            'title' => $expected === UserRole::LANDLORD && $request->routeIs('dashboard.landlord')
                ? 'Property dashboard'
                : __('portal.'.match ($expected) {
                UserRole::ADMIN => 'admin_dashboard', UserRole::DEPARTMENT_MANAGER => 'manager_dashboard', UserRole::EMPLOYEE => 'employee_dashboard', UserRole::LANDLORD => 'landlord_dashboard'
            }),
            'counts' => $counts,
            'latestReport' => (clone $query)->where('kind', 'report')->where('status', 'published')->latest('published_at')->latest('id')->first(),
            'reportHistory' => Schema::hasColumn('workspace_items', 'occupancy')
                ? (clone $query)->where('kind', 'report')->where('status', 'published')->whereNotNull('occupancy')->latest('published_at')->latest('id')->limit(12)->get()->sortBy('published_at')->values()
                : collect(),
            'pendingApprovals' => (clone $query)->where('kind', 'approval')->where('status', 'pending')->count(),
            'recent' => (clone $query)->latest()->limit(6)->get(),
        ]);
    }

    public function index(Request $request, string $section)
    {
        $data = $this->context($request);
        $landlord = $data['isLandlord'];
        abort_unless(in_array($section, $landlord ? ['properties', 'reports', 'documents', 'approvals'] : ['documents', 'training', 'announcements', 'search'], true), 404);
        $request->validate(['q' => 'nullable|string|max:200', 'category' => 'nullable|string|max:100', 'status' => ['nullable', Rule::in(['published', 'pending', 'approved', 'rejected'])]]);
        $query = WorkspaceItem::visibleTo($request->user())->with(['property', 'department']);
        if ($data['selectedProperty']) {
            $query->where('property_id', $data['selectedProperty']->id);
        }
        if ($section !== 'search') {
            $query->where('kind', $this->kind($section));
            if ($section === 'reports') {
                $query->where(function ($reportQuery) {
                    $reportQuery->whereNull('category')->orWhere('category', '!=', 'dashboard-history');
                });
            }
        } else {
            $query->whereIn('kind', ['document', 'training', 'announcement']);
        }
        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(fn ($q) => $q->where('title', 'like', $term)->orWhere('body', 'like', $term));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('workspace.'.$section, $data + [
            'title' => __('workspace.'.$section), 'section' => $section,
            'items' => $query->latest()->paginate(12)->withQueryString(),
        ]);
    }

    public function show(Request $request, int $item)
    {
        $record = WorkspaceItem::visibleTo($request->user())->with(['property', 'department'])->findOrFail($item);

        return view('workspace.detail', $this->context($request) + ['title' => $record->title, 'item' => $record]);
    }

    public function download(Request $request, int $item)
    {
        $record = WorkspaceItem::visibleTo($request->user())->findOrFail($item);
        abort_unless($record->file_path && Storage::disk('local')->exists($record->file_path), 404);

        return Storage::disk('local')->download($record->file_path, $record->file_name);
    }

    public function decide(Request $request, int $item)
    {
        abort_unless($request->user()->role === UserRole::LANDLORD, 403);
        $data = $request->validate(['decision' => ['required', Rule::in(['approved', 'rejected'])], 'comment' => 'nullable|string|max:5000']);
        DB::transaction(function () use ($request, $item, $data) {
            $record = WorkspaceItem::visibleTo($request->user())->where('kind', 'approval')->lockForUpdate()->findOrFail($item);
            abort_unless($record->status === 'pending', 409, __('workspace.already_decided'));
            $record->update(['status' => $data['decision'], 'decision_comment' => $data['comment'] ?? null, 'decided_by' => $request->user()->id, 'decided_at' => now()]);
        });

        return back()->with('status', __('workspace.decision_saved'));
    }

    private function kind(string $section): string
    {
        return ['documents' => 'document', 'training' => 'training', 'announcements' => 'announcement', 'reports' => 'report', 'approvals' => 'approval', 'properties' => 'property'][$section];
    }

    private function context(Request $request): array
    {
        $isLandlord = $request->user()->role === UserRole::LANDLORD;
        if ($request->routeIs('landlord.*')) {
            abort_unless($isLandlord, 403);
        }
        if ($request->routeIs('staff.*')) {
            abort_if($isLandlord, 403);
        }
        $properties = $isLandlord ? $request->user()->properties()->orderBy('name')->get() : collect();
        $selectedProperty = null;
        if ($isLandlord && $request->filled('property')) {
            $request->validate(['property' => 'integer']);
            $selectedProperty = $properties->firstWhere('id', (int) $request->input('property'));
            abort_unless($selectedProperty, 404);
        } elseif ($isLandlord && $request->routeIs('dashboard.landlord')) {
            $selectedProperty = $properties->first();
        }

        return compact('isLandlord', 'properties', 'selectedProperty');
    }
}
