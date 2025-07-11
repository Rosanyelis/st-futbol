<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
    ];

    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class, 'country_id', 'id');
    }

    public function clubs(): HasMany
    {
        return $this->hasMany(Club::class, 'country_id', 'id');
    }

    public function bussines(): HasMany
    {
        return $this->hasMany(Bussines::class, 'country_id', 'id');
    }
}
