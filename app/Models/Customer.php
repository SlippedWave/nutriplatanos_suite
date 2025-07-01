<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'box_balance' => 'integer', // Cast box_balance to an integer
        'active' => 'boolean', // Cast is_active to a boolean
    ];

    /**
     * Get the sales for this customer.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
