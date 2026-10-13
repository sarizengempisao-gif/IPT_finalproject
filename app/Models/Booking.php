<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $primaryKey = 'booking_id';

    protected $fillable = ['passenger_id', 'driver_id', 'vehicle_id', 'booking_reference', 'pickup_location', 'dropoff_location', 'booking_date', 'booking_time', 'fare', 'status', 'notes'];

    protected function casts(): array
    {
        return ['booking_date' => 'date', 'fare' => 'decimal:2'];
    }

    public function passenger()
    {
        return $this->belongsTo(Passenger::class, 'passenger_id', 'passenger_id');
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'driver_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'vehicle_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'booking_id', 'booking_id');
    }
}
