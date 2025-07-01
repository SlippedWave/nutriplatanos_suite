<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RouteExpense extends Model
{
    //

    /**
     * Get all notes for this route expense
     */
    public function notes()
    {
        return $this->morphMany(Note::class, 'notable');
    }
}
