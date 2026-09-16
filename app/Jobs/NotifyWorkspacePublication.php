<?php

namespace App\Jobs;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\WorkspaceItem;
use App\Notifications\WorkspacePublished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyWorkspacePublication implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $timeout = 60;

    public function __construct(public int $itemId)
    {
        $this->onQueue('notifications')->afterCommit();
    }

    public function backoff(): array
    {
        return [10, 60, 180];
    }

    public function handle(): void
    {
        $item = WorkspaceItem::find($this->itemId);
        if (! $item || ! in_array($item->status, ['published', 'pending']) || ($item->published_at && $item->published_at->isFuture())) {
            return;
        }
        $users = User::with('profile')->where('approval_status', 'approved')->whereNull('suspended_at');
        if ($item->kind === 'announcement' && $item->target_mode !== 'legacy') {
            match ($item->target_mode) {
                'all' => null,
                'users' => $users->whereIn('id', $item->targetUsers()->select('users.id')),
                'departments' => $users->whereIn('role', [UserRole::EMPLOYEE, UserRole::DEPARTMENT_MANAGER])->whereIn('department_id', $item->targetDepartments()->select('departments.id')),
                'properties' => $users->where('role', UserRole::LANDLORD)->whereHas('properties', fn ($q) => $q->whereIn('properties.id', $item->targetProperties()->select('properties.id'))),
                default => $users->whereRaw('1 = 0'),
            };
        } elseif ($item->audience === 'landlord') {
            $users->where('role', UserRole::LANDLORD)->whereHas('properties', fn ($q) => $q->where('properties.id', $item->property_id));
        } elseif ($item->audience === 'staff') {
            $users->where('role', UserRole::EMPLOYEE)->when($item->department_id, fn ($q) => $q->where('department_id', $item->department_id));
        } else {
            return;
        }
        $users->chunkById(100, function ($recipients) {
            foreach ($recipients as $recipient) {
                $recipient->notify((new WorkspacePublished($this->itemId))->locale($recipient->profile?->preferred_locale ?? config('app.locale')));
            }
        });
    }
}
