<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(2, true),
            'price' => $this->faker->numberBetween(1000, 10000),
            'brand_name' => $this->faker->company(),
            'description' => $this->faker->sentence(),
            'image_url' => 'https://example.com/dummy.jpg',
            'condition' => '良好',
            'status' => 'available',
        ];
    }
}
