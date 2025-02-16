<?php

namespace Database\Factories;

use App\Models\Exam\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

class ExamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory()->trainer(),
            'is_public' => $this->faker->boolean,
            'name' => $this->faker->unique()->sentence,
            'seb_config_file' => function () {
                if ($this->faker->boolean) {
                    $dir = $this->faker->md5;
                    $file = $this->faker->md5.'.seb';

                    throw_if(!Storage::disk('exams')->put("$dir/$file", ''));

                    return "$dir/$file";
                }

                return null;
            },
            'seb_config_key' => function (array $attributes) {
                return $attributes['seb_config_file'] ? $this->faker->optional()->md5 : null;
            },
            'seb_exam_key' => function (array $attributes) {
                return $attributes['seb_config_file'] ? $this->faker->optional()->md5 : null;
            },
        ];
    }

    /**
     * Indicate that the model has questions
     *
     * @param  int|null $count
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withQuestions($count = 10)
    {
        return $this->has(Question::factory($count));
    }

    /**
     * Indicate that the model doesn't have SEB protection
     *
     * @param  int|null $count
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withoutSeb($count = 10)
    {
        return $this->state(function () {
            return [
                'seb_config_file' => null,
                'seb_config_key' => null,
                'seb_exam_key' => null,
            ];
        });
    }

    /**
     * Indicate that the model has SEB protection
     *
     * @param  int|null $count
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withSeb()
    {
        return $this->state(function () {
            $dir = $this->faker->md5;
            $file = $this->faker->md5.'.seb';

            throw_if(!Storage::disk('exams')->put("$dir/$file", ''));

            return [
                'seb_config_file' => "$dir/$file",
                'seb_config_key' => $this->faker->md5,
                'seb_exam_key' => $this->faker->md5,
            ];
        });
    }
}
