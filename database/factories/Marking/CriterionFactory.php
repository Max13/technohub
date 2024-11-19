<?php

namespace Database\Factories\Marking;

use Illuminate\Database\Eloquent\Factories\Factory;

class CriterionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $min = $this->faker->randomElement(range(10, 100, 10));
        $max = $this->faker->randomElement(range($min, 100, 5));
        $negative = $this->faker->boolean;

        return [
            'name' => $this->faker->unique()->sentence(),
            'min_points' => $negative ? $max * -1 : $min,
            'max_points' => $negative ? $min * -1 : $max,
        ];
    }
}
