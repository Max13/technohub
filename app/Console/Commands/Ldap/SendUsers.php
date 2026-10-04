<?php

namespace App\Console\Commands\Ldap;

use App\Models\User;
use App\Services\ActiveDirectory;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SendUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldap:send:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Up sync local users\' to LDAP (for now, trainers only)';

    /**
     * Execute the console command.
     *
     * @param  \App\Services\ActiveDirectory $adService
     * @return int
     */
    public function handle(ActiveDirectory $adService)
    {
        logger()->debug('Syncing new trainers from Ypareo to LDAP', [
            'arguments' => $this->arguments(),
            'options' => $this->options(),
        ]);

        $this->info('Syncing new trainers from Ypareo to LDAP:');

        DB::transaction(function () use ($adService) {
            $batchUuid = Str::orderedUuid()->toString();
            $notProcessed = 0;
            $query = User::whereRelation('roles', 'name', 'Trainer')
                         ->whereDoesntHave('roles', function ($query) {
                             $query->where('name', 'Staff')
                                   ->orWhere('name', 'Student');
                         })
                         ->select([
                             'id',
                             'ypareo_id',
                             'ypareo_login',
                             'ypareo_uuid',
                             'lastname',
                             'firstname',
                             'email',
                         ]);

            $this->withProgressBar($query->count(), function () use ($batchUuid, &$notProcessed, $query) {
                $query->chunkById(100, function (Collection $trainers) use ($batchUuid, &$notProcessed) {
                    try {
                        Http::put(config('services.n8n.ldap.sendUsers'), [
                                'uuid' => $batchUuid,
                                'trainers' => $trainers->toArray(),
                            ])
                            ->throw();
                    } catch (\Throwable $th) {
                        ++$notProcessed;
                        logger()->notice('  Could not send users to nathan', [
                            'users' => $trainers->only([
                                                    'id',
                                                    'ypareo_id',
                                                    'fullname',
                                                ])->toArray(),
                            'exception' => $th,
                        ]);
                    }
                });

                if ($notProcessed > 0) {
                    $this->warn('  > ' . $notProcessed . ' users could not be processed');
                }
            });
        });

        return 0;
    }
}
