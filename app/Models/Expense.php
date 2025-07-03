<?php

namespace App\Models;

use App\Models\CategoryEgress;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_egress_id',
        'category_expense_id',
        'subcategory_expense_id',
        'description',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category_egress_id' => 'integer',
            'category_expense_id' => 'integer',
            'subcategory_expense_id' => 'integer',
            'description' => 'string',
        ];
    }

    public function categoryEgress(): BelongsTo
    {
        return $this->belongsTo(CategoryEgress::class, 'category_egress_id', 'id');
    }

    public function categoryExpense(): BelongsTo
    {
        return $this->belongsTo(CategoryExpense::class, 'category_expense_id', 'id');
    }

    public function subcategoryExpense(): BelongsTo
    {
        return $this->belongsTo(SubcategoryExpense::class, 'subcategory_expense_id', 'id');
    }
}
