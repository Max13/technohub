<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LedStripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $room = $this->faker->unique()->numberBetween(1, 100);
        $power = $this->faker->numberBetween(60, 3000);

        return [
            'name' => "Room $room",
            'topic' => "/room/$room/leds",
            'length' => $power,
            'power_supply' => $this->faker->numberBetween(1, ($power * 0.02 * 3) * 2),
        ];
    }
}
