<?php

namespace Database\Factories\Exam;

use App\Models\Exam\Answer;
use App\Models\Exam\Assignment;
use App\Models\Exam\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnswerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'assignment_id' => Assignment::factory(),
            'question_id' => Question::factory(),
            'status' => $this->faker->randomElement([
                Answer::STATUS_OK,
                Answer::STATUS_ONGOING,
                Answer::STATUS_ANSWERED,
                Answer::STATUS_EXPIRED,
            ]),
            'value' => null,
            'ended_at' => null,
        ];
    }

    /**
     * Indicate that the model is answered
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function answered()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Answer::STATUS_ANSWERED,
                'created_at' => now(),
                'ended_at' => now()->addSeconds(5),
            ];
        });
    }

    /**
     * Indicate that the model is unanswered
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unanswered()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Answer::STATUS_OK,
                'value' => null,
                'ended_at' => null,
            ];
        });
    }

    /**
     * Indicate that the model is ongoing
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function ongoing()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => Answer::STATUS_ONGOING,
                'value' => null,
                'ended_at' => now()->subSeconds(5),
            ];
        });
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (Answer $answer) {
            if ($answer->status === Answer::STATUS_ANSWERED) {
                $selectedAnswer = in_array($answer->question->answers->count(), [0, 1])
                    ? $this->faker->sentence
                    : random_int(1, $answer->question->answers->count());

                $answer->value = [$selectedAnswer];
                if (is_numeric($selectedAnswer)) {
                    $answer->is_correct = in_array($selectedAnswer, $answer->question->valids);
                } else {
                    $answer->is_correct = $this->faker->boolean;
                }
            }
        });
    }
}
