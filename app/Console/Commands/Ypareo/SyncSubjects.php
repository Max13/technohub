<?php

namespace App\Console\Commands\Ypareo;

use App\Models\Subject;
use App\Models\Training;
use App\Services\Ypareo;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class SyncSubjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ypareo:sync:subjects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Down sync Ypareo subjects and associate them with classrooms';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Ypareo $ypareo)
    {
        logger()->debug('Syncing subjects data from Ypareo', [
            'arguments' => $this->arguments(),
            'options' => $this->options(),
        ]);

        $this->info('Syncing subjects data from Ypareo:');

        DB::transaction(function () use ($ypareo) {
            $ypareoSubjects = $ypareo->getAllClassrooms()
                                     ->reject(function ($c) {
                                         return is_null($c['prixDeVente']);
                                     })
                                     ->mapWithKeys(function ($c) {
                                         return [$c['codeGroupe'] => $c['matieres']];
                                     });

            Subject::whereNotNull('ypareo_id')->delete();

            $bar = $this->output->createProgressBar($ypareoSubjects->flatten()->count());
            $bar->start();

            foreach ($ypareoSubjects as $classYpareoId => $subjects) {
                $dbTraining = Training::whereHas('classrooms', function (Builder $query) use ($classYpareoId) {
                    return $query->where('ypareo_id', $classYpareoId);
                })->sole();

                foreach ($subjects as $s) {
                    $dbSubject = Subject::withTrashed()
                                        ->firstOrNew(['ypareo_id' => $s['codeMatiere']])
                                        ->forceFill([
                                            'name' => $s['nomMatiere'],
                                            'type' => $s['nomTypeMatiere'],
                                            'deleted_at' => null,
                                        ]);

                    try {
                        $dbSubject->save();
                    } catch (QueryException $e) {
                        logger()->notice('  Could not save subject', [
                            'subject' => $dbSubject,
                            'exception' => $e,
                        ]);
                    }

                    $bar->advance();
                }

                try {
                    $sIds = Subject::whereIn('ypareo_id', array_column($subjects, 'codeMatiere'))->pluck('id');
                    $dbTraining->subjects()->sync($sIds);
                } catch (QueryException $e) {
                    logger()->notice('  Could not sync subjects to training', [
                        'training' => $dbTraining,
                        'subjects' => $sIds,
                        'exception' => $e,
                    ]);
                }
            }

            $bar->finish();
        });

        return 0;
    }
}
