<?php

namespace Database\Factories\Exam;

use App\Models\Exam;
use App\Models\Exam\Answer;
use App\Models\Exam\Assignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Carbon;

class AssignmentFactory extends Factory
{
    /** @var bool */
    protected $isCompleted = false;

    /** @var bool */
    protected $isOngoing = false;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $today = today();

        return [
            'uuid' => $this->faker->uuid,
            'exam_id' => Exam::factory()->withQuestions(),
            'user_id' => User::factory()->student(),
            'order' => [],
            'valid_at' => $this->faker->boolean ? new Carbon($this->faker->dateTimeThisMonth) : null,
            'valid_until' => function (array $attributes) use ($today) {
                if ($this->faker->boolean) {
                    return ($attributes['valid_at'] ?? $today)->copy()->addWeek();
                }

                return null;
            },
            'started_at' => function () use ($today) {
                if ($this->faker->boolean) {
                    return $this->faker->dateTimeThisMonth;
                }

                return null;
            },
            'ended_at' => function (array $attributes) use ($today) {
                if (isset($attributes['started_at']) && $this->faker->boolean) {
                    return (new Carbon($attributes['started_at']))->copy()->addMinutes($this->faker->numberBetween(60, 480));
                }

                return null;
            },
            'duration' => function (array $attributes) use ($today) {
                if (isset($attributes['started_at'], $attributes['ended_at'])) {
                    return (new Carbon($attributes['started_at']))->diffInMinutes($attributes['ended_at']);
                }

                return null;
            },
        ];
    }

    /**
     * Indicate that the assignment is cleared
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function cleared()
    {
        return $this->state(function () {
            return [
                'valid_at' => null,
                'valid_until' => null,
                'started_at' => null,
                'ended_at' => null,
                'duration' => null,
            ];
        });
    }

    /**
     * Indicate that the assignment is completed
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function completed()
    {
        $this->isCompleted = true;

        return $this->state(function () {
            return [
                'valid_at' => null,
                'valid_until' => null,
                'started_at' => now()->subDay(),
                'ended_at' => now()->subDay()->addHour(),
                'duration' => 60,
            ];
        });
    }

    /**
     * Indicate that the assignment is ongoing
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function ongoing()
    {
        $this->isOngoing = true;

        return $this->state(function () {
            return [
                'valid_at' => null,
                'valid_until' => null,
                'started_at' => now()->subDay(),
                'ended_at' => null,
                'duration' => null,
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
        return $this->afterMaking(function (Assignment $assignment) {
                        $assignment->order = $assignment->exam->questions()->pluck('id')->shuffle()->values();
                    })
                    ->afterCreating(function (Assignment $assignment) {
                        if ($this->isOngoing || $this->isCompleted) {
                            $qCount = $this->isOngoing ? floor(count($assignment->order) / 2) : count($assignment->order);
                            Answer::factory()
                                  ->answered()
                                  ->for($assignment)
                                  ->count($qCount)
                                  ->sequence(function (Sequence $sequence) use ($assignment) {
                                      return [
                                          'question_id' => $assignment->order[$sequence->index],
                                      ];
                                  })
                                  ->create();
                        }
                    });
    }
}
