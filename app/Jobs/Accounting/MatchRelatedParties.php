<?php

namespace App\Jobs\Accounting;

use App\Models\Accounting\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;

class MatchRelatedParties implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Transaction */
    public Transaction $transaction;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    // public $uniqueFor = 3600;

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    // public function middleware()
    // {
    //     return [(new WithoutOverlapping($this->transaction->id))->dontRelease()];
    // }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (
               $this->transaction->user_id !== null
            || empty($this->transaction->related_parties)
        ) {
            return;
        }

        $query = User::withTrashed()
                     ->distinct()
                     ->where(function ($query) {
                         $query->whereRelation('roles', 'name', 'Student')
                               ->orWhere('is_student', true);
                     })
                     ->getQuery();

        $query->where(function ($query) {
            foreach ($this->transaction->related_parties as $party) {
                // If related_party contains a full name string
                if (isset($party[0])) {
                    $query->orWhere(function ($query) use ($party) {
                        $names = explode(' ', $party);
                        $query->where('firstname', $names[0])
                              ->orWhere('firstname', 'like', $names[0] . ' %')
                              ->orWhere('firstname', 'like', '% ' . $names[0])
                              ->orWhere('lastname', $names[0])
                              ->orWhere('lastname', 'like', $names[0] . ' %')
                              ->orWhere('lastname', 'like', '% ' . $names[0]);
                    });
                // If related_parties contains firstname and lastname keys
                } elseif (isset($party['firstname']) || isset($party['lastname'])) {
                    if (isset($party['firstname'])) {
                        $query->orWhere('firstname', $party['firstname'])
                              ->orWhere('firstname', str_replace('-', ' ', $party['firstname']));
                    }

                    if (isset($party['lastname'])) {
                        $query->orWhere('lastname', $party['lastname'])
                              ->orWhere('lastname', str_replace('-', ' ', $party['lastname']));
                    }
                }
            }
        });

        $usersFound = $query->get();

        // if ($this->transaction->normalized_related_parties[0] === 'DIAMANKA Diouma') {
        //     dd($this->transaction, $usersFound);
        // }

        if (
               $usersFound->count() === 1
            || ($striceUser = $usersFound->first(fn ($student) => ($student->lastname . ' ' . $student->firstname) === $this->transaction->normalized_related_parties[0]))
        ) {
            $this->transaction->student()->associate($usersFound->count() === 1 ? $usersFound[0]->id : $striceUser->id);
            $this->transaction->potential_students = null;
            $this->transaction->is_queued = false;
        } elseif ($usersFound->count() > 1) {
            $this->transaction->potential_students = $usersFound->pluck('id')->toArray();
        } else {
            return;
        }

        try {
            $this->transaction->save();
        } catch (\Throwable $e) {
            dd($e, $this->transaction);
        }
    }

    /** @inheritDoc */
    public function uniqueId()
    {
        return $this->transaction->id;
    }
}
