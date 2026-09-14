<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportSubmission extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['report_month' => 'date', 'submitted_at' => 'datetime', 'metrics' => 'array'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ReportReview::class)->orderBy('id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
