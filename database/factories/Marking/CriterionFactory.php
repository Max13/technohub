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
        return [
            'name' => $this->faker->word(),
            'min_points' => $this->faker->numberBetween(1,10) * ($this->faker->boolean ? -1 : 1),
            'max_points' => function ($attributes) {
                $points = abs($attributes['min_points']);

                if ($this->faker->boolean) {
                    $points += $this->faker->numberBetween($attributes['min_points'], $attributes['min_points'] + 20);
                }

                if ($attributes['min_points'] < 0) {
                    $points *= -1;
                }

                return $points;
            },
        ];
    }
}
