<?php

namespace App\Console\Commands\Ypareo;

use App\Models\Role;
use App\Models\User;
use App\Services\Ypareo;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SyncUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ypareo:sync:users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Down sync Ypareo user\'s data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Ypareo $ypareo)
    {
        logger()->debug('Syncing users data from Ypareo', [
            'arguments' => $this->arguments(),
            'options' => $this->options(),
        ]);

        $this->info('Syncing users data from Ypareo:');

        $ypareoUsers = $ypareo->getUsers();

        DB::transaction(function () use ($ypareoUsers) {
            $roles = Role::all()->keyBy('name');
            $rolesToDetach = $roles->where('is_from_ypareo', true)->pluck('id');
            $now = now();

            User::whereNotNull('ypareo_id')->delete();

            $this->withProgressBar($ypareoUsers, function ($u) use ($roles, $rolesToDetach, $now) {
                $dbUser = User::withTrashed()
                              ->firstOrNew(['ypareo_id' => $u['ypareo_id']])
                              ->forceFill(array_merge($u, [
                                  'training_id' => null,
                                  'email_verified_at' => $now,
                                  'deleted_at' => null,
                              ]));

                if ($dbUser->exists) {
                    $dbUser->roles()->detach($rolesToDetach);
                } else {
                    $dbUser->password = bcrypt(Str::random(10));
                }

                $rolesToApply = [];
                if ($u['is_staff']) {
                    $rolesToApply[] = $roles['Staff']->id;
                }

                if ($u['is_student']) {
                    $rolesToApply[] = $roles['Student']->id;
                }

                if ($u['is_trainer']) {
                    $rolesToApply[] = $roles['Trainer']->id;
                }

                try {
                    $dbUser->save();
                    $dbUser->roles()->attach($rolesToApply);
                } catch (QueryException $e) {
                    logger()->notice('  Could not save user or attach roles', [
                        'user' => $dbUser,
                        'roles' => $rolesToApply,
                        'exception' => $e,
                    ]);
                }
            });
        });

        return 0;
    }
}
