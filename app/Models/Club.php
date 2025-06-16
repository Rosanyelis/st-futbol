<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'supplier_id',
        'event_id',
        'currency_id',
        'name',
        'logo',
        'cuit',
        'responsible',
        'phone',
        'email',
        'country_id',
        'province_id',
        'city_id',
        'has_accommodation',
        'players_quantity',
        'player_price',
        'total_players',
        'teachers_quantity',
        'teacher_price',
        'total_teachers',
        'companions_quantity',
        'companion_price',
        'total_companions',
        'drivers_quantity',
        'driver_price',
        'total_drivers',
        'liberated_quantity',
        'total_people',
        'total_amount',
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

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function clubItems(): HasMany
    {
        return $this->hasMany(ClubItem::class);
    }

    public function eventMovements(): HasMany
    {
        return $this->hasMany(EventMovement::class);
    }
}
