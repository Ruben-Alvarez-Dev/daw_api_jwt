<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Restaurant Table Model
 * 
 * @property int $table_id
 * @property int $restaurant_id
 * @property string $table_name
 * @property int $table_capacity
 * @property string $table_zone
 * @property string $table_status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property-read Restaurant $restaurant
 * 
 * @method static Table create(array $attributes)
 * @method static Table find($id)
 * @method static Collection where($column, $operator = null, $value = null)
 */
class Table extends Model
{
    use HasFactory;

    protected $primaryKey = 'table_id';

    protected $fillable = [
        'restaurant_id',
        'table_name',
        'table_capacity',
        'table_zone',
        'table_status'
    ];

    protected $casts = [
        'table_capacity' => 'integer'
    ];

    // Relación con Restaurant
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class, 'restaurant_id', 'restaurant_id');
    }
}