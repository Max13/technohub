<?php

namespace App\Policies;

use App\Models\Training;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TrainingPolicy
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
        return $user->roles->contains('name', 'HeadTeacher')
            || $user->roles->contains('name', 'Staff')
            || $user->roles->contains('name', 'Trainer');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Training  $training
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Training $training)
    {
        return $user->roles->contains('name', 'HeadTeacher')
            || $user->roles->contains('name', 'Staff')
            || (
                   $user->roles->contains('name', 'Trainer')
                && $user->trainings()->whereRelation('trainings', 'id', $training->id)->exists()
            );
    }
}
