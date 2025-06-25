<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryEgress extends Model
{
    protected $fillable = ['name'];

    public function eventMovements(): HasMany
    {
        return $this->hasMany(EventMovement::class, 'category_egress_id', 'id');
    }
}
