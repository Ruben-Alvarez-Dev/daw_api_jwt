<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_table';

    protected $fillable = [
        'id_restaurant',
        'number',
        'capacity',
        'status'
    ];

    // Relación con Restaurant
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class, 'id_restaurant', 'id_restaurant');
    }
}