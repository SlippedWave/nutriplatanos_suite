<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BoxMovement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'camera_id',
        'route_id',
        'related_route_id',
        'transfer_direction',
        'movement_type',
        'quantity',
        'box_content_status',
        'moved_at',
    ];

    protected $casts = [
        'movement_type' => 'string',
        'quantity' => 'integer',
        'moved_at' => 'datetime',
    ];

    const MOVEMENT_TYPES = [
        'warehouse_to_route' => 'Cámara a ruta',     // Boxes from warehouse to route
        'route_to_warehouse' => 'Ruta a cámara',     // Boxes from route back to warehouse
        'route_to_route' => 'Ruta a ruta',             // Boxes carried over to next route
        'truck_inventory' => 'Inventario de camión',           // Boxes remaining on truck
    ];

    // Direction of a route_to_route transfer, relative to the owning route (route_id).
    const TRANSFER_DIRECTIONS = [
        'out' => 'Envía a',     // this route sends boxes to the counterpart
        'in'  => 'Recibe de',   // this route receives boxes from the counterpart
    ];

    public $timestamps = false;

    const BOX_CONTENT_STATUSES = [
        'full' => 'Lleno',
        'empty' => 'Vacío',
    ];

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * The counterpart route in a route-to-route transfer.
     */
    public function relatedRoute(): BelongsTo
    {
        return $this->belongsTo(Route::class, 'related_route_id');
    }

    /**
     * The counterpart route as seen from a given viewing route.
     *
     * A route_to_route transfer is stored as a single row owned by one route
     * (route_id) pointing at the other (related_route_id). When the viewing
     * route owns the row, the counterpart is related_route; when the viewing
     * route is the counterpart of someone else's row, the counterpart is the
     * owner (route).
     */
    public function counterpartRouteFor(int $viewerRouteId): ?Route
    {
        if ($this->movement_type !== 'route_to_route') {
            return null;
        }

        return (int) $this->route_id === $viewerRouteId
            ? $this->relatedRoute
            : $this->route;
    }

    /**
     * Transfer direction ('in'|'out') as seen from a given viewing route.
     *
     * Direction is stored relative to the owning route, so it must be inverted
     * when the viewing route is the counterpart rather than the owner.
     */
    public function transferDirectionFor(int $viewerRouteId): ?string
    {
        if ($this->movement_type !== 'route_to_route' || $this->transfer_direction === null) {
            return null;
        }

        if ((int) $this->route_id === $viewerRouteId) {
            return $this->transfer_direction;
        }

        return $this->transfer_direction === 'out' ? 'in' : 'out';
    }

    // Scopes
    public function scopeToRoute($query)
    {
        return $query->where('movement_type', 'warehouse_to_route');
    }

    public function scopeFromRoute($query)
    {
        return $query->where('movement_type', 'route_to_warehouse');
    }

    public function scopeCarriedOver($query)
    {
        return $query->where('movement_type', 'route_to_route');
    }

    public function scopeTruckInventory($query)
    {
        return $query->where('movement_type', 'truck_inventory');
    }

    // Enhanced inventory calculation
    public static function getRouteBoxInventory(int $routeId): array
    {
        $fromWarehouse = self::where('route_id', $routeId)->where('movement_type', 'warehouse_to_route')->sum('quantity');
        $toWarehouse   = self::where('route_id', $routeId)->where('movement_type', 'route_to_warehouse')->sum('quantity');

        $transfers = self::routeTransferNet($routeId);

        return [
            'from_warehouse' => (int) $fromWarehouse,
            'to_warehouse'   => (int) $toWarehouse,
            'sent_to_routes'      => $transfers['sent'],
            'received_from_routes' => $transfers['received'],
            'net_on_route' => (int) $fromWarehouse - (int) $toWarehouse + $transfers['received'] - $transfers['sent'],
        ];
    }

    /**
     * Net route-to-route box flow for a given route, accounting for movements the
     * route owns (route_id) as well as movements where it is the counterpart
     * (related_route_id). Direction is stored relative to the owning route.
     *
     * @return array{sent:int, received:int}
     */
    public static function routeTransferNet(int $routeId): array
    {
        $ownedOut = (int) self::where('route_id', $routeId)
            ->where('movement_type', 'route_to_route')
            ->where('transfer_direction', 'out')
            ->sum('quantity'); // this route sends

        $ownedIn = (int) self::where('route_id', $routeId)
            ->where('movement_type', 'route_to_route')
            ->where('transfer_direction', 'in')
            ->sum('quantity'); // this route receives

        // Movements owned by the counterpart route but pointing at this route.
        $counterpartOut = (int) self::where('related_route_id', $routeId)
            ->where('movement_type', 'route_to_route')
            ->where('transfer_direction', 'out')
            ->sum('quantity'); // counterpart sends to us -> we receive

        $counterpartIn = (int) self::where('related_route_id', $routeId)
            ->where('movement_type', 'route_to_route')
            ->where('transfer_direction', 'in')
            ->sum('quantity'); // counterpart receives from us -> we send

        return [
            'sent'     => $ownedOut + $counterpartIn,
            'received' => $ownedIn + $counterpartOut,
        ];
    }
}
