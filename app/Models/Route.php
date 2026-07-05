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
     * Lightweight list of routes usable as route-to-route transfer counterparts,
     * shaped for passing to the box movements editor.
     *
     * @return array<int, array{id:int, title:?string, carrier_name:?string}>
     */
    public static function routeTransferOptions(int $limit = 100): array
    {
        return self::with('carrier:id,name')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get(['id', 'title', 'carrier_id', 'created_at'])
            ->map(fn (Route $route) => [
                'id' => $route->id,
                'title' => $route->title,
                'carrier_name' => $route->carrier_name,
            ])
            ->all();
    }

    /**
     * Whether an editor box-movement row has all the fields its type requires,
     * so incomplete rows can be filtered out before submitting.
     */
    public static function isCompleteBoxMovementRow(array $row): bool
    {
        if (! isset($row['movement_type'], $row['quantity'], $row['box_content_status']) || (int) $row['quantity'] < 1) {
            return false;
        }

        return match ($row['movement_type']) {
            'warehouse_to_route', 'route_to_warehouse' => ! empty($row['camera_id']),
            'route_to_route' => ! empty($row['related_route_id']) && ! empty($row['transfer_direction']),
            default => true,
        };
    }

    /**
     * Box movements to show on this route's detail view: movements the route
     * owns, plus route-to-route transfers where this route is the counterpart
     * (so the receiving route can see incoming transfers created by the other
     * route). Includes superseded (soft-deleted) rows for the audit history.
     */
    public function boxMovementsForDisplay()
    {
        return BoxMovement::withTrashed()
            ->where(function ($query) {
                $query->where('route_id', $this->id)
                    ->orWhere(function ($counterpart) {
                        $counterpart->where('movement_type', 'route_to_route')
                            ->where('related_route_id', $this->id);
                    });
            })
            ->with(['camera', 'route', 'relatedRoute'])
            ->orderBy('moved_at')
            ->get();
    }

    public function getBoxSummary(): array
    {
        $takenFromCameras     = $this->boxMovements()->where('movement_type', 'warehouse_to_route')->sum('quantity');
        $returnedToCameras    = $this->boxMovements()->where('movement_type', 'route_to_warehouse')->sum('quantity');
        $deliveredToCustomers = $this->sales()->sum('boxes_delivered');
        $returnedByCustomers  = $this->sales()->sum('boxes_returned');

        $transfers      = BoxMovement::routeTransferNet($this->id);
        $sentToRoutes   = $transfers['sent'];
        $receivedRoutes = $transfers['received'];

        return [
            'taken_from_cameras'      => (int) $takenFromCameras,
            'returned_to_cameras'     => (int) $returnedToCameras,
            'delivered_to_customers'  => (int) $deliveredToCustomers,
            'returned_by_customers'   => (int) $returnedByCustomers,
            'sent_to_routes'          => $sentToRoutes,
            'received_from_routes'    => $receivedRoutes,
            'net_on_truck'            => (int) ($takenFromCameras - $returnedToCameras - $deliveredToCustomers + $returnedByCustomers - $sentToRoutes + $receivedRoutes),
        ];
    }

    public function getAvailableBoxesOnTruck(?int $excludeSaleId = null): int
    {
        $taken           = (int) $this->boxMovements()->where('movement_type', 'warehouse_to_route')->sum('quantity');
        $returnedCamera  = (int) $this->boxMovements()->where('movement_type', 'route_to_warehouse')->sum('quantity');
        $delivered       = (int) $this->sales()->when($excludeSaleId, fn($q) => $q->where('id', '!=', $excludeSaleId))->sum('boxes_delivered');
        $returnedByCustomers = (int) $this->sales()->when($excludeSaleId, fn($q) => $q->where('id', '!=', $excludeSaleId))->sum('boxes_returned');

        $transfers = BoxMovement::routeTransferNet($this->id);

        return max(0, $taken - $returnedCamera - $delivered + $returnedByCustomers - $transfers['sent'] + $transfers['received']);
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
