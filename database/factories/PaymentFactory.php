<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => \App\Models\Order::factory(),
            'method' => $this->faker->randomElement(['cash', 'qris', 'gopay', 'ovo']),
            'transaction_id' => $this->faker->optional()->uuid(),
            'provider' => $this->faker->randomElement(['CASH', 'QRIS', 'GOPAY', 'OVO']),
            'amount' => $this->faker->numberBetween(10000, 100000),
            'fee' => $this->faker->numberBetween(0, 5000),
            'currency' => 'IDR',
            'status' => $this->faker->randomElement(['pending', 'success', 'failed']),
            'paid_at' => $this->faker->optional()->dateTime(),
        ];
    }

    public function paid()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'success',
                'paid_at' => now(),
            ];
        });
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'paid_at' => null,
            ];
        });
    }
}