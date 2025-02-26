<?php

namespace App\Http\Controllers\Hotspot;

use App\Models\User;
use App\Services\Mikrotik\Hotspot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        Log::debug("Hotspot from $request->mac : Calling ".self::class.'::'.__FUNCTION__.'.', [
            'request' => $request->all(),
            'session' => $request->session()->all(),
        ]);

        $data = $this->validateCallback($request, [
            'auth.user.ypareo_login' => [
                'required',
                Rule::exists('users', 'ypareo_login')->where(function ($query) {
                    // FIXME: Use roles
                    return $query->where('is_trainer', true)
                                 ->orWhere('is_student', true);
                }),
            ]
        ]);

        if ($hotspot->createUser($data['hs'], $data['mac'], $data['mac'], $data['auth']['user']['ypareo_login'], true)) {
            Log::debug("Hotspot from $request->mac : User authenticated on Hotspot.", [
                'request' => $request->all(),
                'session' => $request->session()->all(),
            ]);

            DB::table('hotspot_history')->insert([
                'server' => $data['hs'],
                'user_id' => User::firstWhere('ypareo_login', $data['auth']['user']['ypareo_login'])->id,
                'mac' => $data['mac'],
                'created_at' => now(),
            ]);

            $redirectTo = $data['captive'] . '?' . http_build_query([
                'dst' => $data['dst'] ?? null,
                'username' => $data['mac'],
                'password' => $data['mac'],
            ]);

            Log::debug("Hotspot from $request->mac : Redirecting user to $redirectTo.", [
                'request' => $request->all(),
                'session' => $request->session()->all(),
            ]);

            return redirect()->away($redirectTo);
        }

        Log::debug("Hotspot from $request->mac : User could not be authenticated on Hotspot.", [
            'request' => $request->all(),
            'session' => $request->session()->all(),
        ]);

        return redirect($data['auth']['entryPoint'])->withErrors([
            __('Hotspot authentication failed. Please try again.'),
        ])->withInput();
    }
}
