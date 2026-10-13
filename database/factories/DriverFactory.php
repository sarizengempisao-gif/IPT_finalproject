<?php

namespace Database\Factories;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

class DriverFactory extends Factory
{
    protected $model = Driver::class;

    public function definition(): array
    {
        return ['full_name' => fake()->name(), 'email' => fake()->unique()->safeEmail(), 'license_no' => 'DL-'.fake()->unique()->numerify('######'), 'phone' => fake()->phoneNumber(), 'status' => fake()->randomElement(['Available', 'On Trip', 'Inactive'])];
    }
}
