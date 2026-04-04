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

    public function addBoxStock(int $quantity): void
    {
        $this->box_stock += $quantity;
        $this->save();
    }

    public function removeBoxStock(int $quantity): void
    {
        $this->box_stock -= $quantity;
        $this->save();
    }
}
