<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . $this->faker->unique()->numberBetween(1000, 9999),
            'user_id' => \App\Models\User::factory(),
            'total_price' => $this->faker->numberBetween(10000, 100000),
            'currency' => 'IDR',
            'status' => $this->faker->randomElement(['pending', 'processing', 'paid', 'completed', 'cancelled']),
        ];
    }
}