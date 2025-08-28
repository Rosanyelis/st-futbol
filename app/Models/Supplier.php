<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Supplier extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_supplier_id',
        'subcategory_supplier_id',
        'currency_id',
        'name',
        'representant',
        'phone',
        'description',
    ];

    public function categorySupplier(): BelongsTo
    {
        return $this->belongsTo(CategorySupplier::class, 'category_supplier_id', 'id');
    }

    public function subcategorySupplier(): BelongsTo
    {
        return $this->belongsTo(SubcategorySupplier::class, 'subcategory_supplier_id', 'id');
    }

    /**
     * Relación muchos a muchos con eventos a través de la tabla pivot event_suppliers
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_suppliers')
                    ->withTimestamps();
    }

    /**
     * Asignar un evento a este proveedor
     */
    public function assignEvent(Event $event): void
    {
        $this->events()->attach($event->id);
    }

    /**
     * Desasignar un evento de este proveedor
     */
    public function detachEvent(Event $event): void
    {
        $this->events()->detach($event->id);
    }

    /**
     * Obtener todos los eventos asignados a este proveedor
     */
    public function getAssignedEvents(): array
    {
        return $this->events()->pluck('id')->unique()->toArray();
    }

}
