<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'rfc',
        'active', // Indicates if the customer is active
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime', // Add this for soft deletes
        'box_balance' => 'integer', // Cast box_balance to an integer
        'active' => 'boolean', // Cast active to a boolean
    ];

    /**
     * Get the sales for this customer.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get the notes for this customer.
     */
    public function notes()
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /**
     * Get the box balance for this customer.
     * @return int
     */
    public function getBoxBalance()
    {
        // Check if BoxBalance record exists for this customer
        $boxBalance = $this->boxBalance()->first();
        // Return the current balance if exists, otherwise 0
        return $boxBalance ? $boxBalance->currentBalance() : 0;
    }

    /**
     * Get the box balance relationship.
     */
    public function boxBalance()
    {
        return $this->hasOne(BoxBalance::class);
    }

    /**
     * Get customer initials for avatar
     */
    public function initials(): string
    {
        $words = explode(' ', $this->name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->name, 0, 2));
    }

    /**
     * Check if customer is active
     */
    public function isActive(): bool
    {
        return $this->active;
    }
}
