<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Ypareo;
use Faker\Generator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $view = view('auth.ypareo.login');

        // Validate request: Shows view with errors
        $validator = validator($request->all(), [
            'callback' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $view->with([
                'callback' => null,
            ])->withErrors($validator);
        }
        // Validate request

        $data = $validator->validated();
        $request->session()->flash('auth.entryPoint', $_SERVER['REQUEST_URI']);

        return $view->with([
            'callback' => $data['callback'],
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
            $user = DB::table('users')->where('ypareo_login', $data['username'])->first();

            $request->session()->flash('auth.user', $user);

            return redirect($data['callback']);
        }

        return back()->withErrors([
            __('The username or password is incorrect.'),
        ])->withInput();
    }
}
