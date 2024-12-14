<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AbsenceFactory extends Factory
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
            'label' => $this->faker->word(),
            'is_delay' => $this->faker->boolean(),
            'is_justified' => $this->faker->boolean(),
            'started_at' => (new Carbon($this->faker->dateTimeBetween('-3 months', 'now')))->setTime($this->faker->randomElement([9, 11, 14, 16]), 0),
            'ended_at' => function ($attributes) {
                $ended_at = $attributes['started_at']->copy();

                if ($attributes['is_delay']) {
                    $ended_at->addMinutes($this->faker->numberBetween(10, 60));
                } elseif ($ended_at->hour < 13) {
                    $ended_at->addHours($this->faker->randomElement([13, 18]) - $ended_at->hour);
                } else {
                    $ended_at->addHours(18 - $ended_at->hour);
                }

                return $ended_at;
            },
            'duration' => function ($attributes) {
                return $attributes['started_at']->diffInRealMinutes($attributes['ended_at']);
            }
        ];
    }
}
