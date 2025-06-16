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
        'amount',
        'date',
        'description',
        'status',
        'type',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bussines_id' => 'integer',
            'event_id' => 'integer',
            'club_id' => 'integer',
            'method_payment_id' => 'integer',
            'category_income_id' => 'integer',
            'category_egress_id' => 'integer',
            'currency_id' => 'integer',
            'supplier_id' => 'integer',
            'expense_id' => 'integer',
            'amount' => 'decimal',
            'date' => 'date',
        ];
    }

    public function bussines(): BelongsTo
    {
        return $this->belongsTo(Bussines::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function methodPayment(): BelongsTo
    {
        return $this->belongsTo(MethodPayment::class);
    }

    public function categoryIncome(): BelongsTo
    {
        return $this->belongsTo(CategoryIncome::class);
    }

    public function categoryEgress(): BelongsTo
    {
        return $this->belongsTo(CategoryEgress::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }
}
