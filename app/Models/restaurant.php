<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Restaurant Model
 * 
 * @property int $restaurant_id
 * @property string $restaurant_name
 * @property string $restaurant_business_name
 * @property string $restaurant_food_type
 * @property int $restaurant_capacity
 * @property string $restaurant_business_email
 * @property string $restaurant_supervisor_email
 * @property string $restaurant_phone
 * @property string $restaurant_description
 * @property array|null $restaurant_pictures
 * @property array $restaurant_zones
 * @property string $restaurant_status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property-read Collection|Table[] $tables
 * @property-read Collection|Reservation[] $reservations
 * 
 * @method static Restaurant create(array $attributes)
 * @method static Restaurant find($id)
 */
class Restaurant extends Model
{
    use HasFactory;

    protected $primaryKey = 'restaurant_id';

    protected $fillable = [
        'restaurant_name',
        'restaurant_business_name',
        'restaurant_food_type',
        'restaurant_capacity',
        'restaurant_business_email',
        'restaurant_supervisor_email',
        'restaurant_phone',
        'restaurant_description',
        'restaurant_pictures',
        'restaurant_zones',
        'restaurant_status'
    ];

    protected $casts = [
        'restaurant_zones' => 'array',
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

    // Relación con Table
    public function tables()
    {
        return $this->hasMany(Table::class, 'restaurant_id', 'restaurant_id');
    }

    // Relación con Reservation
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'restaurant_id', 'restaurant_id');
    }
}