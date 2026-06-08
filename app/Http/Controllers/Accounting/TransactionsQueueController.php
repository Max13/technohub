<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Bank\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class TransactionsQueueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('index', Transaction::class);

        $students = User::withTrashed()
                        ->with(['currentClassroom' => function ($query) {
                            $query->withTrashed()
                                  ->select([
                                      'classrooms.id',
                                      'shortname',
                                  ]);
                        }])
                        ->whereRelation('roles', 'name', 'Student')
                        ->orWhere('is_student', true)
                        ->orderBy('lastname')
                        ->get([
                            'id',
                            'firstname',
                            'lastname',
                            'deleted_at',
                        ])
                        ->mapWithKeys(function (User $student) {
                            return [
                                $student->id => [
                                    'id' => $student->id,
                                    'fullname' => $student->fullname,
                                    'is_active' => $student->deleted_at === null,
                                    'classroom' => $student->currentClassroom?->shortname,
                                ]
                            ];
                        });
        $transaction = Transaction::where('is_queued', true)
                                  ->oldest()
                                  ->get();

        $transaction->each(function (Transaction $transaction) use ($students) {
            if ($transaction->user_id !== null) {
                $transaction->setRelation('user', $students[$transaction->user_id]);
            }
        });

        return view('accounting.transactions.queue.index', [
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
