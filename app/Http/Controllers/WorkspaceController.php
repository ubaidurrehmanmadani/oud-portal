<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Property;
use App\Models\WorkspaceItem;
use App\Support\FinancialReport;
use App\Support\ReferenceReports;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

        if ($data['isLandlord']) {
            $summaries = WorkspaceItem::visibleTo($request->user())->where('kind', 'report')->where('status', 'published')
                ->orderByRaw("CASE WHEN category = 'reference-financial' THEN 1 WHEN report_month IS NOT NULL THEN 0 ELSE 2 END")->orderByRaw("CASE WHEN category = 'reference-financial' THEN report_month END ASC")->orderByDesc('report_month')->latest('published_at')->latest('id')->get()->unique('property_id')->keyBy('property_id');

            return view('workspace.landlord-dashboard', $data + ['title' => __('financial.title'), 'summaries' => $summaries]);
        }

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
        $request->validate(['q' => 'nullable|string|max:200', 'category' => 'nullable|string|max:100', 'period' => 'nullable|string|max:100', 'sort' => ['nullable', Rule::in(['newest', 'oldest', 'title'])], 'status' => ['nullable', Rule::in(['published', 'pending', 'approved', 'rejected'])]]);
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
        $categories = (clone $query)->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
        if ($request->filled('period')) {
            $query->where('period', 'like', '%'.$request->string('period').'%');
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

        $sort = $request->input('sort', 'newest');
        if ($section === 'reports' && ! $request->filled('category')) {
            $query->orderByRaw("CASE WHEN category = 'reference-financial' THEN 1 ELSE 0 END");
        }
        $query->orderBy($sort === 'title' ? 'title' : 'created_at', $sort === 'newest' ? 'desc' : 'asc')->orderBy('id');

        return view('workspace.'.$section, $data + [
            'title' => __('workspace.'.$section), 'section' => $section,
            'items' => $query->paginate(12)->withQueryString(), 'categories' => $categories,
            'propertyItems' => $data['properties']->filter(fn ($property) => (! $data['selectedProperty'] || $property->id === $data['selectedProperty']->id) && (! $request->filled('q') || str_contains(mb_strtolower($property->name.' '.$property->location.' '.$property->type), mb_strtolower($request->input('q'))))),
        ]);
    }

    public function show(Request $request, int $item)
    {
        $record = WorkspaceItem::visibleTo($request->user())->with(['property', 'department'])->findOrFail($item);

        if ($record->kind === 'report' && $record->report_month && in_array($request->user()->role, [UserRole::LANDLORD, UserRole::ADMIN], true)) {
            return redirect()->route($request->user()->role === UserRole::ADMIN ? 'admin.financials' : 'landlord.financials', ['property' => $record->property_id, 'year' => $record->report_month->year, 'month' => $record->report_month->month]);
        }

        return view(in_array($record->kind, ['report', 'approval'], true) ? 'workspace.landlord-item' : 'workspace.detail', $this->context($request) + ['title' => $record->title, 'item' => $record]);
    }

    public function download(Request $request, int $item)
    {
        $record = WorkspaceItem::visibleTo($request->user())->findOrFail($item);
        abort_unless($record->file_path && Storage::disk('local')->exists($record->file_path), 404);

        return Storage::disk('local')->download($record->file_path, $record->file_name);
    }

    public function financials(Request $request, int $property)
    {
        $data = $this->context($request);
        $isAdmin = $request->user()->role === UserRole::ADMIN;
        if ($isAdmin) {
            $data['properties'] = Property::orderBy('name')->get();
        }
        $selectedProperty = $data['properties']->firstWhere('id', $property);
        abort_unless($selectedProperty, 404);
        $request->validate(['year' => 'nullable|integer|between:1900,2100', 'month' => 'nullable|integer|between:1,12']);
        $query = WorkspaceItem::visibleTo($request->user())->where('kind', 'report')->when(! $isAdmin, fn ($query) => $query->where('status', 'published'))
            ->where('property_id', $property)->whereNotNull('report_month');
        $latest = (clone $query)->orderByDesc('report_month')->first();
        $year = (int) $request->input('year', $latest?->report_month->year ?? now()->year);
        $month = (int) $request->input('month', $latest && $latest->category !== 'reference-financial' && $latest->report_month->year === $year ? $latest->report_month->month : 1);
        $history = (clone $query)->whereYear('report_month', $year)->orderBy('report_month')->get()
            ->mapWithKeys(fn ($record) => [$record->report_month->month => new FinancialReport($record)]);
        $report = $history->get($month, new FinancialReport(null));
        $years = (clone $query)->pluck('report_month')->map(fn ($date) => Carbon::parse($date)->year)->push($year)->unique()->sortDesc()->values();

        $financialRoute = $isAdmin ? 'admin.financials' : 'landlord.financials';
        $referenceContent = $report->record ? (new ReferenceReports)->content($report->record, $data['properties'], $financialRoute) : null;

        return view($referenceContent ? 'workspace.reference-financial-report' : 'workspace.financial-report', array_replace($data, compact('selectedProperty', 'year', 'month', 'history', 'report', 'years', 'referenceContent')) + [
            'title' => $selectedProperty->name.' · '.__('financial.title'), 'section' => 'reports', 'isFinancialReport' => true,
            'financialRoute' => $financialRoute,
        ]);
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
        $properties = $isLandlord ? $request->user()->properties()->orderBy('name')->get()->sortBy(fn ($property) => array_search($property->name, array_values(ReferenceReports::PROPERTIES), true) === false ? 99 : array_search($property->name, array_values(ReferenceReports::PROPERTIES), true))->values() : collect();
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
