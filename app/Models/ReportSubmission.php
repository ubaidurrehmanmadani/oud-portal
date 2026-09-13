<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSubmission extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['report_month' => 'date', 'submitted_at' => 'datetime', 'metrics' => 'array'];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
