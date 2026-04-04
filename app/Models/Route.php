<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'carrier_id',
        'title',
        'status',
        'closed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'closed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the carrier (user) assigned to this route.
     */
    public function carrier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'carrier_id');
    }

    /**
     * Get the sales for this route.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get the expenses for this route.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get the box movements for this route.
     */
    public function boxMovements(): HasMany
    {
        return $this->hasMany(BoxMovement::class);
    }

    /**
     * Get the carrier name accessor.
     */
    public function getCarrierNameAttribute(): ?string
    {
        return $this->carrier?->name;
    }

    /**
     * Get the status label accessor.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Activa',
            self::STATUS_CLOSED => 'Cerrada',
        };
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_CLOSED,
        ];
    }

    /**
     * Return if status is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get status color for UI display.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'green',
            self::STATUS_CLOSED => 'yellow',
        };
    }

    /**
     * Scope to get only archived routes.
     */
    public function scopeClosed($query)
    {
        return $query->whereNotNull('closed_at');
    }

    /**
     * Scope to get only active routes.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('closed_at');
    }

    /**
     * Get all notes for this sale.
     */
    public function notes()
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Scope to order by carrier name.
     */
    public function scopeOrderByCarrierName($query, $direction = 'asc')
    {
        return $query->leftJoin('users', 'routes.carrier_id', '=', 'users.id')
            ->orderBy('users.name', $direction)
            ->select('routes.*');
    }
}
