<?php

namespace App\Console\Commands\Ypareo;

use App\Models\Classroom;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\Services\Ypareo;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncSfp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ypareo:sync:sfp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Down sync Ypareo SFP status';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Ypareo $ypareo)
    {
        logger()->debug('Syncing SFP data from Ypareo', [
            'arguments' => $this->arguments(),
            'options' => $this->options(),
        ]);

        $this->info('Syncing SFP data from Ypareo:');

        $altClassrooms = Classroom::where('shortname', 'like', '%-ALT')->get();
        $role = Role::where('name', 'SFP')->sole();

        DB::transaction(function () use ($altClassrooms, $role, $ypareo) {
            $this->withProgressBar($altClassrooms, function ($cls) use ($role, $ypareo) {
                $ypareo->getClassroomsStudents($cls)->each(function ($s) use ($role) {
                    foreach ($s['inscriptions'] as $inscription) {
                        if ($inscription['statut']['abregeStatut'] === 'SFP') {
                            try {
                                $dbStudent = User::where('ypareo_id', $s['codeApprenant'])->sole();
                                $dbStudent->roles()->syncWithoutDetaching($role);
                            } catch (ModelNotFoundException $e) {
                                //
                            } catch (QueryException $e) {
                                logger()->notice('  Could not attach SFP role to student', [
                                    'student' => $dbStudent,
                                    'exception' => $e,
                                ]);
                            }
                        }
                    }
                });
            });
        });

        return 0;
    }
}
