<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'customer_id',
        'user_id',
        'route_id',
        'payment_status',
        'paid_amount',
        'total_amount',
        'refunded_amount',
        'net_amount_due'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'customer_id' => 'integer',
        'user_id' => 'integer',
        'route_id' => 'integer',
        'payment_status' => 'string',
        'paid_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'net_amount_due' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The possible payment statuses.
     */
    const PAYMENT_STATUSES = [
        'pending' => 'pending',
        'paid' => 'paid',
        'partial' => 'partial',
    ];

    /**
     * Get the customer for this sale.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the route for this sale.
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the user who made this sale.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sale details for this sale.
     */
    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    /**
     * Get the adjustments for this sale.
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(Adjustment::class);
    }

    /**
     * Get all notes for this sale.
     */
    public function notes()
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get all payments for this sale.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    /**
     * Scope to get paid sales.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope to get pending sales.
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    /**
     * Scope to get sales for a specific customer.
     */
    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope to get sales for a specific route.
     */
    public function scopeForRoute($query, $routeId)
    {
        return $query->where('route_id', $routeId);
    }

    /**
     * Scope to get sales within a date range.
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Calculate and return the total amount for this sale.
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->saleDetails->sum('total_price');
    }

    /**
     * Get the total quantity of items in this sale.
     */
    public function getTotalQuantityAttribute(): float
    {
        return $this->saleDetails->sum('quantity');
    }

    /**
     * Get the total number of different products in this sale.
     */
    public function getTotalProductsAttribute(): int
    {
        return $this->saleDetails->count();
    }

    /**
     * Calculate the total amount paid for this sale.
     */
    public function getTotalPaidAttribute(): float
    {
        return $this->payments()->sum('amount');
    }

    /**
     * Calculate the remaining balance for this sale.
     */
    public function getRemainingBalanceAttribute(): float
    {
        $totalAmount = $this->saleDetails->sum('total_price');
        $totalPaid = $this->total_paid;
        return max(0, $totalAmount - $totalPaid);
    }

    /**
     * Check if the sale is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return $this->remaining_balance <= 0.01; // Using small threshold for float comparison
    }

    /**
     * Check if the sale has been overpaid.
     */
    public function isOverpaid(): bool
    {
        $totalAmount = $this->saleDetails->sum('total_price');
        return $this->total_paid > $totalAmount + 0.01; // Using small threshold for float comparison
    }

    /**
     * Get the overpaid amount.
     */
    public function getOverpaidAmountAttribute(): float
    {
        if (!$this->isOverpaid()) {
            return 0.0;
        }

        $totalAmount = $this->saleDetails->sum('total_price');
        return $this->total_paid - $totalAmount;
    }

    /**
     * Update payment status based on payments received.
     */
    public function updatePaymentStatus(): void
    {
        $this->loadMissing(['payments', 'saleDetails']);

        if ($this->payments->isEmpty()) {
            $this->payment_status = 'pending';
        } elseif ($this->isFullyPaid()) {
            $this->payment_status = 'paid';
        } else {
            $this->payment_status = 'partial';
        }

        // Update the legacy paid_amount field for backwards compatibility
        $this->paid_amount = $this->total_paid;

        $this->save();
    }

    /**
     * Scope to get sales with outstanding balances.
     */
    public function scopeWithOutstandingBalance($query)
    {
        return $query->whereHas('saleDetails')
            ->where(function ($q) {
                $q->where('payment_status', 'pending')
                    ->orWhere('payment_status', 'partial');
            });
    }

    /**
     * Scope to get sales that can receive payments.
     */
    public function scopeCanReceivePayments($query)
    {
        return $query->where('payment_status', '!=', 'cancelled');
    }
}
