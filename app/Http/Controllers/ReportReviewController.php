<?php

namespace App\Http\Controllers;

use App\Jobs\NotifyWorkspacePublication;
use App\Models\AuditEvent;
use App\Models\Property;
use App\Models\ReportSubmission;
use App\Models\WorkspaceItem;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ReportReviewController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['status' => ['nullable', Rule::in(['pending', 'returned', 'rejected', 'approved'])]]);

        return view('admin.reports.review', ['title' => __('review.title'), 'isLandlord' => false, 'properties' => collect(), 'selectedProperty' => null,
            'submissions' => ReportSubmission::with(['property', 'author', 'reviews'])->where('status', $data['status'] ?? 'pending')->latest('submitted_at')->paginate(20)->withQueryString()]);
    }

    public function download(int $submission)
    {
        $record = ReportSubmission::findOrFail($submission);
        abort_if($record->status === 'draft', 403);
        abort_unless(Storage::disk('local')->exists($record->file_path), 404);

        return Storage::disk('local')->download($record->file_path, $record->file_name, ['X-Content-Type-Options' => 'nosniff']);
    }

    public function decide(Request $request, int $submission)
    {
        $data = $request->validate(['decision' => ['required', Rule::in(['approved', 'returned', 'rejected'])], 'comment' => 'required_unless:decision,approved|nullable|string|max:5000']);
        try {
            DB::transaction(function () use ($request, $submission, $data) {
                $report = ReportSubmission::lockForUpdate()->findOrFail($submission);
                abort_unless($report->status === 'pending', 409);
                abort_if($report->user_id === $request->user()->id, 403);
                if ($data['decision'] === 'approved') {
                    abort_if($report->file_processing_required && ! $report->file_processed_at, 409, __('review.processing'));
                    Property::lockForUpdate()->findOrFail($report->property_id);
                    abort_if(WorkspaceItem::where('property_id', $report->property_id)->whereDate('report_month', $report->report_month)->exists(), 409, __('review.duplicate'));
                    abort_unless(Storage::disk('local')->exists($report->file_path), 422, __('review.missing_file'));
                    $metrics = array_filter($report->metrics ?? [], fn ($value) => $value !== null && $value !== '');
                    $item = WorkspaceItem::create([
                        'kind' => 'report', 'title' => $report->title, 'body' => $report->notes,
                        'audience' => 'landlord', 'property_id' => $report->property_id, 'created_by' => $request->user()->id,
                        'status' => 'published', 'published_at' => now(), 'report_month' => $report->report_month,
                        'period' => $report->report_month->format('Y-m'), 'file_path' => $report->file_path, 'file_name' => $report->file_name,
                        'occupancy' => $metrics['occupancy'] ?? null, 'net_revenue' => $metrics['net_revenue'] ?? null,
                        'financial_data' => null,
                        'file_processing_required' => $report->file_processing_required, 'file_processed_at' => $report->file_processed_at, 'file_sha256' => $report->file_sha256,
                    ]);
                    $report->published_item_id = $item->id;
                    NotifyWorkspacePublication::dispatch($item->id);
                }
                $report->reviews()->create(['reviewer_id' => $request->user()->id, 'decision' => $data['decision'], 'comment' => $data['comment'] ?? null,
                    'snapshot' => $report->only(['title', 'report_month', 'metrics', 'notes', 'file_path', 'file_name', 'submitted_at'])]);
                $report->status = $data['decision'];
                $report->save();
                AuditEvent::create(['user_id' => $request->user()->id, 'event' => 'report_review.'.$data['decision'].':'.$report->id, 'ip_address' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 500)]);
            });
        } catch (UniqueConstraintViolationException $exception) {
            abort(409, __('review.duplicate'));
        }

        return back()->with('status', __('workspace.saved'));
    }
}
