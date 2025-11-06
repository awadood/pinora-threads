<?php

namespace Database\Factories;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * CartFactory
 */
class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'user_id' => null,
            'cookie_key' => strtoupper(fake()->unique()->bothify('CK#######')),
            'currency_code' => fake()->randomElement(['USD', 'PKR']),
            'expires_at' => null,
            'checked_out_at' => null,
        ];
    }
}
