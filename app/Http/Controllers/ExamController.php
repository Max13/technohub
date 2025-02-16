<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Exam\Assignment;
use App\Models\Exam\Question;
use App\Models\Role;
use App\Models\User;
use App\Services\Ypareo;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;

class ExamController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Exam::class, 'exam');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->user()->whereRelation('roles', 'name', 'Admin')->exists()) {
            $exams = Exam::with(['author' => function ($query) {
                             $query->select('id', 'firstname', 'lastname');
                         }])
                         ->withCount([
                             'assignments',
                             'questions',
                         ])
                         ->get();
        } elseif ($request->user()->whereRelation('roles', 'name', 'Trainer')->exists()) {
            $exams = $request->user()->exams->loadCount([
                'assignments',
                'questions',
            ]);
        } else {
            return redirect()->route('exams.assignments.index');
        }

        return view('exams.index', [
            'exams' => $exams,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if ($request->has('original')) {
            $originalExam = Exam::find($request->original);
            $this->authorize('view', $originalExam);
            $originalExam->questions->makeVisible('valids');
        } else {
            $originalExam = new Exam;
        }

        return view('exams.create', [
            'available_timers' => config('exam.available_timers'),
            'originalExam' => $originalExam,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique((new Exam)->getTable())->where(function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                })
            ],
            'seb_config_file' => 'required_with:seb_config_key,seb_exam_key|file',
            'seb_config_key' => 'string|size:64|nullable',
            'seb_exam_key' => 'string|size:64|nullable',
            'questions' => 'required|array|min:2',
            'questions.*.question' => 'required|string',
            'questions.*.image' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'questions.*.answers' => [
                'array',
                'between:0,4',
                'nullable',
                function ($attribute, $answers, $fail) {
                    $foundNull = false;

                    foreach ($answers as $val) {
                        if ($val === null) {
                            $foundNull = true;
                        } elseif ($foundNull) {
                            $fail('Please fill in answers one after the other');
                        }
                    }
                },
            ],
            'questions.*.valids' => [
                'required_with:questions.*.answers',
                'array',
                'nullable',
                function ($attribute, $valids, $fail) use ($request) {
                    $answers = array_filter($request->questions[explode('.', $attribute)[1]]['answers']);

                    foreach ($valids as $validIndex) {
                        if (!isset($answers[$validIndex - 1])) {
                            $fail('You cannot have valid answers that are empty');
                        }
                    }
                },
            ],
            'questions.*.duration' => [
                'integer',
                Rule::in(config('exam.available_timers')),
            ],
            'questions.*.points' => 'integer|nullable|min:0',
        ]);

        $data['questions'] = array_values($data['questions']);

        $exam = new Exam($data);

        try {
            DB::beginTransaction();

            // Exam
            if (
                   $request->hasFile('seb_config_file')
                && (
                       !$data['seb_config_file']->isValid()
                    || ($exam->seb_config_file = $data['seb_config_file']->store($exam->uuid, 'exams')) === false
                )
            ) {
                @unlink($exam->seb_config_file);
                throw new UploadException('seb_config_file');
            }
            $request->user()->exams()->save($exam);

            foreach ($data['questions'] as $qIndex => $question) {
                $newQuestion = new Question;
                $newQuestion->question = $question['question'];
                if (
                    $request->hasFile("question.$qIndex.image")
                    && (
                           !$question['image']->isValid()
                        || ($newQuestion->image = $question['image']->store($exam->uuid, 'exams')) === false
                    )
                ) {
                    @unlink($newQuestion->image);
                    throw new UploadException("question.$qIndex.image");
                }
                $newQuestion->answer1 = $question['answers'][0];
                $newQuestion->answer2 = $question['answers'][1];
                $newQuestion->answer3 = $question['answers'][2];
                $newQuestion->answer4 = $question['answers'][3];
                $newQuestion->valids = value(function () use ($question) {
                    return collect($question['valids'])->transform(function ($valid) {
                        return intval($valid);
                    });
                });
                $newQuestion->duration = $question['duration'];
                $newQuestion->points = $question['points'];
                $exam->questions()->save($newQuestion);
            }

            DB::commit();
        } catch (UploadException $e) {
            return back()->withInput()->withErrors([
                $e->getMessage() => __('Could not be uploaded'),
            ]);
        } catch (Exception $e) {
            return back()->withInput()->withErrors([
                $e->getMessage(),
            ]);
        } finally {
            DB::rollBack();
        }

        return redirect()->route('exams.show', $exam);
    }

    /**
     * Show exam assignment view
     *
     * @param  \App\Models\Exam          $exam
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\Ypareo      $ypareo
     * @return \Illuminate\Http\Response
     */
    public function showAssign(Exam $exam, Request $request, Ypareo $ypareo)
    {
        $this->authorize('assign', $exam);

        $user = $request->user()->load(['trainings' => function ($query) {
                                    $query->with(['classrooms.users' => function ($query) {
                                                $query->whereRelation('roles', 'name', 'Student')
                                                      ->select('users.id', 'firstname', 'lastname');
                                          }]);
                                }]);

        return view('exams.assign', [
            'endOfYear' => Carbon::createFromFormat('d/m/Y', $ypareo->getCurrentPeriod()['dateFin']),
            'exam' => $exam,
            'trainings' => $user->trainings,
        ]);
    }

    /**
     * Do exam assignment
     *
     * @param  \App\Models\Exam          $exam
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function doAssign(Exam $exam, Request $request)
    {
        $this->authorize('assign', $exam);

        $data = $request->validate([
            'valid_at' => 'date_format:Y-m-d|after:today|nullable',
            'valid_until' => 'date_format:Y-m-d|after:valid_at|nullable',
            'students' => [
                'required',
                'array',
                Rule::exists('users', 'id')->where(function ($query) {
                    $joinTable = strtolower(class_basename(Role::class)).'_'.strtolower(class_basename(User::class));
                    $rolesTable = strtolower((new Role)->getTable());
                    $usersTable = strtolower((new User)->getTable());

                    return $query->join($joinTable, "$usersTable.id", '=', "$joinTable.user_id")
                                 ->join($rolesTable, function ($join) use ($joinTable, $rolesTable) {
                                     $join->on("$joinTable.role_id", '=', "$rolesTable.id")
                                          ->where("$rolesTable.name", 'Student');
                                 })
                                 ->select('id');
                }),
            ],
        ]);

        try {
            DB::transaction(function () use ($exam, $data) {
                $questionIds = $exam->questions()->pluck('id');

                User::whereIn('id', $data['students'])->each(function (User $user) use ($exam, $data, $questionIds) {
                    $assignment = new Assignment([
                        'order' => $questionIds->shuffle(),
                        'valid_at' => $data['valid_at'],
                        'valid_until' => $data['valid_until'],
                    ]);

                    $assignment->exam()->associate($exam);
                    $assignment->user()->associate($user);
                    $assignment->save();
                });
            });
        } catch (QueryException $e) {
            return back()->withInput()->withErrors([
                $e->getMessage(),
            ]);
        }

        return redirect()->route('exams.show', $exam);
    }

    /**
     * Assign an exam to current user
     *
     * @param  \App\Models\Exam          $exam
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function selfAssign(Exam $exam, Request $request)
    {
        $this->authorize('assign', $exam);

        $assignment = new Assignment([
            'order' => $exam->questions->pluck('id')->shuffle(),
        ]);
        $assignment->exam()->associate($exam);
        $assignment->user()->associate($request->user());
        $assignment->save();

        return redirect()->route('exams.assignments.show', $assignment);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Exam  $exam
     * @return \Illuminate\Http\Response
     */
    public function show(Exam $exam)
    {
        // Did this to get objects and not Assignments
        $assignGroups = DB::table((new Assignment)->getTable())
                          ->select([
                              'id',
                              'group_uuid',
                              'valid_at',
                              'valid_until',
                              'created_at',
                              DB::raw('count(id) as assignments_count'),
                          ])
                          ->where('exam_id', $exam->id)
                          ->groupBy('group_uuid')
                          ->latest()
                          ->paginate(20);

        return view('exams.show', [
            'assignGroups' => $assignGroups,
            'exam' => $exam,
            'questions' => Question::where('exam_id', $exam->id)->oldest('id')->get(),
        ]);
    }

    /**
     * Download a report of the specified resource.
     *
     * @param  string $group_uuid
     * @return \Illuminate\Http\Response
     */
    public function downloadReport($group_uuid)
    {
        $exam = Exam::whereHas('assignments', function ($query) use ($group_uuid) {
                        $query->where('group_uuid', $group_uuid);
                    })
                    ->with([
                        'assignments' => function ($query) {
                            $query->with([
                                'user' => function ($query) {
                                    $query->select('id', 'firstname', 'lastname');
                                },
                            ]);
                        },
                        'questions',
                    ])
                    ->sole();

        return response()->streamDownload(function () use ($exam, $group_uuid) {
            echo 'student,started_at,ended_at,duration,points_over_20,raw_points,total_points'.PHP_EOL;

            $exam->assignments->each(function (Assignment $assignment) use ($exam) {
                echo $assignment->user->fullname.',';
                echo optional($assignment->started_at)->toDateTimeString().',';
                echo optional($assignment->ended_at)->toDateTimeString().',';
                echo $assignment->duration.',';
                echo $assignment->points.',';
                echo $assignment->raw_points.',';
                echo $exam->questions->sum('points');
                echo PHP_EOL;
            });
        }, 'exam_'.Str::slug($exam->name, '-', config('app.locale')).'_'.$group_uuid.'_report.csv');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Exam  $exam
     * @return \Illuminate\Http\Response
     */
    public function edit(Exam $exam)
    {
        if ($exam->assignments()->whereNotNull('started_at')->exists()) {
            return redirect()->route('exams.create', ['original' => $exam->id])
                             ->with('info', __('This exam (<u>:name</u>) has ongoing assignments. It cannot be modified, here is the form to create a new exam based on the one you wanted to modify.', ['name' => $exam->name]));
        }

        $exam->questions->makeVisible('valids');

        return view('exams.edit', [
            'available_timers' => config('exam.available_timers'),
            'exam' => $exam,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Exam  $exam
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Exam $exam)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique((new Exam)->getTable())->where(function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                })->ignore($exam->id),
            ],
            'seb_config_file' => 'required_with:seb_config_key,seb_exam_key|file',
            'seb_config_key' => 'string|size:64|nullable',
            'seb_exam_key' => 'string|size:64|nullable',
            'questions' => 'required|array|min:2',
            'questions.*.question' => 'required|string',
            'questions.*.image' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'questions.*.answers' => [
                'array',
                'between:0,4',
                'nullable',
                function ($attribute, $answers, $fail) {
                    $foundNull = false;

                    foreach ($answers as $val) {
                        if ($val === null) {
                            $foundNull = true;
                        } elseif ($foundNull) {
                            $fail('Please fill in answers one after the other');
                        }
                    }
                },
            ],
            'questions.*.valids' => [
                'required_with:questions.*.answers',
                'array',
                'nullable',
                function ($attribute, $valids, $fail) use ($request) {
                    $answers = array_filter($request->questions[explode('.', $attribute)[1]]['answers']);

                    foreach ($valids as $validIndex) {
                        if (!isset($answers[$validIndex - 1])) {
                            $fail('You cannot have valid answers that are empty');
                        }
                    }
                },
            ],
            'questions.*.duration' => [
                'integer',
                Rule::in(config('exam.available_timers')),
            ],
            'questions.*.points' => 'integer|nullable|min:0',
        ]);

        $data['questions'] = array_values($data['questions']);

        $exam->fill($data);

        try {
            DB::beginTransaction();

            // Exam
            if (
                $request->hasFile('seb_config_file')
                && (
                    !$data['seb_config_file']->isValid()
                    || ($exam->seb_config_file = $data['seb_config_file']->store($exam->uuid, 'exams')) === false
                )
            ) {
                @unlink($exam->seb_config_file);
                throw new UploadException('seb_config_file');
            }
            $request->user()->exams()->save($exam);

            foreach ($data['questions'] as $qIndex => $question) {
                $newQuestion = new Question;
                $newQuestion->question = $question['question'];
                if (
                    $request->hasFile("question.$qIndex.image")
                    && (
                        !$question['image']->isValid()
                        || ($newQuestion->image = $question['image']->store($exam->uuid, 'exams')) === false
                    )
                ) {
                    @unlink($newQuestion->image);
                    throw new UploadException("question.$qIndex.image");
                }
                $newQuestion->answer1 = $question['answers'][0];
                $newQuestion->answer2 = $question['answers'][1];
                $newQuestion->answer3 = $question['answers'][2];
                $newQuestion->answer4 = $question['answers'][3];
                $newQuestion->valids = value(function () use ($question) {
                    return collect($question['valids'])->transform(function ($valid) {
                        return intval($valid);
                    });
                });
                $newQuestion->duration = $question['duration'];
                $newQuestion->points = $question['points'];
                $exam->questions()->save($newQuestion);
            }

            DB::commit();
        } catch (UploadException $e) {
            return back()->withInput()->withErrors([
                $e->getMessage() => __('Could not be uploaded'),
            ]);
        } catch (Exception $e) {
            return back()->withInput()->withErrors([
                $e->getMessage(),
            ]);
        } finally {
            DB::rollBack();
        }

        return redirect()->route('exams.show', $exam);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Exam  $exam
     * @return \Illuminate\Http\Response
     */
    public function destroy(Exam $exam)
    {
        //
    }
}
