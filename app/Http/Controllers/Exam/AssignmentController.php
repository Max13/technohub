<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Models\Exam\Answer;
use App\Models\Exam\Assignment;
use App\Models\Exam\Question;
use App\Policies\Exam\AssignmentPolicy;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class AssignmentController extends Controller
{
    /**
     * Display a list of the resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize(__FUNCTION__, Assignment::class);

        $baseQuery = $request->user()
                             ->assignments()
                             ->with([
                                 'exam' => function ($query) {
                                     $query->withSum('questions', 'points')
                                           ->with(['author' => function ($query) {
                                               $query->select('id', 'firstname', 'lastname');
                                           }]);
                                 },
                             ])
                             ->withCount(['answers']);

        $completed = $baseQuery->clone()
                               ->where('valid_until', '<=', now())
                               ->orWhereNotNull('ended_at')
                               ->get();

        $ongoing = $baseQuery->clone()
                             ->where(function ($query) {
                                 $query->whereNull('valid_until')
                                       ->orWhere('valid_until', '>', now());
                             })
                             ->whereNull('ended_at')
                             ->get();

        return view('exams.assignments.index', [
            'completed' => $completed,
            'ongoing' => $ongoing,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Exam\Assignment $assignment
     * @return \Illuminate\Http\Response
     */
    public function show(Assignment $assignment)
    {
        $this->authorize(__FUNCTION__, $assignment);

        $assignment->load(['exam.questions']);

        return view('exams.assignments.show', [
            'answer' => $assignment,
            'exam' => $assignment->exam,
            'questions' => $assignment->exam->questions,
        ]);
    }

    /**
     * Start a specific exam's question
     *
     * @param  \App\Models\Exam\Assignment $assignment
     * @return \Illuminate\Http\Response
     */
    public function start(Assignment $assignment)
    {
        $this->authorize('startOrResume', $assignment);

        if (!$assignment->is_started) {
            $assignment->fill(['started_at' => now()])->saveOrFail();
        }

        return redirect()->route('exams.assignments.pass', [
            'assignment' => $assignment,
            'question' => $assignment->nextQuestion(),
        ]);
    }

    /**
     * Pass a specific exam's question
     *
     * @param  \App\Models\Exam\Assignment $assignment
     * @param  \App\Models\Exam\Question   $question
     * @return \Illuminate\Http\Response
     */
    public function pass(Assignment $assignment, Question $question)
    {
        if (!in_array($question->id, $assignment->order)) {
            return response()->view('exams.invalid', [], 403);
        }

        /** @var \App\Models\Exam\Answer $answerGiven */
        $answerGiven = $assignment->answers()->where('question_id', $question->id)->firstOrCreate(
            ['question_id' => $question->id],
            [
                'status' => Answer::STATUS_OK,
                'value' => null,
            ],
        );

        if (!app(AssignmentPolicy::class)->pass(request()->user(), $assignment, $answerGiven)) {
            $nextQuestion = $assignment->nextQuestion();

            // Exam finished
            if ($assignment->is_finished || $nextQuestion === null) {
                return redirect()->route('exams.assignments.finish', $assignment);
            }

            // Question expired
            return response()->view(
                'exams.assignments.question_expired',
                [
                    'exam' => $assignment->exam,
                    'next' => route('exams.assignments.pass', [
                        'assignment' => $assignment,
                        'question' => $nextQuestion,
                    ]),
                ],
                403,
            );
        }

        $answerGiven->update(['status' => Answer::STATUS_ONGOING]);

        return response()->view(
            'exams.assignments.pass',
            [
                'assignment' => $assignment,
                'exam' => $assignment->exam,
                'question' => $question,
                'questionNum' => array_search($question->id, $assignment->order) + 1,
            ],
            200,
            [
                'Cache-Control' => 'no-store',
            ]
        );
    }

    /**
     * Answer a specific exam's question
     *
     * @param  \App\Models\Exam\Assignment $assignment
     * @param  \App\Models\Exam\Question   $question
     * @return \Illuminate\Http\Response
     */
    public function answer(Assignment $assignment, Question $question, Request $request)
    {
        /** @var \App\Models\Exam\Answer $answer */
        $answer = $assignment->answers()->where('question_id', $question->id)->firstOrFail();

        if (!app(AssignmentPolicy::class)->answer($request->user(), $assignment, $answer)) {
            if ($answer->status !== Answer::STATUS_ONGOING || $answer->isExpired()) {
                return response()->view(
                    'exams.assignments.question_expired',
                    [
                        'exam' => $assignment->exam,
                        'next' => route('exams.assignments.pass', [
                            'assignment' => $assignment,
                            'question' => $assignment->nextQuestion(),
                        ]),
                    ],
                    403
                );
            }

            return response()->view('exams.invalid', [], 403);
        }

        if ($question->type === Question::TYPE_OPEN || is_null($request->answer)) {
            $givenAnswer = $request->answer;
        } else {
            $givenAnswer = intval($request->answer);
        }

        $answer->update([
            'status' => Answer::STATUS_ANSWERED,
            'value' => $givenAnswer,
            'is_correct' => in_array($givenAnswer, $question->valids),
            'ended_at' => now(),
        ]);

        // Exam finished
        $nextQuestion = $assignment->nextQuestion();
        if ($nextQuestion === null) {
            return redirect()->route('exams.assignments.finish', $assignment);
        }

        return redirect()->route('exams.assignments.pass', [
            'assignment' => $assignment,
            'question' => $nextQuestion,
        ]);
    }

    /**
     * Show that the exam is finished
     *
     * @param  \App\Models\Exam\Assignment $assignment
     * @return \Illuminate\Http\Response
     */
    public function finish(Assignment $assignment)
    {
        $assignment->load([
            'exam',
            'answers',
        ]);

        if (!$assignment->is_finished) {
            $assignment->answers()
                       ->where('status', '!=', Answer::STATUS_ANSWERED)
                       ->update(['status' => Answer::STATUS_EXPIRED]);

            $assignment->update([
                'ended_at' => ($ended_at = now()),
                'duration' => $assignment->started_at->diffInSeconds($ended_at),
            ]);
        }

        return view('exams.assignments.finished', [
            'exam' => $assignment->exam,
            'duration' => CarbonInterval::createFromFormat('s', $assignment->duration)
                                        ->cascade()
                                        ->forHumans(['short' => true]),
        ]);
    }
}
