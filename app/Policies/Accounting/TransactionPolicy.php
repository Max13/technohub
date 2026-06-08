<?php

namespace App\Policies\Accounting;

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
        return $user->roles->contains('name', 'Admin');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->roles->contains('name', 'Accounting');
    }

    //
    // /**
    //  * Determine whether the user can view the model.
    //  *
    //  * @param  \App\Models\User  $user
    //  * @param  \App\Models\Accounting\Transaction  $transaction
    //  * @return \Illuminate\Auth\Access\Response|bool
    //  */
    // public function view(User $user, Transaction $transaction)
    // {
    //     return $user->roles->contains('name', 'Accounting');
    // }
    //
    // /**
    //  * Determine whether the user can create models.
    //  *
    //  * @param  \App\Models\User  $user
    //  * @return \Illuminate\Auth\Access\Response|bool
    //  */
    // public function create(User $user)
    // {
    //     //
    // }
    //
    // /**
    //  * Determine whether the user can update the model.
    //  *
    //  * @param  \App\Models\User  $user
    //  * @param  \App\Models\Accounting\Transaction  $transaction
    //  * @return \Illuminate\Auth\Access\Response|bool
    //  */
    // public function update(User $user, Transaction $transaction)
    // {
    //     //
    // }
    //
    // /**
    //  * Determine whether the user can delete the model.
    //  *
    //  * @param  \App\Models\User  $user
    //  * @param  \App\Models\Accounting\Transaction  $transaction
    //  * @return \Illuminate\Auth\Access\Response|bool
    //  */
    // public function delete(User $user, Transaction $transaction)
    // {
    //     //
    // }
    //
    // /**
    //  * Determine whether the user can restore the model.
    //  *
    //  * @param  \App\Models\User  $user
    //  * @param  \App\Models\Accounting\Transaction  $transaction
    //  * @return \Illuminate\Auth\Access\Response|bool
    //  */
    // public function restore(User $user, Transaction $transaction)
    // {
    //     //
    // }
    //
    // /**
    //  * Determine whether the user can permanently delete the model.
    //  *
    //  * @param  \App\Models\User  $user
    //  * @param  \App\Models\Accounting\Transaction  $transaction
    //  * @return \Illuminate\Auth\Access\Response|bool
    //  */
    // public function forceDelete(User $user, Transaction $transaction)
    // {
    //     //
    // }
}
