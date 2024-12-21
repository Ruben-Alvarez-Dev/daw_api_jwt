<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Reservation Model
 * 
 * @property int $reservation_id
 * @property int $user_id
 * @property int $restaurant_id
 * @property array $reservation_tables
 * @property Carbon $reservation_datetime
 * @property string $reservation_status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property-read User $user
 * @property-read Restaurant $restaurant
 * 
 * @method static Reservation create(array $attributes)
 * @method static Reservation find($id)
 * @method static Collection where($column, $operator = null, $value = null)
 */
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