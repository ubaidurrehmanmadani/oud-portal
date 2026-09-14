<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuedResetPassword extends ResetPassword implements ShouldBeEncrypted, ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $timeout = 30;

    public function __construct($token)
    {
        parent::__construct($token);
        $this->onQueue('notifications')->afterCommit();
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }
}
