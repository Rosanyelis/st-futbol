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
     * Permite que un evento tenga varios clubs en diferentes años
     */
    public function clubs(): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'event_clubs')
                    ->withPivot('year')
                    ->withTimestamps();
    }

    /**
     * Obtener clubs de un evento para un año específico
     */
    public function clubsByYear(string $year): BelongsToMany
    {
        return $this->belongsToMany(Club::class, 'event_clubs')
                    ->wherePivot('year', $year)
                    ->withPivot('year')
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
     * Relación con proveedores
     */
    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class, 'event_id', 'id');
    }

    /**
     * Asignar un club a este evento para un año específico
     */
    public function assignClub(Club $club, string $year): void
    {
        $this->clubs()->attach($club->id, ['year' => $year]);
    }

    /**
     * Desasignar un club de este evento para un año específico
     */
    public function detachClub(Club $club, string $year): void
    {
        $this->clubs()->wherePivot('year', $year)->detach($club->id);
    }

    /**
     * Obtener todos los años en los que este evento tiene clubs asignados
     */
    public function getYearsWithClubs(): array
    {
        return $this->clubs()->pluck('year')->unique()->toArray();
    }
}
