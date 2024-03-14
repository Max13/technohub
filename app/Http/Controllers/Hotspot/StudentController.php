<?php

namespace App\Http\Controllers\Hotspot;

use App\Models\User;
use App\Services\Mikrotik\Hotspot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Hotspot authentication controller for Students and Trainers
 */
class StudentController extends Controller
{
    /**
     * @inheritDoc
     */
    public function callback(Request $request, Hotspot $hotspot)
    {
        $data = $this->validateCallback($request, [
            'auth.user.ypareo_login' => [
                'required',
                Rule::exists('users', 'ypareo_login')->where(function ($query) {
                    return $query->where('is_trainer', true)
                                 ->orWhere('is_student', true);
                }),
            ]
        ]);

        if ($hotspot->createUser($data['hs'], $data['mac'], $data['mac'], $data['auth']['user']['ypareo_login'])) {
            DB::table('hotspot_history')->insert([
                'server' => $data['hs'],
                'user_id' => User::firstWhere('ypareo_login', $data['auth']['user']['ypareo_login']),
                'mac' => $data['mac'],
                'created_at' => now(),
            ]);

            return redirect()->away($data['captive'] . '?' . http_build_query([
               'dst' => $data['dst'],
               'username' => $data['mac'],
               'password' => $data['mac'],
            ]));
        }

        return redirect($data['auth']['entryPoint'])->withErrors([
            __('Hotspot authentication failed. Please try again.'),
        ])->withInput();
    }
}
