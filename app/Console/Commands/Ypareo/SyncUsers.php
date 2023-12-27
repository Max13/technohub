<?php

namespace App\Console\Commands\Ypareo;

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
    protected $signature = 'ypareo:sync-users {--only-students} {--only-employees}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users from Ypareo APIs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Ypareo $ypareo)
    {
        $users = $ypareo->getUsers();

        DB::transaction(function () use ($users) {
            $now = now();
            User::whereNotNull('ypareo_id')->delete();

            $this->withProgressBar($users, function ($u) use ($now) {
                $dbUser = User::withTrashed()
                              ->where('ypareo_id', $u['ypareo_id'])
                              ->first();

                if ($dbUser) {
                    $dbUser->fill($u);
                    $dbUser->deleted_at = null;
                } else {
                    $dbUser = new User($u);
                    $dbUser->password = bcrypt(Str::random(10));
                }

                $dbUser->email_verified_at = $now;

                try {
                    $dbUser->save();
                } catch (QueryException $e) {
                    //
                }
            });
        });

        return 0;
    }
}
