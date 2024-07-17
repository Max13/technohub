<?php

namespace App\Http\Controllers;

use App\Exceptions\Hotspot\BadRequestException;
use App\Services\Mikrotik\Hotspot;
use Illuminate\Http\Request;

/**
 * Hotspot controller, handles where to redirect the user to authenticate.
 */
class HotspotController extends Controller
{
    /**
     * Redirects the user to the appropriate authentication page.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  \App\Services\Mikrotik\Hotspot $hotspot
     * @return \Illuminate\Http\Response
     * @throws \App\Exceptions\Hotspot\BadRequestException
     */
    public function redirectToLogin(Request $request, Hotspot $hotspot)
    {
        if ($hotspot->findUser($request->hs, $request->mac)) {
            return redirect()->route('hotspot.showConnected');
        }

        if ($request->hs === 'hs-staff') {
            return redirect()->route('auth.google.showLogin', [
                'callback' => route('hotspot.staff.callback', $request->query(), false),
                'domains' => config('services.google.allowed_domains'),
            ]);
        }

        if ($request->hs === 'hs-students') {
            return redirect()->route('auth.ypareo.showLogin', [
                'callback' => route('hotspot.students.callback', $request->query(), false),
            ]);
        }

        throw new BadRequestException("Invalid \"hs\" query parameter");
    }

    /**
     * Show "connected" view, at the end of the authentication process.
     *
     * @return \Illuminate\Http\Response
     */
    public function showConnected()
    {
        return view('hotspot.connected');
    }
}
