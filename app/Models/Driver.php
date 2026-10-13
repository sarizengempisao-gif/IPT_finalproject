<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $primaryKey = 'driver_id';

    protected $fillable = ['full_name', 'email', 'license_no', 'phone', 'status'];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'driver_id', 'driver_id');
    }
}
