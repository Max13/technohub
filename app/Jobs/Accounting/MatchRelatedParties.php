<?php

namespace App\Jobs\Accounting;

use App\Models\Bank\Transaction;
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
                $query->orWhere(function ($query) use ($party) {
                    $names = explode(' ', $party);
                    $query->where('firstname', $names[0])
                          ->orWhere('firstname', 'like', $names[0] . ' %')
                          ->orWhere('firstname', 'like', '% ' . $names[0])
                          ->orWhere('lastname', $names[0])
                          ->orWhere('lastname', 'like', $names[0] . ' %')
                          ->orWhere('lastname', 'like', '% ' . $names[0]);
                });
            }
        });

        $usersFound = $query->pluck('id');

        if ($usersFound->count() === 1) {
            $this->transaction->user_id = $usersFound[0];
            $this->transaction->is_queued = false;
        } elseif ($usersFound->count() > 1) {
            $this->transaction->potential_students = $usersFound->values();
        } else {
            return;
        }

        $this->transaction->save();
    }

    /** @inheritDoc */
    public function uniqueId()
    {
        return $this->transaction->id;
    }
}
