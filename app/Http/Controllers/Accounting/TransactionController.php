<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Transaction;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TransactionController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Transaction::class, 'transaction');
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $classrooms = Classroom::withTrashed()
                               ->select([
                                   'id',
                                   'shortname',
                               ])
                               ->get()
                               ->mapWithKeys(function (Classroom $c) {
                                   return [$c->id => $c->shortname];
                               });

        $students = User::withTrashed()
                        ->whereRelation('roles', 'name', 'Student')
                        ->orWhere('is_student', true)
                        ->leftJoin('classroom_user', 'users.id', 'classroom_user'.'.user_id')
                        ->orderBy('classroom_user.year')
                        ->orderBy('users.id')
                        ->get([
                            'users.id',
                            'users.firstname',
                            'users.lastname',
                            'classroom_user.classroom_id',
                            'classroom_user.year',
                            'users.deleted_at',
                        ])
                        ->groupBy('id')
                        ->mapWithKeys(function (Collection $studentCollection) use ($classrooms) {
                            $student = $studentCollection->first();

                            if ($studentCollection->count() === 1) {
                                $classrooms = $student->classroom_id ? [$student->year => $classrooms[$student->classroom_id]] : null;
                            } else {
                                $classrooms = $studentCollection->mapWithKeys(function (User $s) use ($classrooms) {
                                    return $s->classroom_id ? [$s->year => $classrooms[$s->classroom_id]] : null;
                                })->toArray();
                            }

                            return [
                                $student->id => [
                                    'id' => $student->id,
                                    'firstname' => $student->firstname,
                                    'lastname' => $student->lastname,
                                    'fullname' => $student->lastname . ' ' . $student->firstname,
                                    'is_active' => !$student->trashed(),
                                    'classrooms' => $classrooms,
                                ],
                            ];
                        });

        $transactions = Transaction::where('is_queued', false)
                                   ->latest()
                                   ->get()
                                   ->each(function (Transaction $transaction) use ($students) {
                                       if ($transaction->student_id !== null) {
                                           $transaction->setRelation('student', $students[$transaction->student_id]);
                                       }
                                   });

        return view('accounting.index', [
            'transactions' => $transactions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Accounting\Transaction $transaction
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Accounting\Transaction $transaction
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request           $request
     * @param  \App\Models\Accounting\Transaction $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Accounting\Transaction $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
