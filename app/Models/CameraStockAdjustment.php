<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CameraStockAdjustment extends Model
{
    protected $fillable = [
        'camera_id',
        'user_id',
        'quantity',
        'reason',
    ];

    protected $casts = [
        'camera_id' => 'integer',
        'user_id'   => 'integer',
        'quantity'  => 'integer',
    ];

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
