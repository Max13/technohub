<?php

namespace App\Console\Commands;

use App\Models\Absence;
use App\Models\Course;
use App\Services\Ypareo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LinkAbsences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'link-absences {year? : Year (starting) to process} {--force : Refresh all the absences}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Link absences to courses';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Ypareo $ypareo)
    {
        $force = $this->option('force');
        $year = $this->argument('year') ?: substr($ypareo->getCurrentPeriod()['dateDeb'], -4);

        // Find absences for this year
        $query = Absence::whereHas('student.classrooms', function ($query) use ($year) {
            $query->where('classroom_user.year', $year);
        });

        if (!$force) {
            $query->doesntHave('courses');
        }
        // ---

        $count = $query->count();

        if ($count === 0) {
            $this->info("Nothing to synchronize.");
        } else {
            $this->info("Syncing $count absences:");

            DB::transaction(function () use ($query) {
                $this->withProgressBar($query->get(), function (Absence $absence) {
                    $absence->courses()->sync(
                        Course::whereBetween('started_at', [$absence->started_at, $absence->ended_at])
                              ->orWhereBetween('ended_at', [$absence->started_at, $absence->ended_at])
                              ->orWhereBetweenColumns($absence->started_at, ['started_at', 'ended_at'])
                              ->orWhereBetweenColumns($absence->ended_at, ['started_at', 'ended_at'])
                              ->pluck('id')
                    );
                });
            });
        }

        return 0;
    }
}
