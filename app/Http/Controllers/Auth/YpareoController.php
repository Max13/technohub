<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Ypareo;
use Exception;
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
        Log::debug("Hotspot from $request->mac : Showing login form.", $request->except('password'));

        $view = view('auth.ypareo.login');

        // Validate request: Shows view with errors
        $validator = validator($request->except('password'), [
            'callback' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::debug("Hotspot from $request->mac : Validator fails.", $request->except('password'));

            return $view->with([
                'callback' => null,
            ])->withErrors($validator);
        }
        // Validate request

        $request->session()->flash('auth.entryPoint', $_SERVER['REQUEST_URI']);

        Log::debug("Hotspot from $request->mac : Entry point added to the session.", [
            'request' => $request->except('password'),
            'session' => $request->session()->all(),
        ]);

        return $view->with([
            'request' => $request->except('password'),
        ]);
    }

    /**
     * Check user credentials for authentication.
     *
     * This method attempts to authenticate the user by validating local credentials
     * and, if necessary, retrieving and updating the user's information from Ypareo.
     *
     * @param  string               $username
     * @param  string               $password
     *
     * @return bool
     */
    protected function checkCredentials($username, $password) : bool
    {
        /** @var \App\Services\Ypareo $ypareo */
        $ypareo = app(Ypareo::class);

        Log::debug("Hotspot login: Checking local credentials.", [
            'request' => request()->all(),
            'username' => $username,
        ]);

        try {
            // Try local login first
            if (auth()->validate(['ypareo_login' => $username, 'password' => $password])) {
                Log::debug("Hotspot login: Local credentials passed.", [
                    'username' => $username,
                ]);

                return true;
            }

            Log::debug("Hotspot login: Local credentials failed.", [
                'username' => $username,
            ]);
            // ---

            // Retrieve and update Ypareo user
            if (($ypareoUser = $ypareo->retrieve($username, false))) {
                Log::debug("Hotspot login: Retrieve and update Ypareo user.", $ypareoUser);
                User::where('ypareo_login', $username)
                    ->sole()
                    ->forceFill(['password' => $ypareoUser['PASSWORD_UTILISATEUR_CRYPTE']])
                    ->save();
            }

            // Try local login final
            if (auth()->validate(['ypareo_login' => $username, 'password' => $password])) {
                Log::debug("Hotspot login: Local credentials passed.", [
                    'username' => $username,
                ]);

                return true;
            }

            Log::debug("Hotspot login: Local credentials failed AGAIN. Aborting", [
                'username' => $username,
            ]);
            // ---
        } catch (Exception $e) {
            Log::debug("Hotspot login: Exception.", [
                'username' => $username,
                'exception' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'line' => $e->getLine(),
                ],
            ]);
        }

        return false;
    }

    /**
     * Logs in user via Ypareo
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function doLogin(Request $request)
    {
        Log::debug("Hotspot from $request->mac : Logging in to Ypareo.", $request->except('password'));

        $data = $request->validate(
            [
                'callback' => 'required|string',
                'hs' => 'required|in:hs-students',
                'username' => 'required|exists:users,ypareo_login',
                'password' => 'required',
            ],[
                'username.exists' => __('The username or password is incorrect.'),
                'hs.in' => __('This hotspot server is invalid.'),
            ]
        );

        if ($this->checkCredentials($data['username'], $data['password'])) {
            $user = User::where('ypareo_login', $data['username'])->sole();
            $callback = $data['callback'] . '?' . http_build_query($request->except('password'));


            $request->session()->keep(['auth.entryPoint']);
            $request->session()->flash('auth.user', $user->only(['id', 'fullname']));

            Log::debug("Hotspot from $request->mac : Credentials accepted. Injecting user to session, redirecting to callback", [
                'request' => $request->except('password'),
                'session' => $request->session()->all(),
                'callback' => $callback,
            ]);

            return redirect($callback);
        }

        Log::debug("Hotspot from $request->mac : Credentials refused.", [
            'request' => $request->except('password'),
            'session' => $request->session()->all(),
        ]);

        return back()->withErrors([
            __('The username or password is incorrect.'),
        ])->withInput();
    }
}
