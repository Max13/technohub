<?php

namespace App\Console\Commands\Ypareo;

use App\Models\Classroom;
use App\Models\Course;
use App\Models\User;
use App\Services\Ypareo;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SyncCourses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ypareo:sync:courses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Down sync Ypareo courses';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Ypareo $ypareo)
    {
        logger()->debug('Syncing courses data from Ypareo', [
            'arguments' => $this->arguments(),
            'options' => $this->options(),
        ]);

        $this->info('Syncing courses data from Ypareo:');

        $courses = collect();
        $this->withProgressBar(Classroom::all(), function ($cls) use ($courses, $ypareo) {
            $ypareo->getCourses($cls)->each(function ($ypareoCourse) use ($courses) {
                $courses->push($ypareoCourse);
            });
        });
        $courses = $courses->sortBy(function ($crs) {
            return Carbon::createFromFormat('d/m/Y H:i:s', $crs['date'].' '.$crs['heureDebut']);
        });

        DB::transaction(function () use ($courses) {
            Course::whereNotNull('ypareo_id')->delete();

            $this->withProgressBar($courses, function ($crs) {
                $dbCourse = Course::firstOrNew(['ypareo_id' => $crs['codeSeance']])
                                  ->forceFill([
                                      'label' => $crs['nomMatiere'],
                                      'started_at' => Carbon::createFromFormat('d/m/Y H:i:s', $crs['date'].' '.$crs['heureDebut']),
                                      'ended_at' => Carbon::createFromFormat('d/m/Y H:i:s', $crs['date'].' '.$crs['heureFin']),
                                      'duration' => $crs['duree'],
                                  ]);

                try {
                    $dbCourse->save();

                    $dbCourse->classrooms()->sync(
                        Classroom::whereIn('ypareo_id', $crs['codesGroupe'])->pluck('id')
                    );
                    $dbCourse->users()->sync(
                        User::whereIn(
                            'ypareo_id',
                            array_merge($crs['codesApprenant'], $crs['codesPersonnel'])
                        )->pluck('id')
                    );
                } catch (ModelNotFoundException $e) {
                    logger()->notice('  Could not find subject, classrooms, students or trainers', [
                        'course' => $dbCourse,
                        'subject' => ['ypareo_id' => $crs['codeMatiere']],
                        'classrooms' => ['ypareo_id' => $crs['codesGroupe']],
                        'students' => ['ypareo_id' => $crs['codesApprenant']],
                        'trainers' => ['ypareo_id' => $crs['codesPersonnel']],
                        'exception' => $e,
                    ]);
                } catch (QueryException $e) {
                    logger()->notice('  Could not save course', [
                        'course' => $dbCourse,
                        'exception' => $e,
                    ]);
                }
            });
        });

        return 0;
    }
}
