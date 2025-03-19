<?php

namespace App\Http\Controllers\Marking;

use App\Http\Controllers\Controller;
use App\Models\Marking\Criterion;
use App\Models\Marking\Point;
use App\Models\Training;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PointController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \App\Models\User $student
     * @return \Illuminate\Http\Response
     */
    public function index(User $student)
    {
        return $student->points();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Models\User $student
     * @return \Illuminate\Http\Response
     */
    public function create(User $student)
    {
        return view('students.points.create', [
            'criteria' => Criterion::orderBy('name')->get(),
            'student' => $student->loadSum('points as total_points', 'points')
                                 ->load([
                                     'currentTraining',
                                     'points' => function ($query) {
                                        $query->latest()
                                              ->take(7);
                                     },
                                 ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User          $student
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $student)
    {
        $data = $request->validate([
            'criterion_id' => [
                'required',
                Rule::exists((new Criterion)->getTable(), 'id'),
            ],
            'points' => 'required|integer|between:'.($criterion = Criterion::find($request->criterion_id))->min_points.','.$criterion->max_points,
            'notes' => 'nullable|string|max:255',
        ]);

        $point = new Point($data);
        $point->criterion()->associate($criterion);
        $point->staff()->associate($request->user());
        $point->student()->associate($student);
        $point->save();

        return redirect()->route('trainings.show', $student->currentTraining);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Models\Training $training
     * @return \Illuminate\Http\Response
     */
    public function createBatch(Training $training)
    {
        return view('trainings.points.create', [
            'criteria' => Criterion::orderBy('name')->get(),
            'training' => $training->load([
                'students' => function ($query) {
                    $query->distinct()
                          ->withSum('points as sum_points', 'points')
                          ->orderBy('lastname');
                }
            ]),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Training      $training
     * @return \Illuminate\Http\Response
     */
    public function storeBatch(Request $request, Training $training)
    {
        $data = $request->validate([
            'criterion_id' => [
                'required',
                Rule::exists((new Criterion)->getTable(), 'id'),
            ],
            'students' => 'required|array',
            'students.*.id' => [
                'required',
                Rule::exists((new User)->getTable(), 'id'),
            ],
            'students.*.points' => 'nullable|integer|between:'.($criterion = Criterion::find($request->criterion_id))->min_points.','.$criterion->max_points,
            'students.*.notes' => 'nullable|string|max:255',
        ]);

        $data['students'] = array_filter($data['students'], function ($value) {
            return !is_null($value['points']);
        });

        try {
            DB::transaction(function () use ($data, $request) {
                $criterion = Criterion::find($data['criterion_id']);
                $user = $request->user();

                foreach ($data['students'] as $sData) {
                    $point = new Point($sData);
                    $point->criterion()->associate($criterion);
                    $point->staff()->associate($user);
                    $point->student()->associate(User::find($sData['id']));
                    $point->save();
                }
            });
        } catch (\Exception $e) {
            return back()->withErrors([
                $e->getMessage(),
            ]);
        }

        return redirect()->route('trainings.show', $training);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Marking\Point $point
     * @return \Illuminate\Http\Response
     */
    public function edit(Point $point)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Marking\Point $point
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Point $point)
    {
        $data = $request->validate([
            'points' => 'required|between:'.$point->criterion->min_points.','.$point->criterion->max_points,
            'notes' => 'nullable|string|max:255',
        ]);

        $point->points = $data['points'];
        $point->save();

        return $point;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Marking\Point $point
     * @return \Illuminate\Http\Response
     */
    public function destroy(Point $point)
    {
        $point->delete();

        return redirect()->route('students.points.index', $point->student_id);
    }
}
