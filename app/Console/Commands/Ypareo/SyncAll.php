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

        $this->call('ypareo:sync:users');
        $this->newLine(2);

        $this->call('ypareo:sync:classrooms');
        $this->newLine(2);

        $this->call('ypareo:sync:subjects');
        $this->newLine(2);

        $this->call('ypareo:sync:participants');
        $this->newLine(2);

        $this->call('ypareo:sync:absences');
        $this->newLine(2);

        return 0;
    }
}
