<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Zone;
use App\Models\Reservation;

class Restaurant extends Model
{
    use HasFactory;

    protected $primaryKey = 'restaurant_id';

    protected $fillable = [
        'restaurant_name',
        'restaurant_supervisor_id',
        'restaurant_max_capacity',
        'restaurant_starttime',
        'restaurant_endtime',
        'restaurant_intervals'
    ];

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'restaurant_supervisor_id');
    }

    public function zones()
    {
        return $this->hasMany(Zone::class, 'zone_restaurant_id');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'reservation_restaurant_id');
    }
}
