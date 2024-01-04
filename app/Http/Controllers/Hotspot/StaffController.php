<?php

namespace App\Http\Controllers\Hotspot;

use App\Http\Controllers\Controller;
use App\Services\Mikrotik\Hotspot;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class StaffController extends Controller
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showLogin(Request $request)
    {
        $data = $request->validate([
           'captive' => 'required|url',
           'dst' => 'nullable|url',
           'hs' => 'required|string',
           'ip' => 'required|ip',
           'mac' => 'required|mac_address',
        ]);

        return view('hotspot.staff.login')->with($request->query());
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function redirect(Request $request)
    {
        dd($request->all(), session()->all());
        return Socialite::driver('google')
                        ->with(in_array($request->hd, config('services.google.allowed_domains')) ? ['hd' => $request->hd] : [])
                        ->redirect();
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\Hotspot     $hotspot
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request, Hotspot $hotspot)
    {
        $user = Socialite::driver('google')->stateless()->user();

        $user->
        dd($user);

        $data = $request->validate([
            'captive' => 'required|url',
            'dst' => 'nullable|url',
            'hs' => 'required|string',
            'mac' => 'required|mac_address',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($ypareo->auth($data['username'], $data['password'], $request->userAgent())) {
            if ($hotspot->createUser($data['hs'], $data['mac'], $data['mac'], $data['username'])) {
                return redirect()->away($data['captive'] . '?' . http_build_query([
                   'dst' => $data['dst'],
                   'username' => $data['mac'],
                   'password' => $data['mac'],
                ]));
            }

            return back()->withErrors([
                'Erreur à l\'autorisation sur MT',
            ])->withInput();
        }

        return back()->withErrors([
            'Identifiants incorrects',
        ])->withInput();
    }
}
