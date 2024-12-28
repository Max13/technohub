<?php

namespace App\Console\Commands\Ypareo;

use App\Models\Classroom;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\Services\Ypareo;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncParticipants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ypareo:sync:participants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Down sync Ypareo participants (classrooms trainers and students)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Ypareo $ypareo)
    {
        logger()->debug('Syncing participants data from Ypareo', [
            'arguments' => $this->arguments(),
            'options' => $this->options(),
        ]);

        $this->info('Syncing participants data from Ypareo:');

        // TODO: Use roles
        $bar = $this->output->createProgressBar(
            User::where('is_trainer', true)
                ->orWhere('is_student', true)
                ->count()
        );
        $bar->start();

        // Students
        DB::transaction(function () use ($ypareo, $bar) {
            Classroom::all()->each(function ($c) use ($ypareo, $bar) {
                try {
                    $students = $ypareo->getClassroomsStudents($c);
                    // TODO: Add student to course instead of training ?
                    User::whereIn('ypareo_id', $students->pluck('codeApprenant'))
                        ->update(['training_id' => $c->training_id]);
                } catch (QueryException $e) {
                    logger()->notice('  Could not save classrooms students', [
                        'classroom' => $c,
                        'students' => $students->pluck('id'),
                        'exception' => $e,
                    ]);
                }

                $bar->advance($students->count());
            });
        });

        // Trainers
        DB::transaction(function () use ($ypareo, $bar) {
            User::where('is_trainer', true)->eachById(function ($t) use ($ypareo, $bar) {
                try {
                    $ypareoClassrooms = $ypareo->getTrainersClassrooms($t);
                    $t->trainings()->sync(
                        Classroom::whereIn('ypareo_id', $ypareoClassrooms->pluck('codeGroupe'))
                                 ->pluck('training_id')
                    );
                } catch (QueryException $e) {
                    logger()->notice('  Could not save trainers classrooms', [
                        'trainer' => $t,
                        'classrooms' => $ypareoClassrooms->pluck('codeGroupe'),
                        'exception' => $e,
                    ]);
                }

                $bar->advance();
            }, 100);
        });

        $bar->finish();

        return 0;
    }
}
