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

        $bar = $this->output->createProgressBar();

        DB::transaction(function () use ($bar, $ypareo) {
            Course::whereNotNull('ypareo_id')->delete();

            Classroom::eachById(function ($classroom) use ($bar, $ypareo) {
                $courses = $ypareo->getCourses($classroom);

                $bar->start($courses->count());

                foreach ($courses as $course) {
                    $dbCourse = Course::firstOrNew(['ypareo_id' => $course['codeSeance']])
                                      ->forceFill([
                                          'label' => $course['nomMatiere'],
                                          'started_at' => Carbon::createFromFormat('d/m/Y H:i:s', $course['date'].' '.$course['heureDebut']),
                                          'ended_at' => Carbon::createFromFormat('d/m/Y H:i:s', $course['date'].' '.$course['heureFin']),
                                          'duration' => $course['duree'],
                                      ]);

                    try {
                        $dbCourse->save();

                        $dbCourse->classrooms()->sync(
                            Classroom::whereIn('ypareo_id', $course['codesGroupe'])->pluck('id')
                        );
                        $dbCourse->users()->sync(
                            User::whereIn(
                                'ypareo_id',
                                array_merge($course['codesApprenant'], $course['codesPersonnel'])
                            )->pluck('id')
                        );
                    } catch (ModelNotFoundException $e) {
                        logger()->notice('  Could not find subject, classrooms, students or trainers', [
                            'course' => $dbCourse,
                            'subject' => ['ypareo_id' => $course['codeMatiere']],
                            'classrooms' => ['ypareo_id' => $course['codesGroupe']],
                            'students' => ['ypareo_id' => $course['codesApprenant']],
                            'trainers' => ['ypareo_id' => $course['codesPersonnel']],
                            'exception' => $e,
                        ]);
                    } catch (QueryException $e) {
                        logger()->notice('  Could not save course', [
                            'course' => $dbCourse,
                            'exception' => $e,
                        ]);
                    }

                    $bar->advance();
                }
            });
        });

        $bar->finish();

        return 0;
    }
}
