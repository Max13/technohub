<?php

namespace App\Policies\Exam;

use App\Models\Exam\Assignment;
use App\Models\Exam\Question;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuestionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can show the question
     *
     * @param  \App\Models\User            $user
     * @param  \App\Models\Exam\Assignment $answer
     * @param  \App\Models\Exam\Question   $question
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function show(User $user, Question $question, Assignment $answer)
    {
        return app(AssignmentPolicy::class)->resume($user, $answer)
            && !array_key_exists($question->id, $answer->answers ?? []);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, Assignment $answer, Question $question)
    {
        return $user->whereRelation('roles', 'name', '!=', 'Student')->exists();
    }
}
