<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Ypareo;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

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
        return view('auth.itic.login', [
            'remoteAddr' => request()->server('REMOTE_ADDR'),
        ]);
    }

    /**
     * Logs in user via ITIC
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function doLogin(Request $request, Ypareo $ypareo)
    {
        $data = $request->validate(
            [
                'username' => 'required|exists:users,ypareo_login',
                'password' => 'required',
                'remember' => 'sometimes|required|boolean',
            ],[
                'username.exists' => __('The username or password is incorrect.'),
            ]
        );

        if ($ypareo->auth($data['username'], $data['password'], $request->userAgent())) {
            auth()->login(User::where('ypareo_login', $data['username'])->sole(), $data['remember'] ?? false);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'username' => __('The username or password is incorrect.'),
        ])->onlyInput('username');
    }

    /**
     * Send password reset link
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function sendPasswordReset(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
        ]);

        if ($user = User::where('email', $data['email'])->first()) {
            $token = (string) Str::uuid();

            DB::transaction(function () use ($user, $token) {
                DB::table('password_resets')->insert([
                    'email' => $user->email,
                    'token' => $token,
                    'created_at' => now(),
                ]);

                ResetPasswordNotification::createUrlUsing(function (CanResetPassword $notifiable, $token) {
                    return URL::temporarySignedRoute(
                        'auth.itic.showPasswordReset',
                        now()->addMinutes(config('auth.passwords.'.config('auth.defaults.passwords').'.expire')),
                        [
                            'token' => $token,
                            'email' => $notifiable->getEmailForPasswordReset(),
                        ]
                    );
                });
                $user->sendPasswordResetNotification($token);
            });
        }

        return view('auth.itic.password-reset-sent');
    }

    /**
     * Show password reset form
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function showPasswordReset(Request $request)
    {
        $passwordResetTable = config('auth.passwords.'.config('auth.defaults.passwords').'.table');
        $passwordResetExpiry = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|uuid',
        ]);

        if (
               $validator->fails()
            || DB::table($passwordResetTable)
                 ->where('email', $request->email)
                 ->where('token', $request->token)
                 ->where('created_at', '>=', now()->subMinutes($passwordResetExpiry))
                 ->doesntExist()
        ) {
            abort(404);
        }

        return view('auth.itic.password-reset-form');
    }

    /**
     * Do password reset
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function doPasswordReset(Request $request)
    {
        $passwordResetTable = config('auth.passwords.'.config('auth.defaults.passwords').'.table');
        $passwordResetExpiry = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        $data = $request->validate([
            'email' => 'required|email',
            'token' => 'required|uuid',
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ]
        ]);

        $resetLink = DB::table($passwordResetTable)
                       ->where('email', $request->email)
                       ->where('token', $request->token)
                       ->where('created_at', '>=', now()->subMinutes($passwordResetExpiry));

        abort_if($resetLink->doesntExist(), 404);

        DB::transaction(function () use ($data, $resetLink) {
            $user = User::where('email', $data['email'])->firstOrFail();
            $user->password = bcrypt($data['password']);
            $user->save();

            $resetLink->delete();
        });

        $request->session()->flash('success', __('Your password has been reset'));
        return redirect()->route('auth.itic.showLogin');
    }
}
