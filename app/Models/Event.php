<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'url_images',
        'start_date',
        'end_date',
        'year',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Relación muchos a muchos con clubs a través de la tabla pivot event_clubs
     */
    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'event_clubs')
                    ->withTimestamps();
    }


    /** 
     * Relación con movimientos de eventos
     */
    public function eventMovements(): HasMany
    {
        return $this->hasMany(EventMovement::class, 'event_id', 'id');
    }

    /**
     * Relación muchos a muchos con proveedores a través de la tabla pivot event_suppliers
     */
    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'event_suppliers')
                    ->withTimestamps();
    }

    /**
     * Asignar un club a este evento
     */
    public function assignClub(Club $club): void
    {
        $this->clubs()->attach($club->id);
    }

    /**
     * Desasignar un club de este evento para un año específico
     */
    public function detachClub(Club $club): void
    {
        $this->clubs()->detach($club->id);
    }

    /**
     * Obtener todos los clubs asignados a este evento
     */
    public function getAssignedClubs(): array
    {
        return $this->clubs()->pluck('id')->unique()->toArray();
    }

    /**
     * Asignar un proveedor a este evento
     */
    public function assignSupplier(Supplier $supplier): void
    {
        $this->suppliers()->attach($supplier->id);
    }

    /**
     * Desasignar un proveedor de este evento
     */
    public function detachSupplier(Supplier $supplier): void
    {
        $this->suppliers()->detach($supplier->id);
    }

    /**
     * Obtener todos los proveedores asignados a este evento
     */
    public function getAssignedSuppliers(): array
    {
        return $this->suppliers()->pluck('id')->unique()->toArray();
    }
}
