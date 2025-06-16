<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supplier extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id',
        'category_supplier_id',
        'subcategory_supplier_id',
        'currency_id',
        'name',
        'representant',
        'phone',
        'amount',
        'description',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    public function categorySupplier(): BelongsTo
    {
        return $this->belongsTo(CategorySupplier::class, 'category_supplier_id', 'id');
    }

    public function subcategorySupplier(): BelongsTo
    {
        return $this->belongsTo(SubcategorySupplier::class, 'subcategory_supplier_id', 'id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }
}
