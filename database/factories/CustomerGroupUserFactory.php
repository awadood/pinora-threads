<?php

namespace Database\Factories;

use App\Models\CustomerGroupUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * CustomerGroupUserFactory
 */
class CustomerGroupUserFactory extends Factory
{
    protected $model = CustomerGroupUser::class;

    public function definition(): array
    {
        return [
            'customer_group_id' => \App\Models\CustomerGroup::factory(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
