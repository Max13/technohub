<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class BadgeController extends Controller
{
    /**
     * Get user's badge token
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User          $user
     * @param  string                    $platform  "apple" or "google"
     * @return \Illuminate\Http\Response
     */
    public function getToken(Request $request, User $user, $platform)
    {
        /** @var \App\Services\Wallet $wallet */
        $wallet = app(Wallet::class, [$platform]);

        if (optional($user->badge)->uuid === null) {
            $user->loadMissing([
                'currentClassroom',
                'points',
                'roles',
            ]);

            $points = $user->points->sum('points');

            if ($user->roles->contains('name', 'Student')) {
                $level = $user->currentClassroom->shortname;
            } elseif ($user->roles->contains('name', 'HeadTeacher') || $user->roles->contains('name', 'Admin')) {
                $level = 'Maître Jedi';
                $points = '∞';
            } elseif ($user->roles->contains('name', 'Trainer')) {
                $level = __('Trainer');
            } else {
                throw new RuntimeException('Unsupported role for badge');
            }

            $user->badge = [
                'platform' => $platform,
                'uuid' => ($uuid = Str::orderedUuid()->toString()),
                'token' => $wallet->token($uuid, $user->fullname, $level, $points),
            ];
            // $user->save();
        }

        return $user->badge;
    }
}
