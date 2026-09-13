<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function scopeVisibleTo(Builder $query, User $user): void
    {
        if ($user->role === UserRole::ADMIN) {
            return;
        }
        $query->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
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
    }
}
