<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Passenger extends Model
{
    use HasFactory;

    protected $primaryKey = 'passenger_id';

    protected $fillable = ['full_name', 'phone', 'email', 'address'];

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'passenger_id', 'passenger_id');
    }
}
