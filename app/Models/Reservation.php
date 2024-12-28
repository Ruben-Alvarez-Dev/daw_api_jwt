<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Restaurant;

class Reservation extends Model
{
    use HasFactory;

    protected $primaryKey = 'reservation_id';

    protected $fillable = [
        'reservation_user_id',
        'reservation_restaurant_id',
        'reservation_tables_ids',
        'reservation_datetime',
        'reservation_status'
    ];

    protected $casts = [
        'reservation_datetime' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'reservation_user_id');
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class, 'reservation_restaurant_id', 'restaurant_id');
    }

    // Helper method to get tables array
    public function getTablesIdsArray()
    {
        return explode(',', $this->reservation_tables_ids);
    }

    // Helper method to set tables array
    public function setTablesIdsArray(array $ids)
    {
        $this->reservation_tables_ids = implode(',', $ids);
    }
}
