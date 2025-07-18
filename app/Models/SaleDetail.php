<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    //
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'price_per_unit',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'total_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the sale that owns the SaleDetail.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the product associated with the SaleDetail.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate the total price for this SaleDetail.
     * Note: total_price is a computed column in the database
     *
     * @return float
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->quantity * $this->price_per_unit;
    }
}
