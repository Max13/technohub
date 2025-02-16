<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Exam\Answer;
use App\Models\Exam\Assignment;
use App\Models\Exam\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExamTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_exams_can_be_viewed_as_admin()
    {
        /** @var \App\Models\User $admin */
        $admin = User::factory()->admin()->create();

        /** @var \App\Models\Exam $exam */
        $exams = Exam::factory()
                     ->count(5)
                     ->for(User::factory()->trainer(), 'author')
                     ->withQuestions()
                     ->withoutSeb()
                     ->create();

        $responseView = $this->actingAs($admin)->get(route('exams.index'));

        $responseView->assertSee($exams[0]->name);
        $responseView->assertSee($exams[0]->author->fullname);
        $responseView->assertSee($exams[1]->name);
        $responseView->assertSee($exams[1]->author->fullname);
        $responseView->assertSee($exams[2]->name);
        $responseView->assertSee($exams[2]->author->fullname);
        $responseView->assertSee($exams[3]->name);
        $responseView->assertSee($exams[3]->author->fullname);
        $responseView->assertSee($exams[4]->name);
        $responseView->assertSee($exams[4]->author->fullname);
    }

    public function test_only_own_exams_can_be_viewed_as_user()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->trainer()->create();

        /** @var \App\Models\Exam $visibleExams */
        $visibleExams = Exam::factory()
                            ->count(5)
                            ->for($user, 'author')
                            ->withQuestions()
                            ->withoutSeb()
                            ->create();

        /** @var \App\Models\Exam $notVisibleExams */
        $notVisibleExams = Exam::factory()->count(5)->create();

        $responseView = $this->actingAs($user)->get(route('exams.index'));

        $responseView->assertSee($visibleExams[0]->name);
        $responseView->assertSee($visibleExams[1]->name);
        $responseView->assertSee($visibleExams[2]->name);
        $responseView->assertSee($visibleExams[3]->name);
        $responseView->assertSee($visibleExams[4]->name);

        $responseView->assertDontSee($notVisibleExams[0]->name);
        $responseView->assertDontSee($notVisibleExams[1]->name);
        $responseView->assertDontSee($notVisibleExams[2]->name);
        $responseView->assertDontSee($notVisibleExams[3]->name);
        $responseView->assertDontSee($notVisibleExams[4]->name);
    }

    public function test_assignee_can_show_an_exam()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->student()->create();

        /** @var \App\Models\Exam $exam */
        $exam = Exam::factory()->withQuestions()->withoutSeb()->create();

        /** @var \App\Models\Exam\Assignment $assignment */
        $assignment = Assignment::factory()
                                ->for($user)
                                ->for($exam)
                                ->create();

        $responseView = $this->actingAs($user)->get(route('exams.assignments.show', $assignment));

        $responseView->assertSee($exam->name);
        $responseView->assertSee(route('exams.assignments.start', $assignment));
    }

    public function test_not_assignee_cannot_show_an_exam()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->student()->create();

        /** @var \App\Models\Exam\Assignment $assignment */
        $assignment = Assignment::factory()
                                ->for(Exam::factory()->withQuestions()->withoutSeb())
                                ->for($user)
                                ->create();

        $responseView = $this->actingAs(User::factory()->student()->create())
                             ->get(route('exams.assignments.show', $assignment));

        $responseView->assertForbidden();
    }

    public function test_assignee_can_start_an_exam()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->student()->create();

        /** @var \App\Models\Exam\Assignment $assignment */
        $assignment = Assignment::factory()
                               ->cleared()
                               ->for(Exam::factory()->withQuestions()->withoutSeb())
                               ->for($user)
                               ->create();

        $agent = $this->actingAs($user);

        $agent->get(route('exams.assignments.start', $assignment))
              ->assertRedirect(route('exams.assignments.pass', [$assignment, $assignment->order[0]]));

        $agent->get(route('exams.assignments.pass', [$assignment, $assignment->order[0]]))
              ->assertSuccessful()
              ->assertSee(Question::find($assignment->order[0])->question);
    }

    public function test_not_assignee_cannot_start_another_one_exam()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->student()->create();

        /** @var \App\Models\Exam\Assignment $assignment */
        $assignment = Assignment::factory()
                                ->cleared()
                                ->for(Exam::factory()->withQuestions()->withoutSeb())
                                ->create();

        $agent = $this->actingAs($user);

        $agent->get(route('exams.assignments.start', $assignment))
              ->assertForbidden();
    }

    public function test_assignee_can_resume_an_exam()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->student()->create();

        /** @var \App\Models\Exam\Assignment $assignment */
        $assignment = Assignment::factory()
                                ->cleared()
                                ->for(Exam::factory()->withQuestions()->withoutSeb())
                                ->for($user)
                                ->create([
                                    'started_at' => now()->subMinutes(10),
                                ]);

        Answer::factory()
              ->answered()
              ->for($assignment)
              ->count(3)
              ->sequence(function (Sequence $sequence) use ($assignment) {
                  return [
                      'question_id' => $assignment->order[$sequence->index],
                  ];
              })
              ->create();

        $agent = $this->actingAs($user);

        $agent->get(route('exams.assignments.start', $assignment))
              ->assertRedirect(route('exams.assignments.pass', [$assignment, $assignment->order[3]]));
    }

    public function test_assignment_shows_expired_when_refreshing_a_question()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->student()->create();

        /** @var \App\Models\Exam\Assignment $assignment */
        $assignment = Assignment::factory()
                                ->cleared()
                                ->for(Exam::factory()->withQuestions()->withoutSeb())
                                ->for($user)
                                ->create([
                                    'started_at' => now()->subMinutes(10),
                                ]);

        Answer::factory()
              ->answered()
              ->for($assignment)
              ->count(3)
              ->sequence(function (Sequence $sequence) use ($assignment) {
                  return [
                      'question_id' => $assignment->order[$sequence->index],
                  ];
              })
              ->create();

        $agent = $this->actingAs($user);

        $agent->get(route('exams.assignments.pass', [$assignment, $assignment->order[0]]))
              ->assertViewIs('exams.assignments.question_expired')
              ->assertSee(route('exams.assignments.pass', [$assignment, $assignment->nextQuestion()]));
    }

    public function test_assignment_shows_invalid_when_passing_another_question()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->student()->create();

        /** @var \App\Models\Exam\Assignment $assignment */
        $assignment = Assignment::factory()
                                ->ongoing()
                                ->for(Exam::factory()->withQuestions()->withoutSeb())
                                ->for($user)
                                ->create();

        /** @var \App\Models\Exam $agent */
        $otherExam = Exam::factory()->withQuestions()->create();

        $agent = $this->actingAs($user);

        $agent->get(route('exams.assignments.pass', [$assignment, $otherExam->questions()->first()]))
              ->assertViewIs('exams.invalid');
    }

    public function test_assignment_shows_finished_when_finished()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->student()->create();

        /** @var \App\Models\Exam\Assignment $assignment */
        $assignment = Assignment::factory()
                                ->completed()
                                ->for(Exam::factory()->withQuestions()->withoutSeb())
                                ->for($user)
                                ->create();

        $agent = $this->actingAs($user);

        $agent->get(route('exams.assignments.pass', [$assignment, $assignment->order[0]]))
              ->assertRedirect(route('exams.assignments.finish', $assignment));
    }

    public function test_question_can_be_answered_in_time()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->student()->create();

        /** @var \App\Models\Exam\Assignment $assignment */
        $assignment = Assignment::factory()
                                ->ongoing()
                                ->for(Exam::factory()->withQuestions()->withoutSeb())
                                ->for($user)
                                ->create();

        $question = $assignment->nextQuestion();
        $question->update(['duration' => 60]);
        $nextQuestion = $assignment->order[array_search($question->id, $assignment->order) + 1];

        Answer::factory()
              ->ongoing()
              ->for($assignment)
              ->for($question)
              ->create([
                  'created_at' => now()->subSeconds(30),
              ]);

        $this->actingAs($user)
             ->post(route('exams.assignments.answer', [$assignment, $question]), [
                 'answer' => $question->type === Question::TYPE_OPEN ? 'answer' : 1,
             ])
             ->assertRedirect(route('exams.assignments.pass', [$assignment, $nextQuestion]));
    }

    public function test_question_cannot_be_answered_after_delay()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->student()->create();

        /** @var \App\Models\Exam\Assignment $assignment */
        $assignment = Assignment::factory()
                                ->ongoing()
                                ->for(Exam::factory()->withQuestions()->withoutSeb())
                                ->for($user)
                                ->create();

        $question = $assignment->nextQuestion();
        $question->update(['duration' => 30]);

        Answer::factory()
              ->ongoing()
              ->for($assignment)
              ->for($question)
              ->create([
                  'created_at' => now()->subSeconds(60),
              ]);

        $this->actingAs($user)
             ->post(route('exams.assignments.answer', [$assignment, $question]), [
                 'answer' => $question->type === Question::TYPE_OPEN ? 'answer' : 1,
             ])
             ->assertViewIs('exams.assignments.question_expired');
    }
}
