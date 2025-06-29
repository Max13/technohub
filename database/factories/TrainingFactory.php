<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $prefix = $this->faker->randomElement(['BTS', 'BTS', 'Bach', 'Mastere', 'Mastere']);
        $nth_year = $this->faker->numberBetween(1, 5);
        $job = $this->faker->jobTitle;

        return [
            'name' => "$prefix $job $nth_year",
            'fullname' => "$prefix $job $nth_year",
            'nth_year' => $nth_year,
        ];
    }
}
