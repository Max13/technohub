<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Ypareo;
use Illuminate\Http\Request;

class IticController extends Controller
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
     * Show ITIC login page
     *
     * @return \Illuminate\Http\Response
     */
    public function showLogin()
    {
        return view('auth.itic.login');
    }

    /**
     * Logs in user via ITIC
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function doLogin(Request $request)
    {
        $data = $request->validate(
            [
                'username' => 'required|exists:users,ypareo_login',
                'password' => 'required',
            ],[
                'username.exists' => __('The username or password is incorrect.'),
            ]
        );

        if (auth()->attempt(['ypareo_login' => $data['username'], 'password' => $data['password']])) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'username' => __('The username or password is incorrect.'),
        ])->onlyInput('username');
    }
}
