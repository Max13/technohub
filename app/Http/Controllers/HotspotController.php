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
        }

        if ($request->hs === 'hs-students') {
            return redirect()->route('auth.ypareo.showLogin', [
                'callback' => route('hotspot.students.callback', $request->query(), false),
            ]);
        }

        throw new BadRequestException("Invalid \"hs\" query parameter");
    }
}
