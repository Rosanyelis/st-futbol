<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventMovement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bussines_id',
        'event_id',
        'club_id',
        'method_payment_id',
        'category_income_id',
        'category_egress_id',
        'currency_id',
        'supplier_id',
        'expense_id',
        'account_payable_id',
        'account_receivable_id',
        'account_receivable_payment_id',
        'account_payable_payment_id',
        'amount',
        'date',
        'description',
        'status',
        'type',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date:Y-m-d',
        'amount' => 'decimal:2',
    ];

    public function bussines(): BelongsTo
    {
        return $this->belongsTo(Bussines::class, 'bussines_id', 'id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class, 'club_id', 'id');
    }

    public function methodPayment(): BelongsTo
    {
        return $this->belongsTo(MethodPayment::class, 'method_payment_id', 'id');
    }

    public function categoryIncome(): BelongsTo
    {
        return $this->belongsTo(CategoryIncome::class, 'category_income_id', 'id');
    }

    public function categoryEgress(): BelongsTo
    {
        return $this->belongsTo(CategoryEgress::class, 'category_egress_id', 'id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class, 'expense_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function accountPayable(): BelongsTo
    {
        return $this->belongsTo(AccountPayable::class, 'account_payable_id', 'id');
    }

    public function accountReceivable(): BelongsTo
    {
        return $this->belongsTo(AccountReceivable::class, 'account_receivable_id', 'id');
    }

    public function accountReceivablePayment(): BelongsTo
    {
        return $this->belongsTo(AccountReceivablePayment::class, 'account_receivable_payment_id', 'id');
    }

    public function accountPayablePayment(): BelongsTo
    {
        return $this->belongsTo(AccountPayablePayment::class, 'account_payable_payment_id', 'id');
    }
}
