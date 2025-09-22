<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventMovementView extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'event_movements_view';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'bussines_id',
        'event_id',
        'club_id',
        'method_payment_id',
        'category_income_id',
        'category_egress_id',
        'currency_id',
        'supplier_id',
        'expense_id',
        'amount',
        'date',
        'description',
        'status',
        'type',
        'user_id',
        'created_at',
        'updated_at',
        'club_name',
        'club_logo',
        'club_cuit',
        'club_responsible',
        'club_phone',
        'club_email',
        'currency_name',
        'currency_symbol',
        'method_payment_account_holder',
        'method_payment_account_number',
        'method_payment_cbu_cvu',
        'method_payment_alias',
        'method_payment_type_account',
        'method_payment_initial_balance',
        'method_payment_current_balance',
        'entity_name',
        'supplier_name',
        'supplier_representant',
        'supplier_phone',
        'supplier_description',
        'account_receivable_payment_id',
        'account_receivable_payment_amount',
        'account_receivable_payment_date',
        'account_payable_payment_id',
        'account_payable_payment_amount',
        'account_payable_payment_date'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'method_payment_initial_balance' => 'decimal:2',
        'method_payment_current_balance' => 'decimal:2',
        'account_receivable_payment_amount' => 'decimal:2',
        'account_payable_payment_amount' => 'decimal:2',
        'account_receivable_payment_date' => 'date',
        'account_payable_payment_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}
