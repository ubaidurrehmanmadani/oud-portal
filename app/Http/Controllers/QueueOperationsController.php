<?php

namespace App\Http\Controllers;

use App\Jobs\DeleteUnreferencedUpload;
use App\Jobs\NotifyWorkspacePublication;
use App\Jobs\ProcessPrivateUpload;
use App\Models\AuditEvent;
use App\Notifications\WorkspacePublished;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QueueOperationsController extends Controller
{
    private function supported(): bool
    {
        return config('queue.failed.driver') === 'database-uuids' && config('queue.failed.database') === config('database.default') && Schema::hasTable(config('queue.failed.table', 'failed_jobs'));
    }

    private function retryable($record): bool
    {
        $payload = json_decode($record->payload, true);

        return in_array($payload['displayName'] ?? '', [ProcessPrivateUpload::class, DeleteUnreferencedUpload::class, NotifyWorkspacePublication::class, WorkspacePublished::class], true);
    }

    public function index()
    {
        $supported = $this->supported();
        $failures = $supported ? DB::table(config('queue.failed.table', 'failed_jobs'))->orderByDesc('failed_at')->paginate(20) : null;
        if ($failures) {
            $failures->through(fn ($record) => (object) ['uuid' => $record->uuid, 'queue' => $record->queue, 'failed_at' => $record->failed_at, 'retryable' => $this->retryable($record)]);
        }
        $queue = config('queue.connections.'.config('queue.default'), []);
        $counts = collect();
        if (($queue['driver'] ?? null) === 'database') {
            $connection = DB::connection($queue['connection'] ?? null);
            $table = $queue['table'] ?? 'jobs';
            if ($connection->getSchemaBuilder()->hasTable($table)) {
                $counts = $connection->table($table)->selectRaw('queue, count(*) as total')->groupBy('queue')->pluck('total', 'queue');
            }
        }

        return view('admin.notifications.view-notifications', ['title' => __('operations.title'), 'failures' => $failures, 'counts' => $counts, 'supported' => $supported]);
    }

    public function retry(Request $request, string $uuid)
    {
        $request->validate(['current_password' => ['required', 'current_password:web']]);
        abort_unless($this->supported(), 422);
        DB::transaction(function () use ($request, $uuid) {
            $record = DB::table(config('queue.failed.table', 'failed_jobs'))->where('uuid', $uuid)->lockForUpdate()->first();
            abort_unless($record, 404);
            abort_unless($this->retryable($record), 422);
            abort_unless(Artisan::call('queue:retry', ['id' => [$uuid]]) === 0, 500);
            AuditEvent::create(['user_id' => $request->user()->id, 'event' => 'queue.retried:'.$uuid, 'ip_address' => $request->ip(), 'user_agent' => substr((string) $request->userAgent(), 0, 500)]);
        });

        return back()->with('status', __('workspace.saved'));
    }
}
