<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoxBalanceAdjustment extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'quantity',
        'reason',
    ];

    protected $casts = [
        'customer_id' => 'integer',
        'user_id'     => 'integer',
        'quantity'    => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
