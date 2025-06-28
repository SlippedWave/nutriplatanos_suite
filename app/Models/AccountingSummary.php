<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountingSummary extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'date',
        'period_type',
        'total_sales',
        'total_expenses',
        'net_total',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'total_sales' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'net_total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The possible period types.
     */
    const PERIOD_TYPES = [
        'daily' => 'daily',
        'weekly' => 'weekly',
        'monthly' => 'monthly',
    ];

    /**
     * Scope to get daily summaries.
     */
    public function scopeDaily($query)
    {
        return $query->where('period_type', 'daily');
    }

    /**
     * Scope to get weekly summaries.
     */
    public function scopeWeekly($query)
    {
        return $query->where('period_type', 'weekly');
    }

    /**
     * Scope to get monthly summaries.
     */
    public function scopeMonthly($query)
    {
        return $query->where('period_type', 'monthly');
    }

    /**
     * Scope to get summaries for a date range.
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to get profitable summaries.
     */
    public function scopeProfitable($query)
    {
        return $query->where('net_total', '>', 0);
    }

    /**
     * Scope to get loss summaries.
     */
    public function scopeLoss($query)
    {
        return $query->where('net_total', '<', 0);
    }

    /**
     * Calculate profit margin percentage.
     */
    public function getProfitMarginAttribute(): float
    {
        if ($this->total_sales <= 0) {
            return 0;
        }

        return ($this->net_total / $this->total_sales) * 100;
    }
}
