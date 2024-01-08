<?php

namespace App\Http\Controllers\Hotspot;

use App\Services\Mikrotik\Hotspot;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Hotspot authentication callback
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  \App\Services\Mikrotik\Hotspot $hotspot
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request, Hotspot $hotspot)
    {
        $data = $this->validateCallback($request, [
            'auth.user.email' => [
                'required',
                'email',
                Rule::exists('users', 'email')->where(function ($query) {
                    return $query->where('is_staff', true);
                }),
            ]
        ]);

        if ($hotspot->createUser($data['hs'], $data['mac'], $data['mac'], $data['username'])) {
            return redirect()->away($data['captive'] . '?' . http_build_query([
               'dst' => $data['dst'],
               'username' => $data['mac'],
               'password' => $data['mac'],
            ]));
        }

        return redirect($data['auth.entryPoint'])->withErrors([
            __('Identifiants incorrects'),
        ])->withInput();
    }
}
