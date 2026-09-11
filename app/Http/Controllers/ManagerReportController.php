<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\ReportSubmission;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ManagerReportController extends Controller
{
    private function context(Request $request): array
    {
        abort_unless($request->user()->canSubmitFinancialReports(), 403);

        return ['title' => __('portal.manager_reports'), 'isLandlord' => false, 'properties' => collect(), 'selectedProperty' => null,
            'availableProperties' => $request->user()->properties()->orderBy('name')->get()];
    }

    private function query(Request $request)
    {
        abort_unless($request->user()->canSubmitFinancialReports(), 403);

        return ReportSubmission::where('user_id', $request->user()->id)
            ->where('department_id', $request->user()->department_id)
            ->whereIn('property_id', $request->user()->properties()->select('properties.id'));
    }

    public function index(Request $request)
    {
        $context = $this->context($request);
        $filters = $request->validate(['q' => 'nullable|string|max:255', 'status' => ['nullable', Rule::in(['draft', 'pending'])]]);
        $query = $this->query($request)->with('property');
        if ($filters['q'] ?? null) {
            $query->where('title', 'like', '%'.$filters['q'].'%');
        }
        if ($filters['status'] ?? null) {
            $query->where('status', $filters['status']);
        }

        return view('manager.reports', $context + ['submissions' => $query->latest()->paginate(20)->withQueryString()]);
    }

    public function create(Request $request)
    {
        return view('manager.upload-report', $this->context($request) + ['record' => new ReportSubmission]);
    }

    public function edit(Request $request, int $submission)
    {
        return view('manager.upload-report', $this->context($request) + ['record' => $this->query($request)->findOrFail($submission)]);
    }

    public function store(Request $request)
    {
        return $this->save($request, new ReportSubmission);
    }

    public function update(Request $request, int $submission)
    {
        return $this->save($request, $this->query($request)->findOrFail($submission));
    }

    private function save(Request $request, ReportSubmission $record)
    {
        $context = $this->context($request);
        abort_if($record->exists && $record->status !== 'draft', 409);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'property_id' => ['required', 'integer', Rule::in($context['availableProperties']->modelKeys())],
            'report_month' => 'required|date_format:Y-m',
            'notes' => 'nullable|string|max:10000',
            'action' => ['required', Rule::in(['draft', 'submit'])],
            'file' => [$record->exists ? 'nullable' : 'required', 'file', 'mimes:xls,xlsx,pdf', 'extensions:xls,xlsx,pdf', 'max:20480'],
            'metrics' => 'nullable|array:occupancy,gross_revenue,net_revenue,rent',
            'metrics.occupancy' => 'nullable|numeric|between:0,100',
            'metrics.gross_revenue' => 'nullable|numeric|between:0,99999999999999',
            'metrics.net_revenue' => 'nullable|numeric|between:-99999999999999,99999999999999',
            'metrics.rent' => 'nullable|numeric|between:0,99999999999999',
        ]);
        $newPath = $request->hasFile('file') ? $request->file('file')->store('report-submissions', 'local') : null;
        $oldPath = $record->file_path;
        try {
            DB::transaction(function () use ($request, $record, $data, $newPath) {
                if ($record->exists) {
                    $locked = $this->query($request)->lockForUpdate()->findOrFail($record->id);
                    abort_if($locked->status !== 'draft', 409);
                }
                $record->fill(collect($data)->except(['file', 'action'])->all());
                $record->report_month = $data['report_month'].'-01';
                $record->user_id = $request->user()->id;
                $record->department_id = $request->user()->department_id;
                $record->status = $data['action'] === 'submit' ? 'pending' : 'draft';
                $record->submitted_at = $record->status === 'pending' ? now() : null;
                if ($newPath) {
                    $record->file_path = $newPath;
                    $record->file_name = basename($request->file('file')->getClientOriginalName());
                }
                $record->save();
                AuditEvent::create(['user_id' => $request->user()->id, 'event' => 'report_submission.'.$record->status.':'.$record->id, 'ip_address' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 500)]);
            });
        } catch (\Throwable $error) {
            if ($newPath) {
                Storage::disk('local')->delete($newPath);
            }
            if ($error instanceof UniqueConstraintViolationException) {
                throw ValidationException::withMessages(['report_month' => __('portal.manager_duplicate')]);
            }
            throw $error;
        }
        if ($newPath && $oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()->route('manager.reports.index')->with('status', __('portal.manager_saved'));
    }

    public function download(Request $request, int $submission)
    {
        $record = $this->query($request)->findOrFail($submission);
        abort_unless(Storage::disk('local')->exists($record->file_path), 404);

        return Storage::disk('local')->download($record->file_path, $record->file_name, ['X-Content-Type-Options' => 'nosniff']);
    }
}
