<?php

namespace App\Console\Commands\Ypareo;

use App\Models\Absence;
use App\Models\Classroom;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\Services\Ypareo;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncAbsences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ypareo:sync:absences';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Down sync Ypareo absences';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Ypareo $ypareo)
    {
        logger()->debug('Syncing absences data from Ypareo', [
            'arguments' => $this->arguments(),
            'options' => $this->options(),
        ]);

        $this->info('Syncing absences data from Ypareo:');

        DB::transaction(function () use ($ypareo) {
            Absence::whereNotNull('ypareo_id')->delete();

            $this->withProgressBar($ypareo->getAllAbsences(), function ($abs) {
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
                    $training = Training::whereRelation('classrooms', 'ypareo_id', $abs['codeGroupe'])->sole();
                    $user = User::where('ypareo_id', $abs['codeApprenant'])->sole();

                    $dbAbsence->student()->associate($user);
                    $dbAbsence->training()->associate($training);

                    $dbAbsence->save();
                } catch (ModelNotFoundException $e) {
                    logger()->notice('  Could not find training or student', [
                        'absence' => $abs,
                        'exception' => $e,
                    ]);
                } catch (QueryException $e) {
                    logger()->notice('  Could not save absence', [
                        'absence' => $dbAbsence,
                        'exception' => $e,
                    ]);
                }
            });
        });

        return 0;
    }
}
