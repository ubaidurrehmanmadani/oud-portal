<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\ReportReview;
use App\Models\ReportSubmission;
use App\Models\User;
use App\Models\WorkspaceItem;
use Illuminate\Support\Facades\DB;

class RemoveUnusedUser
{
    // The caller holds the target-user lock within the audited lifecycle transaction.
    public function remove(User $user): void
    {
        abort_unless($user->suspended_at !== null, 409, __('lifecycle.suspend_first'));
        $hasHistory = $user->approved_by !== null || $user->approval_decided_at !== null
            || DB::table('announcement_user')->where('user_id', $user->id)->exists()
            || $user->department_id !== null || $user->properties()->exists()
            || $user->loginEvents()->exists()
            || AuditEvent::where('user_id', $user->id)->exists()
            || ReportSubmission::where('user_id', $user->id)->exists()
            || ReportReview::where('reviewer_id', $user->id)->exists()
            || WorkspaceItem::where('created_by', $user->id)->orWhere('decided_by', $user->id)->exists()
            || User::where('approved_by', $user->id)->exists();
        abort_if($hasHistory, 409, __('lifecycle.retained_history'));

        // Remove recovery tokens so a later account using this email cannot inherit them.
        $broker = config('auth.defaults.passwords');
        DB::connection(config('auth.passwords.'.$broker.'.connection'))
            ->table(config('auth.passwords.'.$broker.'.table', 'password_reset_tokens'))
            ->where('email', $user->email)->delete();
        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)->delete();
        }
        $user->delete();
    }
}
