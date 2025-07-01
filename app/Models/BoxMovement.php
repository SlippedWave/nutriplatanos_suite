<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoxMovement extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'camera_id',
        'sale_id',
        'movement_type',
        'quantity',
        'box_content_status', // Indicates if the box is empty or full when moved
        'moved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'movement_type' => 'string',
        'quantity' => 'integer',
        'moved_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The possible movement types.
     */
    const MOVEMENT_TYPES = [
        'in' => 'in',
        'out' => 'out',
    ];

    /**
     * The possible box content statuses.
     */
    const BOX_CONTENT_STATUSES = [
        'empty' => 'empty',
        'full' => 'full',
    ];


    /**
     * Get the camera for this box movement.
     */
    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    /**
     * Get the sale for this box movement.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    /**
     * Scope to get only 'in' movements.
     */
    public function scopeIn($query)
    {
        return $query->where('movement_type', 'in');
    }

    /**
     * Scope to get only 'out' movements.
     */
    public function scopeOut($query)
    {
        return $query->where('movement_type', 'out');
    }

    /**
     * Scope to get movements for a specific camera.
     */
    public function scopeForCamera($query, $cameraId)
    {
        return $query->where('camera_id', $cameraId);
    }

    /**
     * Scope to get movements for a specific client.
     */
    public function scopeForSale($query, $saleId)
    {
        return $query->where('sale_id', $saleId);
    }
}
