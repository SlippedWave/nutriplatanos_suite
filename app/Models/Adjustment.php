<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Adjustment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'sale_id',
        'adjusted_weight_kg',
        'adjustment_type',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'adjusted_weight_kg' => 'decimal:3',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The possible adjustment types.
     */
    const ADJUSTMENT_TYPES = [
        'discount' => 'discount',
        'credit' => 'credit',
    ];

    /**
     * Get the sale for this adjustment.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Scope to get discount adjustments.
     */
    public function scopeDiscount($query)
    {
        return $query->where('adjustment_type', 'discount');
    }

    /**
     * Scope to get credit adjustments.
     */
    public function scopeCredit($query)
    {
        return $query->where('adjustment_type', 'credit');
    }

    /**
     * Scope to get adjustments for a specific sale.
     */
    public function scopeForSale($query, $saleId)
    {
        return $query->where('sale_id', $saleId);
    }

    /**
     * Scope to get adjustments above a certain weight.
     */
    public function scopeAboveWeight($query, $weight)
    {
        return $query->where('adjusted_weight_kg', '>', $weight);
    }

    /**
     * Get the adjustment amount based on sale price and adjusted weight.
     */
    public function getAdjustmentAmountAttribute(): float
    {
        if (!$this->sale) {
            return 0;
        }

        // Assuming the sale has a price_per_kg field
        $pricePerKg = $this->sale->price_per_kg ?? 0;
        return $this->adjusted_weight_kg * $pricePerKg;
    }
}
