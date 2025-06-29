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
    const STATUS_PENDING = 'Pendiente';
    const STATUS_IN_PROGRESS = 'En Progreso';
    const STATUS_ARCHIVED = 'Archivada';
    const STATUS_CANCELED = 'Cancelada';
    const STATUS_DELETED = 'Eliminada';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'date',
        'carrier_id',
        'status',
        'archived_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'archived_at' => 'datetime',
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
        return $this->status ?? self::STATUS_PENDING;
    }

    /**
     * Get all available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_IN_PROGRESS,
            self::STATUS_ARCHIVED,
            self::STATUS_CANCELED,
            self::STATUS_DELETED,
        ];
    }

    /**
     * Get status color for UI display.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_ARCHIVED => 'gray',
            self::STATUS_CANCELED => 'red',
            self::STATUS_DELETED => 'red',
            default => 'gray',
        };
    }

    /**
     * Scope to get only archived routes.
     */
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    /**
     * Scope to get only active routes.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
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
