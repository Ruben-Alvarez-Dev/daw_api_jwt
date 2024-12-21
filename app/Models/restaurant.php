<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    protected $primaryKey = 'restaurant_id';

    protected $fillable = [
        'restaurant_name',
        'restaurant_zones',
        'restaurant_capacity',
        'restaurant_is_active',
        'restaurant_status'
    ];

    protected $casts = [
        'restaurant_zones' => 'array',
        'restaurant_is_active' => 'boolean',
        'restaurant_capacity' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($restaurant) {
            if (!isset($restaurant->restaurant_zones)) {
                $restaurant->restaurant_zones = ['main room'];
            }
        });
    }
}