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
        'movement_type',
        'quantity',
        'camera_id',
        'client_id',
        'route_id',
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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
     * Get the camera for this box movement.
     */
    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    /**
     * Get the client for this box movement.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'client_id');
    }

    /**
     * Get the route for this box movement.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
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
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }
}
