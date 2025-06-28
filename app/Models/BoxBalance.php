<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoxBalance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'client_id',
        'total_boxes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'total_boxes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the client for this box balance.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'client_id');
    }

    /**
     * Scope to get balances with positive boxes.
     */
    public function scopeWithBoxes($query)
    {
        return $query->where('total_boxes', '>', 0);
    }

    /**
     * Scope to get balances with zero boxes.
     */
    public function scopeEmpty($query)
    {
        return $query->where('total_boxes', 0);
    }

    /**
     * Scope to get balances with negative boxes (debt).
     */
    public function scopeInDebt($query)
    {
        return $query->where('total_boxes', '<', 0);
    }

    /**
     * Add boxes to the balance.
     */
    public function addBoxes(int $quantity): void
    {
        $this->increment('total_boxes', $quantity);
    }

    /**
     * Remove boxes from the balance.
     */
    public function removeBoxes(int $quantity): void
    {
        $this->decrement('total_boxes', $quantity);
    }
}
