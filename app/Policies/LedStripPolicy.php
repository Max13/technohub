<?php

namespace App\Policies;

use App\Models\LedStrip;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LedStripPolicy
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
        if ($user->roles->contains('name', 'Admin')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->roles->contains('name', 'Trainer');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User     $user
     * @param  \App\Models\LedStrip $ledStrip
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, LedStrip $ledStrip)
    {
        return $user->roles->contains('name', 'Trainer');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->roles->contains('name', 'Admin');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User     $user
     * @param  \App\Models\LedStrip $ledStrip
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, LedStrip $ledStrip)
    {
        return $user->roles->contains('name', 'Admin');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User     $user
     * @param  \App\Models\LedStrip $ledStrip
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, LedStrip $ledStrip)
    {
        return $user->roles->contains('name', 'Admin');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User     $user
     * @param  \App\Models\LedStrip $ledStrip
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, LedStrip $ledStrip)
    {
        return $user->roles->contains('name', 'Admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User     $user
     * @param  \App\Models\LedStrip $ledStrip
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, LedStrip $ledStrip)
    {
        return $user->roles->contains('name', 'Admin');
    }

    /**
     * Determine whether the user can control the model.
     *
     * @param  \App\Models\User     $user
     * @param  \App\Models\LedStrip $ledStrip
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function control(User $user, LedStrip $ledStrip)
    {
        return $user->roles->contains('name', 'HeadTeacher');
    }
}
