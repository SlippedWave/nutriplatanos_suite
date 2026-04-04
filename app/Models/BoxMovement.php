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
        $movements = self::where('route_id', $routeId)->get();

        $fromWarehouse = $movements->where('movement_type', 'warehouse_to_route')->sum('quantity');
        $toWarehouse = $movements->where('movement_type', 'route_to_warehouse')->sum('quantity');
        $carriedOver = $movements->where('movement_type', 'route_to_route')->sum('quantity');

        return [
            'from_warehouse' => $fromWarehouse,
            'to_warehouse' => $toWarehouse,
            'carried_over' => $carriedOver,
            'net_on_route' => $fromWarehouse - $toWarehouse + $carriedOver,
        ];
    }

    // Get truck inventory across all routes for a carrier
    public static function getTruckInventory(?int $carrierId = null): array
    {
        $query = self::query();

        if ($carrierId) {
            $query->whereHas('route', function ($q) use ($carrierId) {
                $q->where('carrier_id', $carrierId);
            });
        }

        // Get the latest truck inventory movements
        $latestInventory = $query->where('movement_type', 'truck_inventory')
            ->orderBy('moved_at', 'desc')
            ->first();

        return [
            'boxes_on_truck' => $latestInventory ? $latestInventory->quantity : 0,
            'last_updated' => $latestInventory ? $latestInventory->moved_at : null,
        ];
    }

    // Transfer boxes between routes
    public static function transferToNextRoute(int $fromRouteId, int $toRouteId, int $quantity, array $options = []): void
    {
        // Record boxes leaving previous route
        self::create([
            'route_id' => $fromRouteId,
            'movement_type' => 'route_to_route',
            'quantity' => -$quantity, // Negative for outgoing
            'box_content_status' => $options['status'] ?? 'empty',
            'moved_at' => now(),
            'notes' => "Transferred to route {$toRouteId}",
        ]);

        // Record boxes entering new route
        self::create([
            'route_id' => $toRouteId,
            'movement_type' => 'route_to_route',
            'quantity' => $quantity, // Positive for incoming
            'box_content_status' => $options['status'] ?? 'empty',
            'moved_at' => now(),
            'notes' => "Transferred from route {$fromRouteId}",
        ]);
    }

    // Update truck inventory
    public static function updateTruckInventory(int $routeId, int $quantity, string $action = 'set'): void
    {
        $note = match ($action) {
            'add' => "Added {$quantity} boxes to truck",
            'remove' => "Removed {$quantity} boxes from truck",
            'set' => "Truck inventory set to {$quantity} boxes",
        };

        if ($action === 'add') {
            $currentInventory = self::getTruckInventory();
            $quantity = $currentInventory['boxes_on_truck'] + $quantity;
        } elseif ($action === 'remove') {
            $currentInventory = self::getTruckInventory();
            $quantity = max(0, $currentInventory['boxes_on_truck'] - $quantity);
        }

        self::create([
            'route_id' => $routeId,
            'movement_type' => 'truck_inventory',
            'quantity' => $quantity,
            'box_content_status' => 'empty',
            'moved_at' => now(),
            'notes' => $note,
        ]);
    }
}
