<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Mikrotik\Hotspot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OneButtonController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('cache.headers:no_store');
    }

    /**
     * Show 1button login page
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showLogin(Request $request)
    {
        Log::debug("Hotspot from $request->mac : Showing login form.", $request->all());

        $request->session()->flash('auth.entryPoint', $_SERVER['REQUEST_URI']);

        Log::debug("Hotspot from $request->mac : Entry point added to the session.", [
            'request' => $request->all(),
            'session' => $request->session()->all(),
        ]);

        return view('auth.1button.login')->with([
            'request' => $request->all(),
        ]);
    }

    /**
     * Logs in user via 1button
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\Mikrotik\Hotspot $hotspot
     * @return \Illuminate\Http\Response
     */
    public function doLogin(Request $request, Hotspot $hotspot)
    {
        Log::debug("Hotspot from $request->mac : Logging in to 1button.", $request->all());

        $data = $request->validate([
            'captive'         => 'required|url',
            'dst'             => 'nullable|url',
            'hs'              => 'required|string',
            'ip'              => 'required|ip',
            'mac'             => 'required|mac_address',
        ]);

        if (!$hotspot->createUser($data['hs'], $data['mac'], $data['mac'], '1button', true)) {
            return back()->withErrors([
                'captive' => 'Hotspot user could not be created.',
            ]);
        }

        Log::debug("Hotspot from $request->mac : User authenticated on Hotspot.", [
            'request' => $request->all(),
            'session' => $request->session()->all(),
        ]);

        DB::table('hotspot_history')->insert([
            'server' => $data['hs'],
            'user_id' => User::firstOrCreate(
                ['ypareo_login' => 'ANON'],
                [
                    'is_staff' => false,
                    'is_student' => true,
                    'is_trainer' => false,
                    'ypareo_id' => 0,
                    'firstname' => 'Anonymous',
                    'lastname' => 'Anonymous',
                    'email' => 'guest@example.com',
                    'password' => 'guest@example.com',
                ]
            )->id,
            'mac' => $data['mac'],
            'created_at' => now(),
        ]);

        $redirectTo = $data['captive'] . '?' . http_build_query([
            'dst' => route('hotspot.showConnected'),
            'username' => $data['mac'],
            'password' => $data['mac'],
        ]);

        Log::debug("Hotspot from $request->mac : Redirecting user to $redirectTo.", [
            'request' => $request->all(),
            'session' => $request->session()->all(),
        ]);

        return redirect()->away($redirectTo);
    }
}
