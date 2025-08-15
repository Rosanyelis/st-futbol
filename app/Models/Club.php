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
        // 'has_accommodation',
        // 'players_quantity',
        // 'player_price',
        // 'total_players',
        // 'teachers_quantity',
        // 'teacher_price',
        // 'total_teachers',
        // 'companions_quantity',
        // 'companion_price',
        // 'total_companions',
        // 'drivers_quantity',
        // 'driver_price',
        // 'total_drivers',
        // 'liberated_quantity',
        // 'total_people',
        // 'total_amount',
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
     * Permite que un club participe en varios eventos en diferentes años
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_clubs')
                    ->withPivot('year')
                    ->withTimestamps();
    }

    /**
     * Obtener eventos de un club para un año específico
     */
    public function eventsByYear(string $year): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_clubs')
                    ->wherePivot('year', $year)
                    ->withPivot('year')
                    ->withTimestamps();
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

    // /**
    //  * Crear una cuenta por cobrar para este club
    //  */
    // public function createAccountReceivable(Event $event, float $totalAmount, string $dueDate, ?string $notes = null): ClubAccountReceivable
    // {
    //     return $this->accountReceivables()->create([
    //         'event_id' => $event->id,
    //         'currency_id' => $this->currency_id,
    //         'total_amount' => $totalAmount,
    //         'paid_amount' => 0,
    //         'pending_amount' => $totalAmount,
    //         'due_date' => $dueDate,
    //         'created_date' => now()->toDateString(),
    //         'status' => 'Pendiente',
    //         'notes' => $notes,
    //     ]);
    // }

    // /**
    //  * Obtener el total de cuentas por cobrar pendientes
    //  */
    // public function getTotalPendingReceivables(): float
    // {
    //     return $this->accountReceivables()->pending()->sum('pending_amount');
    // }

    // /**
    //  * Obtener el total de cuentas por cobrar vencidas
    //  */
    // public function getTotalOverdueReceivables(): float
    // {
    //     return $this->accountReceivables()->overdue()->sum('pending_amount');
    // }
}
