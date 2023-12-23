<?php

namespace App\Console\Commands\Hotspot;

use App\Services\Mikrotik\Hotspot;
use Illuminate\Console\Command;

class Clear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotspot:clear {name : Hotspot\'s server name} {--queue : Should be queued}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear a MikroTik Hotspot';

    /**
     * Execute the console command.
     */
    public function handle(Hotspot $hotspot)
    {
        $job = function ($userId) use ($hotspot) {
            $hotspot->removeUser($userId);
        };

        $this->withProgressBar($hotspot->getUsers($this->argument('name')), function ($user) use ($job) {
            if ($this->option('queue')) {
                dispatch($job($user['.id']));
            } else {
                $job($user['.id']);
            }
        });
    }
}
