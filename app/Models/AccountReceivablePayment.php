<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountReceivablePayment extends Model
{
    protected $fillable = [
        'account_receivable_id',
        'date',
        'amount',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function accountReceivable()
    {
        return $this->belongsTo(AccountReceivable::class);
    }
}
