<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Note extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'type',
        'notable_type',
        'notable_id'
    ];

    /**
     * Get the user who created the note
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent notable model (Sale, Route, Customer, etc.)
     */
    public function notable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter notes by type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}
