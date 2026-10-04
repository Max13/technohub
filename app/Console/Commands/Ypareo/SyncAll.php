<?php

namespace App\Console\Commands\Ypareo;

use App\Console\Commands\Ldap\SendUsers;
use Illuminate\Console\Command;

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
    public function handle()
    {
        logger()->debug('Syncing all data from Ypareo', [
            'arguments' => $this->arguments(),
            'options' => $this->options(),
        ]);

        $this->call(SyncUsers::class);
        $this->newLine(2);

        $this->call(SyncClassrooms::class);
        $this->newLine(2);

        $this->call(SyncSubjects::class);
        $this->newLine(2);

        $this->call(SyncParticipants::class);
        $this->newLine(2);

        $this->call(SyncAbsences::class);
        $this->newLine(2);

        $this->call(SyncCourses::class);
        $this->newLine(2);

        $this->call(SendUsers::class);
        $this->newLine(2);

        $this->call(SendPrinterPin::class);
        $this->newLine(2);

        return 0;
    }
}
