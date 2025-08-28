<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BussinesMovement extends Model
{
    protected $fillable = [
        'bussines_id',
        'club_id',
        'supplier_id',
        'event_club_id',
        'method_payment_id',
        'category_income_id',
        'expense_id',
        'category_egress_id',
        'currency_id',
        'user_id',
        'amount',
        'date',
        'description',
        'type',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function bussines()
    {
        return $this->belongsTo(Bussines::class);
    }

    public function methodPayment()
    {
        return $this->belongsTo(MethodPayment::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function categoryIncome()
    {
        return $this->belongsTo(CategoryIncome::class);
    }

    public function categoryEgress()
    {
        return $this->belongsTo(CategoryEgress::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getMethodPaymentName()
    {
        return $this->methodPayment ? $this->methodPayment->account_holder. ' - '. $this->methodPayment->currency->name . ' - '. $this->methodPayment->entity->name : 'N/A';
    }
}
