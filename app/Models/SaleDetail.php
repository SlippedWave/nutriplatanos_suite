<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    //
    protected $fillable = [
        'sale_id',
        'product_id',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
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
     *
     * @return void
     */
    public function calculateTotalPrice()
    {
        $this->total_price = $this->quantity * $this->price_per_unit;
        $this->save();
    }
}
