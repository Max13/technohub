<?php

namespace Database\Factories\Marking;

use App\Models\Marking\Criterion;
use Illuminate\Database\Eloquent\Factories\Factory;

class PointFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'points' => function ($attributes) {
                $criterion = Criterion::find($attributes['criterion_id']);
                return $this->faker->numberBetween($criterion->min_points, $criterion->max_points);
            },
        ];
    }
}
