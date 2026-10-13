<?php

namespace Database\Factories;

use App\Models\Passenger;
use Illuminate\Database\Eloquent\Factories\Factory;

class PassengerFactory extends Factory
{
    protected $model = Passenger::class;

    public function definition(): array
    {
        return ['full_name' => fake()->name(), 'phone' => fake()->phoneNumber(), 'email' => fake()->unique()->safeEmail(), 'address' => fake()->address()];
    }
}
