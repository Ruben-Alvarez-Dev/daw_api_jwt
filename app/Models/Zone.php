<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Restaurant;
use App\Models\Table;

class Zone extends Model
{
    use HasFactory;

    protected $primaryKey = 'zone_id';

    protected $fillable = [
        'zone_restaurant_id',
        'zone_name'
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class, 'zone_restaurant_id', 'restaurant_id');
    }

    public function tables()
    {
        return $this->hasMany(Table::class, 'table_zone_id');
    }
}
