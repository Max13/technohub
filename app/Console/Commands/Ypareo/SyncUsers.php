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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ypareo:sync-users {--only-students} {--only-employees}';

    /**
     * The console command description.
     *
     * @var string
     *
     * @todo Split in multiple commands
     */
    protected $description = 'Sync users from Ypareo APIs, including absences, trainings, subjects and students in class';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Ypareo $ypareo)
    {
        // Users
        $this->info('- Users:');
        DB::transaction(function () use ($ypareo) {
            $roles = Role::all()->keyBy('name');
            $rolesToDetach = $roles->where('is_from_ypareo', true)->pluck('id');
            $yUsers = $ypareo->getUsers();
            $now = now();
            User::whereNotNull('ypareo_id')->delete();

            $this->withProgressBar($yUsers, function ($u) use ($roles, $rolesToDetach, $now) {
                $dbUser = User::withTrashed()
                              ->where('ypareo_id', $u['ypareo_id'])
                              ->first();

                if ($dbUser) {
                    $dbUser->roles()->detach($rolesToDetach);

                    $dbUser->fill($u);
                    $dbUser->training_id = null;
                    $dbUser->deleted_at = null;
                } else {
                    $dbUser = new User($u);
                    $dbUser->password = bcrypt(Str::random(10));
                }

                $dbUser->email_verified_at = $now;

                $rolesToApply = [];
                if ($u['is_staff']) {
                    $rolesToApply[] = $roles['Staff']->id;
                }

                if ($u['is_student']) {
                    $rolesToApply[] = $roles['Student']->id;
                }

                if ($u['is_trainer']) {
                    $rolesToApply[] = $roles['Trainer']->id;
                }

                try {
                    $dbUser->save();
                    $dbUser->roles()->attach($rolesToApply);
                } catch (QueryException $e) {
                    //
                }
            });
        });
        // /Users

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

//                if ($c->ypareo_id == 56553) {
//                    dd($c); // "codeApprenant" => 8296
//                }
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
