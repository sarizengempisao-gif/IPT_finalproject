<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleFactory extends Factory
{
    protected $model = Vehicle::class;

    public function definition(): array
    {
        return ['plate_number' => fake()->unique()->bothify('ABC-####'), 'vehicle_type' => fake()->randomElement(['Sedan', 'Hatchback', 'MPV', 'SUV']), 'model' => fake()->randomElement(['Toyota Vios', 'Mitsubishi Mirage', 'Honda City', 'Suzuki Dzire']), 'year' => fake()->year(), 'color' => fake()->randomElement(['White', 'Black', 'Silver', 'Red', 'Blue']), 'capacity' => fake()->numberBetween(4, 6), 'status' => fake()->randomElement(['Available', 'In Service', 'Unavailable'])];
    }
}
