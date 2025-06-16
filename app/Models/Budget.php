<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = [
        'event_id',
        'bussines_id',
        'start_date',
        'end_date',
        'total_amount',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    public function bussines()
    {
        return $this->belongsTo(Bussines::class, 'bussines_id', 'id');
    }
}
