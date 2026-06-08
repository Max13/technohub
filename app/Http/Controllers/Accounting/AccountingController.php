<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\Transaction;
use App\Models\Accounting\TransactionStatus;
use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AccountingController extends Controller
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
     * Display the dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function dashboard(Request $request)
    {
        $this->authorize('viewAny', Transaction::class);

        $students = User::whereRelation('roles', 'name', 'Student')
                        ->when($request->query('status'), function (Builder $query) use ($request) {
                            if (in_array($request->query('status'), ['init', 'alt'])) {
                                $query->whereHas('currentClassroom', function ($query) use ($request) {
                                    $query->where('shortname', 'like', '%-'.strtoupper($request->query('status')));
                                });
                            } elseif ($request->query('status') === 'arrears') {
                                $query->whereHas('lastTransaction', function ($query) {
                                    $query->where('rejection_status', TransactionStatus::MISSED)
                                          ->latest()
                                          ->take(1);
                                });
                            }
                        })
                        ->with(['classrooms' => function ($query) {
                            $query->withTrashed()
                                  ->latest();
                        }])
                        ->whereHas('transactions', function (Builder $query) {
                            $query->where('amount', '!=', 0);
                        })
                        ->with('lastTransaction')
                        ->withSum('transactions', 'amount')
                        ->withSum(['transactions as past_transactions_sum_amount' => function (Builder $query) {
                            $query->where('created_at', '<', today());
                        }], 'amount')
                        ->withSum(['transactions as future_transactions_sum_amount' => function (Builder $query) {
                            $query->where('created_at', '>=', today());
                        }], 'amount')
                        ->get()
                        ->reject(function ($s) {
                            return $s->transactions->first()->label === 'Solde de scolarité'
                                || $s->transactions->first()->note === 'commissioné';
                        });

        // Store last query
        session(['accounting.dashboard.query' => $request->query()]);

        return view('accounting.dashboard', [
            'students' => $students,
            'studentsJson' => $students->map(function ($s) {
                                  return [
                                      'id' => $s->id,
                                      'fullname' => $s->fullname . ' - ' . ($s->classrooms->first()?->name ?? '×'),
                                  ];
                              }),
            'trainings' => Training::all()->keyBy('id'),
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \App\Models\User          $user
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(User $user, Request $request)
    {
        $this->authorize('viewAny', Transaction::class);

        $user->load([
            'classrooms',
            'transactions' => function ($query) {
                $query->latest();
            },
        ]);

        $today = today();

        return view('accounting.index', [
            'student' => $user,
            'lastTransaction' => $user->transactions->first(),
            'pastTransactions' => $user->transactions->filter(function ($t) use ($today) {
                return $t->created_at->lt($today);
            }),
            'futureTransactions' => $user->transactions->filter(function ($t) use ($today) {
                return $t->created_at->gte($today);
            }),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $user)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Accounting\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function show(User $user, Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Accounting\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user, Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @param  \App\Models\Accounting\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Accounting\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user, Transaction $transaction)
    {
        //
    }
}
