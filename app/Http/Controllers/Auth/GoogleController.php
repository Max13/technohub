<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
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
     * Show Google OAuth login page (Continue with Google button)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showLogin(Request $request)
    {
        $view = view('auth.google.login');

        // Validate request: Shows view with errors
        $validator = validator($request->all(), [
            'callback' => 'required|string',
            'domains'  => 'sometimes|required|array|min:0',
        ]);

        if ($validator->stopOnFirstFailure()->fails()) {
            return $view->with([
                'callback' => null,
                'domains' => [],
            ])->withErrors($validator);
        }
        // Validate request

        $data = $validator->validated();

        return $view->with([
            'callback' => $data['callback'],
            'domains'  => $data['domains'] ?? [],
        ]);
    }

    /**
     * Redirects user to Google OAuth flow
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function redirect(Request $request)
    {
        Log::debug("Hotspot: Google Auth - Validating request.", $request->all());

        $data = $request->validate([
            'callback' => 'required|string',
            'domains'  => 'required|array|min:0',
        ]);

        $request->session()->flash('callback', $data['callback']);
        $request->session()->flash('domains', $data['domains']);

        if (count($data['domains']) === 1) {
            $with = ['hd' => $data['domains'][0]];
        } else {
            $with = [];
        }

        Log::debug("Hotspot: Google Auth - Redirecting to Google.", $with);

        return Socialite::driver('google')
                        ->with($with)
                        ->redirect();
    }

    /**
     * Google OAuth callback.
     * Redirects to callback route with entryPoint
     * and user info in case of errors
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request)
    {
        Log::debug("Hotspot: Google Auth - Callback.", $request->all());

        $entryPointRoute = route(
            'auth.google.showLogin',
            $request->session()->only(['callback', 'domains']),
            false
        );

        // Validate the session data.
        $validator = validator($request->session()->only(['callback', 'domains']), [
            'callback' => 'required|string',
            'domains'  => 'required|array|min:0',
        ]);

        if ($validator->fails()) {
            Log::debug("Hotspot: Google Auth - Callback validation failed.", $validator->failed());

            return redirect($entryPointRoute)->withErrors($validator);
        }

        Log::debug("Hotspot: Google Auth - Callback validation success.", $validator->validated());

        $sessionData = $validator->validated();
        // End session data validation

        try {
            $user = Socialite::driver('google')->user();
        } catch (Exception $e) {
            return redirect($entryPointRoute)->withErrors([
                __('Google authentication failed. Please try again.'),
            ]);
        }

        // Validate the user's domain
        $domains = $sessionData['domains'];
        if (count($domains) && !in_array($request->hd, $domains)) {
            Log::debug("Hotspot: Google Auth - Domain not authorized.", [$request->all(), $user, $domains]);

            return redirect($entryPointRoute)->withErrors([
                __('validation.custom.hd.in', ['domains' => join(', ', $domains)]),
            ]);
        }
        // End user's domain validation

        Log::debug("Hotspot: Google Auth - Redirecting user to app.", [$request->all(), $user, $entryPointRoute]);

        $request->session()->flash('auth.entryPoint', $entryPointRoute);
        $request->session()->flash('auth.user', $user);

        return redirect($sessionData['callback']);
    }
}
