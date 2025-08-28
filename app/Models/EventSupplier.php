<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventSupplier extends Model
{
    protected $fillable = ['event_id', 'supplier_id'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

}
