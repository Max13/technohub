<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Ypareo;
use Faker\Generator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class YpareoController extends Controller
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
     * Show Ypareo login page
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showLogin(Request $request)
    {
        Log::debug("Hotspot from $request->mac : Showing login form.", $request->all());

        $view = view('auth.ypareo.login');

        // Validate request: Shows view with errors
        $validator = validator($request->all(), [
            'callback' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::debug("Hotspot from $request->mac : Validator fails.", $request->all());

            return $view->with([
                'callback' => null,
            ])->withErrors($validator);
        }
        // Validate request

        $request->session()->flash('auth.entryPoint', $_SERVER['REQUEST_URI']);

        Log::debug("Hotspot from $request->mac : Entry point added to the session.", [
            'request' => $request->all(),
            'session' => $request->session()->all(),
        ]);

        return $view->with([
            'request' => $request->all(),
        ]);
    }

    /**
     * Logs in user via Ypareo
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function doLogin(Request $request, Ypareo $ypareo)
    {
        Log::debug("Hotspot from $request->mac : Logging in to Ypareo.", $request->all());

        $data = $request->validate(
            [
                'callback' => 'required|string',
                'username' => 'required|exists:users,ypareo_login',
                'password' => 'required',
            ],[
                'username.exists' => __('The username or password is incorrect.'),
            ]
        );

        if ($ypareo->auth($data['username'], $data['password'], app(Generator::class)->userAgent())) {
            $user = User::where('ypareo_login', $data['username'])->first();

            $request->session()->keep(['auth.entryPoint']);
            $request->session()->flash('auth.user', $user->toArray());

            Log::debug("Hotspot from $request->mac : Credentials accepted. Injecting user to session", [
                'request' => $request->all(),
                'session' => $request->session()->all(),
            ]);

            return redirect($data['callback']);
        }

        Log::debug("Hotspot from $request->mac : Credentials refused.", [
            'request' => $request->all(),
            'session' => $request->session()->all(),
        ]);

        return back()->withErrors([
            __('The username or password is incorrect.'),
        ])->withInput();
    }
}
