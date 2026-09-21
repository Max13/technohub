<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Transaction;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('view-queue', Transaction::class);

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
                        ->with([
                            'classrooms' => function ($query) {
                                $query->withTrashed()
                                      ->latest()
                                      ->select([
                                          'classrooms.id',
                                      ]);
                            },
                        ])
                        ->whereRelation('roles', 'name', 'Student')
                        ->orWhere('is_student', true)
                        ->orderBy('lastname')
                        ->get([
                            'id',
                            'firstname',
                            'lastname',
                            'deleted_at',
                        ])
                        ->mapWithKeys(function (User $student) use ($classrooms) {
                            return [
                                $student->id => [
                                    'id' => $student->id,
                                    'firstname' => $student->firstname,
                                    'lastname' => $student->lastname,
                                    'fullname' => $student->fullname,
                                    'is_active' => !$student->trashed(),
                                    'classrooms' => $student->classrooms->map(function (Classroom $c) use ($classrooms) {
                                        return $classrooms[$c->id];
                                    }),

                                ],
                            ];
                        });

        $transaction = Transaction::where('is_queued', true)
                                  ->oldest()
                                  ->get()
                                  ->each(function (Transaction $transaction) use ($students) {
                                      if ($transaction->student_id !== null) {
                                          $transaction->setRelation('student', $students[$transaction->user_id]);
                                      }
                                  });

        return view('accounting.queue.index', [
            'students' => $students,
            'transactions' => $transaction,
        ]);
    }

    /**
     * Process the actions on the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function process(Request $request)
    {
        dd($request->all());

        $this->authorize('process', Transaction::class);

        $data = $this->validate($request, [
            'transaction' => 'required|array',
            'transaction.*.id' => 'required|integer|exists:bank_transactions,id',
            'transaction.*.user_id' => 'sometimes|required_without:transaction.*.action|integer|exists:users,id',
            'transaction.*.action' => 'sometimes|required_without:transaction.*.user_id|in:approve,reject',
        ]);

        foreach ($data['transaction'] as $transactionData) {
            $trx = Transaction::find($transactionData['id']);

            if (isset($transactionData['user_id'])) {
                $trx->user()->associate(User::find($transactionData['user_id']));
            } elseif (isset($transactionData['action'])) {
                if ($transactionData['action'] === 'reject') {
                    $trx->delete();
                    continue;
                }
            }

            $trx->is_queued = false;
            $trx->save();
        }

        return redirect()->route('accounting.transactions.queue.index')
                         ->with('alert', [
                             'type' => 'success',
                             'message' => __('Transactions processed successfully'),
                         ]);
    }
}
