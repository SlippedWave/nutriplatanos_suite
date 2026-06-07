<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;


class Camera extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'location',
        'box_stock',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'box_stock' => 'integer',
    ];

    /**
     * Get the box movements for this camera.
     */
    public function boxMovements(): HasMany
    {
        return $this->hasMany(BoxMovement::class);
    }

    /**
     * Get the sales for this camera.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function getCurrentStock(): int
    {
        return $this->box_stock + $this->getMovementsDelta() + $this->getAdjustmentsDelta();
    }

    public function getMovementsDelta(): int
    {
        $incoming = $this->boxMovements()->where('movement_type', 'route_to_warehouse')->sum('quantity');
        $outgoing = $this->boxMovements()->where('movement_type', 'warehouse_to_route')->sum('quantity');

        return $incoming - $outgoing;
    }

    public function getAdjustmentsDelta(): int
    {
        return (int) $this->stockAdjustments()->sum('quantity');
    }

    public function stockAdjustments()
    {
        return $this->hasMany(CameraStockAdjustment::class);
    }
}
