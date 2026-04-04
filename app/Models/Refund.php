<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'sale_id',
        'refunded_amount',
        'refund_method',
        'reason',
    ];

    protected $casts = [
        'refunded_amount' => 'decimal:2',
        'refund_method' => 'string',
        'reason' => 'string',
    ];

    const REFUND_METHODS = [
        'discount' => 'Descuento',
        'product' => 'Producto',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
