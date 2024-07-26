<?php

namespace App\Http\Controllers;

use App\Exceptions\Hotspot\BadRequestException;
use App\Services\Mikrotik\Hotspot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        Log::debug("Hotspot from $request->mac : Connection.", $request->all());

        if ($hotspot->findUser($request->hs, $request->mac)) {
            Log::debug("Hotspot from $request->mac : Already logged-in, redirecting.", $request->all());

            if ($request->dst) {
                return redirect()->away($request->dst);
            }
            return redirect()->route('hotspot.showConnected');
        }

        if ($request->hs === 'hs-staff') {
            return redirect()->route('auth.google.showLogin', [
                'callback' => route('hotspot.staff.callback', $request->query(), false),
                'domains' => config('services.google.allowed_domains'),
            ]);
        }

        if ($request->hs === 'hs-students') {
            return redirect()->route('auth.ypareo.showLogin', array_merge($request->query(), [
                'callback' => route('hotspot.students.callback', $request->query(), false),
            ]));
        }

        Log::debug("Hotspot from $request->mac : Bad \"hs\" query parameter.", $request->all());

        throw new BadRequestException("Invalid \"hs\" query parameter");
    }

    /**
     * Show "connected" view, at the end of the authentication process.
     *
     * @param \Illuminate\Http\Request   $request
     * @return \Illuminate\Http\Response
     */
    public function showConnected(Request $request)
    {
        return view('hotspot.connected', [
            'dst' => $request->dst,
        ]);
    }

    /**
     * Show "status" view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showStatus(Request $request)
    {
        // captive=$(link-login-only-esc)
        // hs=$(server-name-esc)
        // ip=$(ip-esc)
        // mac=$(mac-esc)
        // uptime=$(uptime) in seconds
        // bytes_in=$(bytes-in-nice-esc)
        // bytes_out=$(bytes-out-nice-esc)

        return view('hotspot.status', [
            'captive' => $request->captive,
            'hs' => $request->hs,
            'ip' => $request->ip,
            'mac' => $request->mac,
            'uptime' => $request->uptime,
            'bytes_in' => $request->bytes_in,
            'bytes_out' => $request->bytes_out,
        ]);
    }
}
