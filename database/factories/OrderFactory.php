<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

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
        $createdAt = $this->faker->dateTimeBetween('-1 year', 'now');
        $isPaid = $this->faker->boolean(70); // 70% шанс, что заказ оплачен
        
        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-' . strtoupper($this->faker->bothify('??###')),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'cancelled']),
            'total_amount' => $this->faker->randomFloat(2, 50, 5000),
            'shipping_address' => $this->faker->address,
            'billing_address' => $this->faker->address,
            'payment_method' => $this->faker->randomElement(['credit_card', 'paypal', 'bank_transfer']),
            'is_paid' => $isPaid,
            'paid_at' => $isPaid ? $this->faker->dateTimeBetween($createdAt, 'now') : null,
            'created_at' => $createdAt,
            'updated_at' => $this->faker->dateTimeBetween($createdAt, 'now'),
        ];
    }
}
