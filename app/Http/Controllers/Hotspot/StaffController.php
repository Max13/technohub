<?php

namespace App\Http\Controllers\Hotspot;

use App\Models\User;
use App\Services\Mikrotik\Hotspot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Hotspot authentication controller for Staff
 */
class StaffController extends Controller
{
    /**
     * @inheritDoc
     */
    public function callback(Request $request, Hotspot $hotspot)
    {
        $data = $this->validateCallback($request, [
            'auth.user.email' => [
                'required',
                'email',
                Rule::exists('users', 'email')->where(function ($query) {
                    // FIXME: Use roles
                    return $query->where('is_staff', true);
                }),
            ],
        ]);

        if ($hotspot->createUser($data['hs'], $data['mac'], $data['mac'], $request->auth['user']->user['given_name'])) {
            DB::table('hotspot_history')->insert([
                'server' => $data['hs'],
                'user_id' => User::firstWhere('email', $data['auth']['user']['email'])->id,
                'mac' => $data['mac'],
                'created_at' => now(),
            ]);

            return redirect()->away($data['captive'] . '?' . http_build_query([
                'dst' => route('hotspot.showConnected'),
                'username' => $data['mac'],
                'password' => $data['mac'],
            ]));
        }

        return redirect($data['auth.entryPoint'])->withErrors([
            __('Hotspot authentication failed. Please try again.'),
        ])->withInput();
    }
}
