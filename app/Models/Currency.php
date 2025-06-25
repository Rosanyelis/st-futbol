<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'symbol',
    ];

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class, 'currency_id', 'id');
    }

    public function clubs(): HasMany
    {
        return $this->hasMany(Club::class, 'currency_id', 'id');
    }
}
