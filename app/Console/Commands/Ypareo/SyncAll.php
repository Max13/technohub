<?php

namespace App\Console\Commands\Ypareo;

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

        $this->call('ypareo:sync:courses');
        $this->newLine(2);

        $this->call('ypareo:sync:printerpin');
        $this->newLine(2);

        return 0;
    }
}
