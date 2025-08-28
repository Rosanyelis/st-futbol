<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountPayablePayment extends Model
{
    protected $fillable = [
        'account_payable_id',
        'date',
        'amount',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function accountPayable()
    {
        return $this->belongsTo(AccountPayable::class);
    }
}
