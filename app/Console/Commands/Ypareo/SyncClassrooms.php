<?php

namespace App\Console\Commands\Ypareo;

use App\Models\Classroom;
use App\Models\Training;
use App\Services\Ypareo;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class SyncClassrooms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ypareo:sync:classrooms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Down sync Ypareo classrooms';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Ypareo $ypareo)
    {
        logger()->debug('Syncing classrooms data from Ypareo', [
            'arguments' => $this->arguments(),
            'options' => $this->options(),
        ]);

        $this->info('Syncing classrooms data from Ypareo:');

        $ypareoClassrooms = $ypareo->getAllClassrooms()->reject(function ($c) {
            return is_null($c['prixDeVente']);
        });

        DB::transaction(function () use ($ypareoClassrooms) {
            Training::whereNotNull('id')->delete();
            Classroom::whereNotNull('ypareo_id')->delete();

            $this->withProgressBar($ypareoClassrooms, function ($c) {
                $name = implode('-', explode('-', $c['abregeGroupe'], -1)) ?: $c['abregeGroupe'];
                $dbTraining = Training::withTrashed()
                                      ->firstOrNew(['name' => $name])
                                      ->forceFill([
                                          'ypareo_id' => $c['codeFormation'],
                                          'fullname' => str_replace([' INITIAL', ' ALTERNANCE'], '', $c['etenduGroupe']),
                                          'nth_year' => value(function ($name) {
                                              if (str_starts_with($name, 'M1-')) {
                                                  return 4;
                                              }
                                              if (str_starts_with($name, 'M2-')) {
                                                  return 5;
                                              }
                                              if (str_starts_with($name, 'BACH ')) {
                                                  return 3;
                                              }
                                              return substr($name, -1);
                                          }, $name),
                                          'deleted_at' => null,
                                      ]);

                $dbClass = Classroom::withTrashed()
                                    ->firstOrNew(['ypareo_id' => $c['codeGroupe']])
                                    ->forceFill([
                                        'name' => $c['nomGroupe'],
                                        'shortname' => $c['abregeGroupe'],
                                        'fullname' => $c['etenduGroupe'],
                                        'deleted_at' => null,
                                    ]);

                try {
                    $dbTraining->save();
                    $dbClass->training()->associate($dbTraining);
                    $dbClass->save();
                } catch (QueryException $e) {
                    logger()->notice('  Could not save classroom and/or training', [
                        'classroom' => $dbClass,
                        'training' => $dbTraining,
                        'exception' => $e,
                    ]);
                }
            });
        });

        return 0;
    }
}
