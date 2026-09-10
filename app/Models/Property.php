<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Property extends Model
{
    protected $fillable = ['name', 'location', 'type', 'total_units', 'status', 'description'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
