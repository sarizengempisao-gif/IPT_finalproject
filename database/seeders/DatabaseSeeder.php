<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Payment;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $passengers = Passenger::factory(10)->create();
        $drivers = Driver::factory(8)->create();
        $vehicles = Vehicle::factory(8)->create();
        $bookings = collect();
        for ($i = 0; $i < 16; $i++) {
            $booking = Booking::factory()->create([
                'passenger_id' => $passengers->random()->passenger_id,
                'driver_id' => $drivers->random()->driver_id,
                'vehicle_id' => $vehicles->random()->vehicle_id,
                'fare' => fake()->randomFloat(2, 80, 1200),
            ]);
            $bookings->push($booking);
        }
        foreach ($bookings->random(min(10, $bookings->count())) as $booking) {
            Payment::factory()->create(['booking_id' => $booking->booking_id, 'amount' => $booking->fare, 'status' => $booking->status === 'Completed' ? 'Paid' : fake()->randomElement(['Paid', 'Pending'])]);
        }
    }
}
