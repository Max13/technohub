<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'is_from_ypareo' => $this->faker->boolean,
            'name' => $this->faker->unique()->word,
        ];
    }
}
