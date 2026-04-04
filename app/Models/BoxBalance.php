<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoxBalance extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'delivered_boxes',
        'returned_boxes',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'delivered_boxes' => 'integer',
        'returned_boxes' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // Current debt of boxes from customer
    public function getCurrentBalance(): int
    {
        return $this->delivered_boxes - $this->returned_boxes;
    }

    // Add boxes when customer receives them in a sale
    public function addDeliveredBoxes(int $quantity): void
    {
        $this->increment('delivered_boxes', $quantity);
    }

    // Add boxes when customer returns them
    public function addReturnedBoxes(int $quantity): void
    {
        $this->increment('returned_boxes', $quantity);
    }

    // Reset the box balance for a customer
    public function resetBalance(): void
    {
        $this->delivered_boxes = 0;
        $this->returned_boxes = 0;
        $this->save();
    }
}
