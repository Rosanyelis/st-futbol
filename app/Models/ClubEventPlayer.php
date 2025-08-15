<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClubEventPlayer extends Model
{
    protected $fillable = [
        'club_event_id',
        'club_id',
        'user_id',
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

    public function clubEvent()
    {
        return $this->belongsTo(ClubEvent::class, 'club_event_id', 'id');
    }

    public function club()
    {
        return $this->belongsTo(Club::class, 'club_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
