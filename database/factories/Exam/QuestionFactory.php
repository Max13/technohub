<?php

namespace Database\Factories\Exam;

use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $md5 = $this->faker->md5;
        $type = $this->faker->randomElement([
            'TrueFalse',
            'SingleChoice',
            'MultipleChoice',
            'Open',
        ]);

        return [
            'question' => $this->faker->sentence.' ?',
            'image' => function () use ($md5) {
                if ($this->faker->boolean) {
                    $filename = $md5 . '/' . $this->faker->md5 . '.jpg';

                    throw_if(!Storage::disk('exams')->put($filename, file_get_contents('https://placehold.co/300x200.jpg')));

                    return $filename;
                }

                return null;
            },
            'answer1' => function () use ($type) {
                switch ($type) {
                    case 'TrueFalse':
                        return __('True');
                    case 'SingleChoice':
                    case 'MultipleChoice':
                        return $this->faker->word;
                    case 'Open':
                        return __('Open answer');
                }

                return null;
            },
            'answer2' => function () use ($type) {
                switch ($type) {
                    case 'TrueFalse':
                        return __('False');
                    case 'SingleChoice':
                    case 'MultipleChoice':
                        return $this->faker->word;
                    case 'Open':
                        return null;
                }

                return null;
            },
            'answer3' => function () use ($type) {
                switch ($type) {
                    case 'TrueFalse':
                        return null;
                    case 'SingleChoice':
                    case 'MultipleChoice':
                        return $this->faker->optional()->word;
                    case 'Open':
                        return null;
                }

                return null;
            },
            'answer4' => function ($attributes) use ($type) {
                switch ($type) {
                    case 'TrueFalse':
                        return null;
                    case 'SingleChoice':
                    case 'MultipleChoice':
                        return isset($attributes['answer3']) ? $this->faker->optional()->word() : null;
                    case 'Open':
                        return null;
                }

                return null;
            },
            'valids' => function ($attributes) use ($type) {
                $nValids = count(array_filter(
                    [
                        $attributes['answer1'],
                        $attributes['answer2'],
                        $attributes['answer3'],
                        $attributes['answer4'],
                    ],
                    function ($val) {
                        return isset($val);
                    }
                ));

                switch ($type) {
                    case 'TrueFalse':
                        return [random_int(1, 2)];
                    case 'SingleChoice':
                        return [$this->faker->numberBetween(1, $nValids)];
                    case 'MultipleChoice':
                        return $this->faker->randomElements(range(1, $nValids));
                    case 'Open':
                        return null;
                }

                return null;
            },
            'duration' => $type === 'Open' ? 60 : $this->faker->randomElement([5, 10, 20, 30, 60, 120]),
            'points' => $this->faker->optional()->randomElement([1, 2, 5, 10, 20, 50, 100]),
        ];
    }
}
