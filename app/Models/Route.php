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

    /**
     * Boxes moved between this route and the cameras, optionally restricted to
     * the state the boxes were in when moved ('full'|'empty').
     */
    private function cameraBoxTotal(string $movementType, ?string $contentStatus = null): int
    {
        return (int) $this->boxMovements()
            ->where('movement_type', $movementType)
            ->when($contentStatus, fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('box_content_status', $contentStatus))
            ->sum('quantity');
    }

    public function getBoxSummary(): array
    {
        $takenFromCameras     = $this->cameraBoxTotal('warehouse_to_route');
        $returnedToCameras    = $this->cameraBoxTotal('route_to_warehouse');
        $deliveredToCustomers = (int) $this->sales()->sum('boxes_delivered');
        $returnedByCustomers  = (int) $this->sales()->sum('boxes_returned');

        $transfers      = BoxMovement::routeTransferNet($this->id);
        $sentToRoutes   = $transfers['sent'];
        $receivedRoutes = $transfers['received'];

        return [
            'taken_from_cameras'      => $takenFromCameras,
            'returned_to_cameras'     => $returnedToCameras,
            'delivered_to_customers'  => $deliveredToCustomers,
            'returned_by_customers'   => $returnedByCustomers,
            'sent_to_routes'          => $sentToRoutes,
            'received_from_routes'    => $receivedRoutes,
            'net_on_truck'            => $takenFromCameras - $returnedToCameras - $deliveredToCustomers + $returnedByCustomers - $sentToRoutes + $receivedRoutes,
            'net_full_on_truck'       => $this->netFullBoxesOnTruck(),
            'net_empty_on_truck'      => $this->netEmptyBoxesOnTruck(),
        ];
    }

    /**
     * Boxes with product currently on the truck — what is still available to
     * deliver. Boxes delivered to customers always leave full, so they only
     * draw this figure down; boxes returned by customers always come back
     * empty (refunded product is waste, never restocked) and never add to it.
     *
     * Deliberately unclamped: a negative result means the movement records
     * disagree with the sales, which the summary should surface rather than
     * hide behind a zero.
     */
    public function netFullBoxesOnTruck(?int $excludeSaleId = null): int
    {
        $transfers = BoxMovement::routeTransferNet($this->id, 'full');

        $delivered = (int) $this->sales()
            ->when($excludeSaleId, fn ($q) => $q->where('id', '!=', $excludeSaleId))
            ->sum('boxes_delivered');

        return $this->cameraBoxTotal('warehouse_to_route', 'full')
            - $this->cameraBoxTotal('route_to_warehouse', 'full')
            + $transfers['received'] - $transfers['sent']
            - $delivered;
    }

    /**
     * Empty boxes currently on the truck. They hold no product, but the carrier
     * stays custodially responsible for them, so they are reported separately
     * rather than folded into the deliverable count.
     */
    public function netEmptyBoxesOnTruck(): int
    {
        $transfers = BoxMovement::routeTransferNet($this->id, 'empty');

        return $this->cameraBoxTotal('warehouse_to_route', 'empty')
            - $this->cameraBoxTotal('route_to_warehouse', 'empty')
            + $transfers['received'] - $transfers['sent']
            + (int) $this->sales()->sum('boxes_returned');
    }

    /**
     * Ceiling for how many boxes a sale may deliver. Only boxes with product
     * can be delivered, so empties on the truck do not raise it.
     */
    public function getAvailableBoxesOnTruck(?int $excludeSaleId = null): int
    {
        return max(0, $this->netFullBoxesOnTruck($excludeSaleId));
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
