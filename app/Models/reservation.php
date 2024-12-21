<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $primaryKey = 'reservation_id';

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'reservation_tables',
        'reservation_datetime',
        'reservation_status'
    ];

    protected $casts = [
        'reservation_tables' => 'array',
        'reservation_datetime' => 'datetime'
    ];

    // Relación con User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Relación con Restaurant
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id', 'restaurant_id');
    }
}