<?php

namespace App\Console\Commands\Ypareo;

use App\Models\Absence;
use App\Models\Classroom;
use App\Models\Role;
use App\Models\Subject;
use App\Models\Training;
use App\Models\User;
use App\Services\Ypareo;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ypareo:sync:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Down sync all Ypareo data (Users, Classrooms, Subjects, Trainings, Absences, …)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Ypareo $ypareo)
    {
        logger()->debug('Syncing all data from Ypareo', [
            'arguments' => $this->arguments(),
            'options' => $this->options(),
        ]);

        Artisan::call('ypareo:sync:users');
        $this->newLine(2);

        // Classrooms
        $this->info('- Classrooms:');
        $yClassrooms = $ypareo->getAllClassrooms();
        DB::transaction(function () use ($yClassrooms) {
            Classroom::whereNotNull('ypareo_id')->delete();

            $this->withProgressBar($yClassrooms, function ($c) {
                $dbClass = Classroom::withTrashed()
                                    ->firstOrNew(['ypareo_id' => $c['codeGroupe']])
                                    ->forceFill([
                                        'name' => $c['nomGroupe'],
                                        'shortname' => $c['abregeGroupe'],
                                        'fullname' => $c['etenduGroupe'],
                                        'deleted_at' => null,
                                    ]);

                try {
                    $dbClass->training()->associate(
                        Training::updateOrCreate(
                            [
                                'name' => implode('-', explode('-', $c['abregeGroupe'], -1)) ?: $c['abregeGroupe'],
                            ],[
                                'fullname' => str_replace([' INITIAL', ' ALTERNANCE'], '', $c['etenduGroupe']),
                                'nth_year' => $c['numeroAnnee'],
                            ]
                        )
                    );

                    $dbClass->save();
                } catch (QueryException $e) {
                    //
                }
            });
        });
        // /Classroom

        $this->newLine(2);

        // Subjects
        $this->info('- Subjects:');
        DB::transaction(function () use ($yClassrooms) {
            Subject::whereNotNull('ypareo_id')->delete();

            $this->withProgressBar($yClassrooms, function ($c) {
                foreach ($c['matieres'] as $m) {
                    $dbSubject = Subject::withTrashed()
                                        ->firstOrNew(['ypareo_id' => $m['codeMatiere']])
                                        ->forceFill([
                                            'name' => $m['nomMatiere'],
                                            'type' => $m['nomTypeMatiere'],
                                        ]);

                    try {
                        $dbSubject->save();
                    } catch (QueryException $e) {
                        //
                    }
                }

                try {
                    Classroom::firstWhere('ypareo_id', $c['codeGroupe'])
                             ->training
                             ->subjects()
                             ->sync(Subject::whereIn('ypareo_id', array_column($c['matieres'], 'codeMatiere'))->pluck('id'));
                } catch (Exception $e) {
                    //
                }
            });
        });
        // /Subjects

        $this->newLine(2);

        // Student's training
        $this->info('- Students:');
        DB::transaction(function () use ($ypareo) {
            $this->withProgressBar(Classroom::all(), function ($c) use ($ypareo) {
                User::whereIn(
                    'ypareo_id',
                    $ypareo->getClassroomsStudents($c->ypareo_id)->pluck('codeApprenant')
                )->update(['training_id' => $c->training_id]);
            });
        });
        // /Student's classroom

        $this->newLine(2);

        // Trainer's trainings
        $this->info('- Trainers:');
        DB::transaction(function () use ($ypareo) {
            $this->withProgressBar(User::where('is_trainer', true)->get(), function ($u) use ($ypareo) {
                $yClassrooms = $ypareo->getClassrooms($u['ypareo_id']);
                $u->trainings()->sync(
                    Classroom::whereIn('ypareo_id', $yClassrooms->pluck('codeGroupe'))
                             ->pluck('training_id')
                );
            });
        });
        // /Trainer's classroom

        $this->newLine(2);

        // Absences
        $this->info('- Absences:');
        DB::transaction(function () use ($ypareo) {
            $absences = $ypareo->getAllAbsences();
            Absence::whereNotNull('ypareo_id')->delete();

            $this->withProgressBar($absences, function ($abs) {
                $dbAbsence = Absence::firstOrNew(['ypareo_id' => $abs['codeAbsence']])
                                    ->forceFill([
                                        'label' => $abs['motifAbsence']['nomMotifAbsence'],
                                        'is_delay' => $abs['isRetard'],
                                        'is_justified' => $abs['isJustifie'],
                                        'started_at' => Carbon::createFromFormat('d/m/Y', $abs['dateDeb'])
                                                              ->addMinutes($abs['heureDeb']),
                                        'ended_at' => Carbon::createFromFormat('d/m/Y', $abs['dateFin'])
                                                            ->addMinutes($abs['heureFin']),
                                        'duration' => $abs['duree'],
                                    ]);

                try {
                    $training = Training::whereRelation('classrooms', 'ypareo_id', $abs['codeGroupe'])
                                        ->first();
                    $user = User::where('ypareo_id', $abs['codeApprenant'])
                                ->first();

                    if (is_null($training) || is_null($user)) {
                        return;
                    }

                    $dbAbsence->student()->associate($user);
                    $dbAbsence->training()->associate($training);

                    $dbAbsence->save();
                } catch (QueryException $e) {
                    //
                }
            });
        });
        // /Absences

        return 0;
    }
}
