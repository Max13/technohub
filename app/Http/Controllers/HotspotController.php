<?php

namespace App\Http\Controllers;

use App\Exceptions\Hotspot\BadRequestException;
use Illuminate\Http\Request;

class HotspotController extends Controller
{
    public function redirectToLogin(Request $request)
    {
        if ($request->hs === 'hs-staff') {
            return redirect()->route('auth.google.showLogin', [
                'callback' => route('hotspot.staff.callback', $request->query(), false),
                'domains' => config('services.google.allowed_domains'),
            ]);
        } elseif ($request->hs === 'hs-students') {
            $route = redirect()->route('hotspot.ypareo.showLogin', $request->query());
        } else {
            $route = null;
        }

        if ($route) {
            session()->flash('intent', 'hotspot/ok');
        }

        throw new BadRequestException("Invalid \"hs\" query parameter");
    }
}
