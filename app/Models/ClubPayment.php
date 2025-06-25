<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubPayment extends Model
{
    protected $fillable = [
        'club_id',
        'currency_id',
        'amount',
    ];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'club_id', 'id');
    }

    public function currency(): BelongsTo   
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }
}
