<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CourseFactory extends Factory
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
            'label' => Subject::random(),
            'started_at' => (new Carbon($this->faker->dateTimeBetween('-3 months', 'now')))->setTime($this->faker->randomElement([9, 11, 14, 16]), 0),
            'ended_at' => function ($attributes) {
                return $attributes['started_at']->copy()->addHours(2);
            },
            'duration' => 120,
        ];
    }
}
