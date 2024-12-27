<?php

namespace App\Console\Commands\Ypareo;

use App\Models\Classroom;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\Services\Ypareo;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        DB::transaction(function () use ($ypareo) {
            $ypareoClassrooms = $ypareo->getAllClassrooms()->reject(function ($c) {
                return is_null($c['prixDeVente']);
            });

            Training::whereNotNull('id')->delete();
            Classroom::whereNotNull('ypareo_id')->delete();

            $this->withProgressBar($ypareoClassrooms, function ($c) {
                try {
                    $dbTraining = Training::withTrashed()
                                          ->firstOrNew(['name' => implode('-', explode('-', $c['abregeGroupe'], -1)) ?: $c['abregeGroupe']])
                                          ->forceFill([
                                              'fullname' => str_replace([' INITIAL', ' ALTERNANCE'], '', $c['etenduGroupe']),
                                              'nth_year' => $c['numeroAnnee'],
                                              'deleted_at' => null,
                                          ]);
                    $dbTraining->save();

                    $dbClass = Classroom::withTrashed()
                                        ->firstOrNew(['ypareo_id' => $c['codeGroupe']])
                                        ->forceFill([
                                            'name' => $c['nomGroupe'],
                                            'shortname' => $c['abregeGroupe'],
                                            'fullname' => $c['etenduGroupe'],
                                            'deleted_at' => null,
                                        ]);
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
