<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalePayment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'sale_id',
        'amount',
        'payment_date',
        'payment_method',
        'route_id',
        'user_id',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sale_id' => 'integer',
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'payment_method' => 'string',
        'route_id' => 'integer',
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The possible payment methods.
     */
    const PAYMENT_METHODS = [
        'cash' => 'Efectivo',
        'transfer' => 'Transferencia',
        'check' => 'Cheque',
        'card' => 'Tarjeta',
        'other' => 'Otro',
    ];

    /**
     * Get the sale this payment belongs to.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the route where this payment was collected.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the user who collected this payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get payments for a specific sale.
     */
    public function scopeForSale($query, $saleId)
    {
        return $query->where('sale_id', $saleId);
    }

    /**
     * Scope to get payments for a specific route.
     */
    public function scopeForRoute($query, $routeId)
    {
        return $query->where('route_id', $routeId);
    }

    /**
     * Scope to get payments within a date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Get the payment method label.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }
}
