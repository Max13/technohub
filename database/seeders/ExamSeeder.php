<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\Exam\Answer;
use App\Models\Exam\Assignment;
use App\Models\Exam\Question;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            $cleared = collect([]);

            User::whereRelation('roles', 'name', 'Admin')
                ->each(function (User $user) use ($cleared) {
                    Exam::factory()
                        ->count(5)
                        ->for($user, 'author')
                        ->create()
                        ->each(function (Exam $exam) use ($cleared) {
                            $questions = Question::factory()
                                                 ->count(5)
                                                 ->for($exam)
                                                 ->create();

                            $cleared->push(
                                // Clean assignment
                                Assignment::factory()
                                          ->cleared()
                                          ->for($exam)
                                          ->for($exam->author)
                                          ->create()
                            );

                            $completed = random_int(0, 2);

                            $assignment = Assignment::factory()
                                                    ->for($exam)
                                                    ->for($exam->author)
                                                    ->create();

                            $questions->each(function (Question $question) use ($exam, $assignment, $completed) {
                                $answer = Answer::factory()
                                                ->for($assignment)
                                                ->for($question);

                                if ($completed) {
                                    $assignment->ended_at = now()->addMinutes(random_int(5, 10));
                                    $assignment->save();

                                    $answer = $answer->answered();
                                }

                                $answer->create();
                            });
                        });
                });

            $cleared->each(function (Assignment $assignment) {
                echo $assignment->uuid.PHP_EOL;
            });
        });
    }
}
