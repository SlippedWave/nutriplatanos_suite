<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    /**
     * The column defaults to 'empty' in the database while the editor offers
     * 'full' first; setting it here means Eloquent always writes an explicit
     * value, so the two can never disagree for app-created rows.
     */
    protected $attributes = [
        'box_content_status' => 'full',
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

    /**
     * Net route-to-route box flow for a given route, accounting for movements the
     * route owns (route_id) as well as movements where it is the counterpart
     * (related_route_id). Direction is stored relative to the owning route.
     *
     * Pass $contentStatus ('full'|'empty') to count only boxes moved in that
     * state; omit it for the combined total.
     *
     * @return array{sent:int, received:int}
     */
    public static function routeTransferNet(int $routeId, ?string $contentStatus = null): array
    {
        $sum = fn (string $column, string $direction): int => (int) self::where($column, $routeId)
            ->where('movement_type', 'route_to_route')
            ->where('transfer_direction', $direction)
            ->when($contentStatus, fn (Builder $query) => $query->where('box_content_status', $contentStatus))
            ->sum('quantity');

        // Movements this route owns: direction is stored relative to the owner.
        $ownedOut = $sum('route_id', 'out'); // this route sends
        $ownedIn  = $sum('route_id', 'in');  // this route receives

        // Movements owned by the counterpart route but pointing at this route,
        // so their stored direction is inverted from this route's perspective.
        $counterpartOut = $sum('related_route_id', 'out'); // counterpart sends to us -> we receive
        $counterpartIn  = $sum('related_route_id', 'in');  // counterpart receives from us -> we send

        return [
            'sent'     => $ownedOut + $counterpartIn,
            'received' => $ownedIn + $counterpartOut,
        ];
    }
}
