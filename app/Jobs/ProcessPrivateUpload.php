<?php

namespace App\Jobs;

use App\Models\ReportSubmission;
use App\Models\WorkspaceItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProcessPrivateUpload implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $timeout = 60;

    public function __construct(public string $kind, public int $recordId, public string $path)
    {
        $this->onQueue('uploads')->afterCommit();
    }

    public function backoff(): array
    {
        return [10, 60, 180];
    }

    public function handle(): void
    {
        $model = $this->kind === 'workspace' ? WorkspaceItem::class : ReportSubmission::class;
        $record = $model::find($this->recordId);
        if (! $record || $record->file_path !== $this->path || $record->file_processed_at) {
            return;
        }
        $disk = Storage::disk('local');
        if (! $disk->exists($this->path) || $disk->size($this->path) > 51200 * 1024) {
            throw new \RuntimeException('Private upload missing or exceeds processing limit');
        }
        $stream = $disk->readStream($this->path);
        if (! is_resource($stream)) {
            throw new \RuntimeException('Private upload cannot be read');
        }
        try {
            $hash = hash_init('sha256');
            hash_update_stream($hash, $stream);
            $digest = hash_final($hash);
        } finally {
            fclose($stream);
        }
        DB::transaction(function () use ($model, $digest) {
            $record = $model::lockForUpdate()->find($this->recordId);
            if (! $record || $record->file_path !== $this->path || $record->file_processed_at) {
                return;
            }
            $record->forceFill(['file_sha256' => $digest, 'file_processed_at' => now()])->save();
            if ($this->kind === 'workspace' && in_array($record->kind, ['document', 'report', 'approval']) && in_array($record->status, ['published', 'pending'])) {
                NotifyWorkspacePublication::dispatch($record->id)->delay($record->published_at ?? now());
            }
        });
    }
}
