<?php

namespace App\Console\Commands\Ypareo;

use App\Models\Classroom;
use App\Models\User;
use App\Services\Ypareo;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

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

        $year = substr($ypareo->getCurrentPeriod()['dateDeb'], -4);

        // Classrooms students
        $classrooms = Classroom::all();
        $ypareoStudents = [];
        $this->withProgressBar($classrooms, function ($cls) use (&$ypareoStudents, $ypareo) {
            $ypareoStudents[$cls->id] = [];
            $ypareo->getClassroomsStudents($cls)->each(function ($student) use ($cls, &$ypareoStudents) {
                $ypareoStudents[$cls->id][] = $student['codeApprenant'];
            });
            sort($ypareoStudents[$cls->id]);
        });

        // Students
        DB::transaction(function () use ($classrooms, $ypareoStudents, $year, $ypareo) {
            $this->withProgressBar($classrooms, function ($cls) use ($ypareoStudents, $year, $ypareo) {
                try {
                    $cls->users()->syncWithPivotValues(
                        User::whereIn('ypareo_id', $ypareoStudents[$cls->id])->pluck('id'),
                        ['year' => $year],
                        false
                    );
                } catch (QueryException $e) {
                    logger()->notice('  Could not save classroom students', [
                        'classroom' => $cls,
                        'ypareoStudentsIds' => $ypareoStudents[$cls->id],
                        'exception' => $e,
                    ]);
                }
            });
        });

        // Classrooms trainers
        $trainers = User::where('is_trainer', true)->get();
        $ypareoTrnClass = [];
        $this->withProgressBar($trainers, function ($trn) use (&$ypareoTrnClass, $ypareo) {
            $ypareoTrnClass[$trn->id] = $ypareo->getTrainersClassrooms($trn)->pluck('codeGroupe')->sort();
        });

        // Trainers
        DB::transaction(function () use ($trainers, $ypareoTrnClass, $year, $ypareo) {
            $this->withProgressBar($trainers, function ($trn) use ($ypareoTrnClass, $year) {
                try {
                    $trn->classrooms()->sync(
                        Classroom::whereIn('ypareo_id', $ypareoTrnClass[$trn->id])->pluck('id')
                    );
                } catch (QueryException $e) {
                    logger()->notice('  Could not save trainer classrooms', [
                        'trainer' => $trn,
                        'ypareoClassroomsIds' => $ypareoTrnClass[$trn->id],
                        'exception' => $e,
                    ]);
                }
            });
        });

        return 0;
    }
}
