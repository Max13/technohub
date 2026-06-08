<?php

namespace App\Policies\Bank;

use App\Models\Bank\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     *
     * @param  \App\Models\User  $user
     * @param  string  $ability
     * @return void|bool
     */
    public function before(User $user)
    {
        return $user->roles->contains('name', 'Admin') ?: null;
    }

    /**
     * Determine whether the user can view the bank transactions queue.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function index(User $user)
    {
        return $user->roles->contains('name', 'Bank');
    }

    /**
     * Determine whether the user can process bank transactions queue.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Accounting\Transaction  $transaction
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function process(User $user, Transaction $transaction)
    {
        return $user->roles->contains('name', 'Bank');
    }
}
