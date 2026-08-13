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
            Log::debug("Hotspot from $request->mac : Redirecting staff member.", $request->all());

            return redirect()->route('auth.ypareo.showLogin',
                array_merge(
                    $request->query(),
                    [
                        'callback' => route('hotspot.staff.callback', absolute: false)
                    ],
                ),
            );
        }

        if ($request->hs === 'hs-students') {
            Log::debug("Hotspot from $request->mac : Redirecting student.", $request->all());

            return redirect()->route('auth.ypareo.showLogin',
                array_merge(
                    $request->query(),
                    [
                        'callback' => route('hotspot.students.callback', absolute: false)
                    ],
                ),
            );
        }

        Log::debug("Hotspot from $request->mac : Bad \"hs\" query parameter.", $request->all());

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

    /**
     * Show "status" view, showing current consumption.
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  \App\Services\Mikrotik\Hotspot $hotspot
     * @return \Illuminate\Http\Response
     * @throws \App\Exceptions\Hotspot\BadRequestException
     */
    public function showStatus(Request $request, Hotspot $hotspot)
    {
        if (($user = $hotspot->findUser($request->hs, $request->mac)) !== false) {
            Log::debug("Hotspot from $request->mac : User found.", [$request->all(), $user]);

            return view('hotspot.status');
        }

        Log::debug("Hotspot from $request->mac : User not found.", $request->all());

        return view('hotspot.generic-error');
    }
}
