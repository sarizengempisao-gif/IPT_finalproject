<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $primaryKey = 'vehicle_id';

    protected $fillable = ['plate_number', 'vehicle_type', 'model', 'year', 'color', 'capacity', 'status'];

    protected function casts(): array
    {
        return ['capacity' => 'integer', 'year' => 'integer'];
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'vehicle_id', 'vehicle_id');
    }
}
