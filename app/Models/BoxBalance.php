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
        'client_id',
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
}
