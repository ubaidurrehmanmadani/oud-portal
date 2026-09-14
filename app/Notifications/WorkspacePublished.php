<?php

namespace App\Notifications;

use App\Models\WorkspaceItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkspacePublished extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $timeout = 30;

    public function __construct(public int $itemId)
    {
        $this->onQueue('notifications')->afterCommit();
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function backoff(): array
    {
        return [10, 60, 180];
    }

    public function shouldSend($notifiable, string $channel): bool
    {
        return $notifiable->approval_status === 'approved' && $notifiable->suspended_at === null
            && WorkspaceItem::visibleTo($notifiable)->whereKey($this->itemId)->exists();
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)->subject(__('delivery.subject'))->line(__('delivery.message'))
            ->action(__('workspace.open'), route('workspace.show', $this->itemId));
    }
}
