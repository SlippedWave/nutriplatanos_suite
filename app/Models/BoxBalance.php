<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoxBalance extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'customer_id', // Foreign key to the Customer model
        'delivered_boxes',
        'returned_boxes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'delivered_boxes' => 'integer',
        'returned_boxes' => 'integer',
    ];

    /**
     * Get the customer for this box balance.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'client_id');
    }

    /**
     * Calculate the current box balance.
     * @return int
     */
    public function currentBalance(): int
    {
        return $this->delivered_boxes - $this->returned_boxes;
    }
}
