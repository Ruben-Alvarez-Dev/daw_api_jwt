<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Zone;

class Table extends Model
{
    use HasFactory;

    protected $primaryKey = 'table_id';

    protected $fillable = [
        'table_zone_id',
        'table_capacity',
        'table_name'
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'table_zone_id', 'zone_id');
    }
}
