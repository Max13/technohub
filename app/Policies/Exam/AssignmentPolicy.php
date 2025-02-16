<?php

namespace App\Policies\Exam;

use App\Models\Exam\Answer;
use App\Models\Exam\Assignment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssignmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function index(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the exam.
     *
     * @param  \App\Models\User            $user
     * @param  \App\Models\Exam\Assignment $assignment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function show(User $user, Assignment $assignment)
    {
        return $assignment->user()->is($user);
    }

    /**
     * Determine whether the user can start the exam.
     *
     * @param  \App\Models\User            $user
     * @param  \App\Models\Exam\Assignment $assignment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function startOrResume(User $user, Assignment $assignment)
    {
        return $assignment->user()->is($user)
            && $assignment->is_valid
            && !$assignment->is_finished;
    }

    /**
     * Determine whether the user can pass the assignment.
     *
     * @param  \App\Models\User            $user
     * @param  \App\Models\Exam\Assignment $assignment
     * @param  \App\Models\Exam\Answer     $answer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function pass(User $user, Assignment $assignment, Answer $answer)
    {
        return $assignment->user()->is($user)
            && $this->startOrResume($user, $assignment)
            && $answer->status === Answer::STATUS_OK;
    }

    /**
     * Determine whether the user can answer the model.
     *
     * @param  \App\Models\User            $user
     * @param  \App\Models\Exam\Assignment $assignment
     * @param  \App\Models\Exam\Answer     $answer
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function answer(User $user, Assignment $assignment, Answer $answer)
    {
        return $assignment->user()->is($user)
            && $this->startOrResume($user, $assignment)
            && $answer->status === Answer::STATUS_ONGOING
            && !$answer->isExpired();
    }
}
