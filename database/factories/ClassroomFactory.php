<?php

namespace Database\Factories;

use App\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassroomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $suffix = $this->faker->word;

        return [
            'training_id' => Training::factory(),
            'ypareo_id' => $this->faker->unique()->randomNumber,
            'name' => function (array $attributes) use ($suffix) {
                return Training::find($attributes['training_id'])->name . ' ' . $suffix;
            },
            'shortname' => function (array $attributes) {
                return $attributes['name'];
            },
            'fullname' => function (array $attributes) use ($suffix) {
                return Training::find($attributes['training_id'])->fullname . ' ' . $suffix;
            },
        ];
    }
}
