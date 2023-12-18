<?php

namespace App\Http\Controllers\Hotspot;

use App\Http\Controllers\Controller;
use App\Services\Mikrotik\Hotspot;
use App\Services\Ypareo;
use Illuminate\Http\Request;

class YpareoController extends Controller
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showLogin(Request $request)
    {
        return view('hotspot.login', [
            'captive' => $request->captive,
            'dst' => $request->dst,
            'hs' => $request->hs,
            'mac' => $request->mac,
        ]);
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\Ypareo      $ypareo
     * @return \Illuminate\Http\Response
     */
    public function doLogin(Request $request, Hotspot $hotspot, Ypareo $ypareo)
    {
        $data = $request->validate([
            'captive' => 'required|url',
            'dst' => 'url',
            'mac' => 'required|mac_address',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($ypareo->auth($data['username'], $data['password'], $request->userAgent())) {
            if ($hotspot->createUser($data['mac'], $data['mac'], $data['username'])) {
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
