<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_restaurant';

    protected $fillable = [
        'name',
        'zones',
        'capacity',
        'isActive',
        'status'
    ];

    protected $casts = [
        'zones' => 'array',
        'isActive' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($restaurant) {
            if (!isset($restaurant->zones)) {
                $restaurant->zones = ['main room'];
            }
        });
    }
}