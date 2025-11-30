<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'items' => [
                [
                    'id' => 1,
                    'name' => fake()->words(2, true),
                    'price' => fake()->randomFloat(2, 10, 100),
                    'quantity' => fake()->numberBetween(1, 5),
                ]
            ],
            'total' => fake()->randomFloat(2, 20, 500),
            'status' => fake()->randomElement(['pending', 'for delivery', 'delivered', 'canceled']),
        ];
    }
}
