<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WorkspaceItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'decided_at' => 'datetime', 'report_month' => 'date', 'financial_data' => 'array', 'occupancy' => 'decimal:2', 'net_revenue' => 'decimal:2', 'leased_area' => 'decimal:2', 'amount' => 'decimal:2'];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function targetUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_user');
    }

    public function targetDepartments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'announcement_department');
    }

    public function targetProperties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'announcement_property');
    }

    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if ($user->role === UserRole::ADMIN) {
            return;
        }
        if ($user->role === UserRole::LANDLORD) {
            if (! $user->allows('view_reports')) {
                $query->where('kind', '!=', 'report');
            }
            if (! $user->allows('view_documents')) {
                $query->whereNotIn('kind', ['document', 'training']);
            }
        }
        $query->where(fn (Builder $q) => $q->where('file_processing_required', false)->orWhereNotNull('file_processed_at'));
        $query->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
        $query->where(function (Builder $audience) use ($user) {
            $audience->where(function (Builder $query) use ($user) {
                $query->where('target_mode', 'legacy');
                if ($user->role === UserRole::LANDLORD) {
                    $query->where('audience', 'landlord')
                        ->whereIn('property_id', $user->properties()->select('properties.id'))
                        ->where(function (Builder $visible) {
                            $visible->where('status', 'published')->orWhere(function (Builder $approval) {
                                $approval->where('kind', 'approval')->whereIn('status', ['pending', 'approved', 'rejected']);
                            });
                        });
                } else {
                    $query->where('audience', 'staff')->whereNull('property_id')->where('status', 'published')
                        ->where(function (Builder $q) use ($user) {
                            $q->whereNull('department_id');
                            if ($user->department_id) {
                                $q->orWhere('department_id', $user->department_id);
                            }
                        });
                }
            })->orWhere(function (Builder $targeted) use ($user) {
                $targeted->where('kind', 'announcement')->where('status', 'published')
                    ->where(function (Builder $targets) use ($user) {
                        $targets->where('target_mode', 'all')
                            ->orWhere(fn (Builder $q) => $q->where('target_mode', 'users')->whereHas('targetUsers', fn ($users) => $users->where('users.id', $user->id)));
                        if ($user->department_id && in_array($user->role, [UserRole::EMPLOYEE, UserRole::DEPARTMENT_MANAGER])) {
                            $targets->orWhere(fn (Builder $q) => $q->where('target_mode', 'departments')->whereHas('targetDepartments', fn ($departments) => $departments->where('departments.id', $user->department_id)));
                        }
                        if ($user->role === UserRole::LANDLORD) {
                            $targets->orWhere(fn (Builder $q) => $q->where('target_mode', 'properties')->whereHas('targetProperties', fn ($properties) => $properties->whereIn('properties.id', $user->properties()->select('properties.id'))));
                        }
                    });
            });
        });
    }
}
