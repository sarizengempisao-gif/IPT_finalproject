<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return ['booking_id' => Booking::factory(), 'payment_reference' => 'PAY-'.fake()->unique()->numerify('######'), 'amount' => fake()->randomFloat(2, 80, 1200), 'payment_method' => fake()->randomElement(['Cash', 'GCash', 'Card', 'Bank Transfer']), 'payment_date' => fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d'), 'status' => fake()->randomElement(['Paid', 'Pending', 'Refunded']), 'notes' => fake()->optional()->sentence()];
    }
}
