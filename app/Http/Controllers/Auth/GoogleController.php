<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

    public function showLogin(Request $request)
    {
        $data = $request->validate([
            'callback' => 'required|string',
            'domains' => 'required|array|min:0',
        ]);

        return view('auth.google.login', [
            'domains' => $data['domains'] ?? [],
            'callback' => $data['callback'],
        ]);
    }

    public function redirect(Request $request)
    {
        $data = $request->validate([
            'callback' => 'required|string',
            'domains' => 'sometimes|required|array|min:0',
        ]);

        $request->session()->flash('callback', $data['callback']);

        return Socialite::driver('google')
                        ->with(['hd' => $data['domains'] ?? []])
                        ->redirect();
    }

    public function callback(Request $request)
    {
        dd($request->all(), $request->session()->all(), $request->session()->get('callback'));
    }
}
