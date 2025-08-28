<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Club extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'logo',
        'cuit',
        'responsible',
        'phone',
        'email',
        'country_id',
        'province_id',
        'city_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            
        ];
    }

    public function categoryIncome(): BelongsTo
    {
        return $this->belongsTo(CategoryIncome::class, 'category_income_id', 'id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    /**
     * Relación muchos a muchos con eventos a través de la tabla pivot event_clubs
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_clubs')
                    ->withTimestamps();
    }

    /**
     * Asignar un evento a este club
     */
    public function assignEvent(Event $event): void
    {
        $this->events()->attach($event->id);
    }

    /**
     * Desasignar un evento de este club
     */
    public function detachEvent(Event $event): void
    {
        $this->events()->detach($event->id);
    }

    /**
     * Obtener todos los eventos asignados a este club
     */
    public function getAssignedEvents(): array
    {
        return $this->events()->pluck('id')->unique()->toArray();
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }


    /**
     * Relación con las cuentas por cobrar
     */
    public function accountReceivables(): HasMany
    {
        return $this->hasMany(ClubAccountReceivable::class, 'club_id', 'id');
    }

    
}
