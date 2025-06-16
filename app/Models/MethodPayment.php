<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MethodPayment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_method_payment_id',
        'entity_id',
        'currency_id',
        'account_holder',
        'account_number',
        'cbu_cvu',
        'alias',
        'type_account',
        'initial_balance',
        'current_balance',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category_method_payment_id' => 'integer',
            'entity_id' => 'integer',
            'currency_id' => 'integer',
            'initial_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
        ];
    }

    public function categoryMethodPayment(): BelongsTo
    {
        return $this->belongsTo(CategoryMethodPayment::class);
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
