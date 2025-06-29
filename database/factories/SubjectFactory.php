<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'ypareo_id' => $this->faker->unique()->randomNumber(),
            'name' => $this->faker->word,
            'type' => $this->faker->randomElement(['Présentiel', 'Autonomie', 'Distanciel']),
        ];
    }
}
