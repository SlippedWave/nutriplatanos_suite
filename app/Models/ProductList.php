<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductList extends Model
{
    //
    protected $fillable = [
        'listable_id',
        'listable_type',
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
     * Get the sale that owns the ProductList.
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the product associated with the ProductList.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate the total price for this ProductList.
     * Note: total_price is a computed column in the database
     *
     * @return float
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->quantity * $this->price_per_unit;
    }
}
