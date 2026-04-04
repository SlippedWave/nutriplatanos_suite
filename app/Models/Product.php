<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the sale details associated with this product.
     */
    public function productLists()
    {
        return $this->hasMany(ProductList::class);
    }

    /**
     * Get the sales associated with this product through product lists.
     */
    public function sales()
    {
        return $this->hasManyThrough(Sale::class, ProductList::class);
    }
}
