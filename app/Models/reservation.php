<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_reservation';

    protected $fillable = [
        'id_user',
        'id_restaurant',
        'tables',
        'datetime',
        'status'
    ];

    protected $casts = [
        'tables' => 'array',
        'datetime' => 'datetime'
    ];

    // Relación con User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    // Relación con Restaurant
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class, 'id_restaurant', 'id_restaurant');
    }
}