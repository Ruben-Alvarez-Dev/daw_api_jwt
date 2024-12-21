<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $primaryKey = 'table_id';

    protected $fillable = [
        'restaurant_id',
        'table_number',
        'table_capacity',
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