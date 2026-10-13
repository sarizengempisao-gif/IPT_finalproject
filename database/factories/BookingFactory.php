<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return ['passenger_id' => Passenger::factory(), 'driver_id' => Driver::factory(['status' => 'Available']), 'vehicle_id' => Vehicle::factory(['status' => 'Available']), 'booking_reference' => 'BK-'.fake()->unique()->numerify('######'), 'pickup_location' => fake()->streetAddress(), 'dropoff_location' => fake()->streetAddress(), 'booking_date' => fake()->dateTimeBetween('-30 days', '+15 days')->format('Y-m-d'), 'booking_time' => fake()->time('H:i'), 'fare' => fake()->randomFloat(2, 80, 1200), 'status' => fake()->randomElement(['Pending', 'Confirmed', 'Completed', 'Cancelled']), 'notes' => fake()->optional()->sentence()];
    }
}
