<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
    
class EventClubMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_club_id',
        'method_payment_id',
        'category_income_id',
        'subcategory_expense_id',
        'category_egress_id',
        'currency_id',
        'supplier_id',
        'user_id',
        'amount',
        'date',
        'description',
        'type',
    ];

    public function eventClub(): BelongsTo
    {
        return $this->belongsTo(EventClub::class, 'event_club_id', 'id');
    }

    public function methodPayment(): BelongsTo
    {
        return $this->belongsTo(MethodPayment::class, 'method_payment_id', 'id');
    }

    public function categoryIncome(): BelongsTo
    {
        return $this->belongsTo(CategoryIncome::class, 'category_income_id', 'id');
    }   

    public function subcategoryExpense(): BelongsTo
    {
        return $this->belongsTo(SubcategoryExpense::class, 'subcategory_expense_id', 'id');
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
