<?php

namespace App\Jobs;

use App\Models\ReportReview;
use App\Models\ReportSubmission;
use App\Models\WorkspaceItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class DeleteUnreferencedUpload implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $timeout = 60;

    public function __construct(public string $path)
    {
        $this->afterCommit();
    }

    public function backoff(): array
    {
        return [10, 60, 180];
    }

    public function handle(): void
    {
        if (! str_starts_with($this->path, 'workspace/') && ! str_starts_with($this->path, 'report-submissions/')) {
            return;
        }
        if (WorkspaceItem::where('file_path', $this->path)->exists() || ReportSubmission::where('file_path', $this->path)->exists()
            || ReportReview::where('snapshot->file_path', $this->path)->exists()) {
            return;
        }
        if (! Storage::disk('local')->delete($this->path)) {
            throw new \RuntimeException('Upload cleanup failed');
        }
    }
}
