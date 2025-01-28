<?php

namespace App\Console\Commands\Ypareo;

use App\Models\Classroom;
use App\Models\Role;
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
        $ypareoStudents = collect();
        $this->withProgressBar($classrooms, function ($cls) use (&$ypareoStudents, $ypareo) {
            $students = [];
            $ypareo->getClassroomsStudents($cls)->each(function ($s) use ($cls, &$students) {
                $students[] = [
                    'is_disabled_worker' => boolval($s['estReconnuHandicape']),
                    'is_sfp' => collect($s['inscriptions'])->contains(function ($i) {
                        return $i['statut']['abregeStatut'] === 'SFP';
                    }),
                    'ypareo_id' => $s['codeApprenant'],
                ];
            });
            sort($students);
            $ypareoStudents->put($cls->id, $students);
        });

        // Students in classroom
        DB::transaction(function () use ($classrooms, $ypareoStudents, $year) {
            $this->withProgressBar($classrooms, function ($cls) use ($ypareoStudents, $year) {
                try {
                    $cls->users()->syncWithPivotValues(
                        User::whereIn('ypareo_id', array_column($ypareoStudents[$cls->id], 'ypareo_id'))->pluck('id'),
                        ['year' => $year],
                        false
                    );
                } catch (QueryException $e) {
                    logger()->notice('  Could not save classroom students', [
                        'classroom' => $cls,
                        'studentsYpareoIds' => $ypareoStudents[$cls->id],
                        'exception' => $e,
                    ]);
                }
            });
        });

        // Students as disabled workers
        DB::transaction(function () use ($ypareoStudents) {
            $role = Role::where('name', 'Disabled')->sole();
            $disabledStudents = $ypareoStudents->collapse()->where('is_disabled_worker', true);

            try {
                $role->users()->syncWithoutDetaching(
                    User::where('ypareo_id', $disabledStudents->pluck('ypareo_id'))->pluck('id')
                );
            } catch (QueryException $e) {
                logger()->notice('  Could not attach Disabled role to student', [
                    'studentsYpareoIds' => $disabledStudents->pluck('ypareo_id'),
                    'exception' => $e,
                ]);
            }
        });

        // Students as SFP
        DB::transaction(function () use ($ypareoStudents) {
            $role = Role::where('name', 'SFP')->sole();
            $sfpStudents = $ypareoStudents->collapse()->where('is_sfp', true);

            try {
                $role->users()->syncWithoutDetaching(
                    User::where('ypareo_id', $sfpStudents->pluck('ypareo_id'))->pluck('id')
                );
            } catch (QueryException $e) {
                logger()->notice('  Could not attach SFP role to students', [
                    'studentsYpareoIds' => $sfpStudents->pluck('ypareo_id'),
                    'exception' => $e,
                ]);
            }
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
                    $trn->classrooms()->syncWithPivotValues(
                        Classroom::whereIn('ypareo_id', $ypareoTrnClass[$trn->id])->pluck('id'),
                        ['year' => $year],
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
