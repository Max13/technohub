<?php

namespace App\Policies;

use App\Models\Absence;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AbsencePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->roles->whereIn('name', ['Admin', 'HeadTeacher', 'Staff'])->isNotEmpty();
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Absence  $absence
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Absence $absence)
    {
        return $user->roles->viewAny($user);
    }
}
