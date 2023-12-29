<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HotspotController extends Controller
{
    public function showLogin(Request $request)
    {
        if (!$request->has(['captive', 'hs','mac'])) {
            if (app()->environment('production')) {
                Log::notice('Captive portal without required parameters:', $request->all());
            }

            return view('hotspot.login-error');
        }

        if ($request->hs === 'hs-students') {
            return redirect()->route('hotspot.ypareo.showLogin', $request->only([
                'captive',
                'dst',
                'hs',
                'mac',
            ]));
        }

        if (app()->environment('production')) {
            Log::notice('Captive portal not for students:', $request->all());
        }

        return view('hotspot.login-error');
    }
}
