<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User as LocalUser;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use LdapRecord\Auth\BindException;
use LdapRecord\Container;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use LdapRecord\Models\ModelNotFoundException as LdapModelNotFoundException;

class LdapController extends Controller
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
     * Show LDAP login page
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function showLogin(Request $request)
    {
        Log::debug("Hotspot from $request->mac : Showing login form.", $request->all());

        $view = view('auth.ldap.login');

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
     * Logs in user via LDAP
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function doLogin(Request $request)
    {
        Log::debug("Hotspot from $request->mac : Logging in to LDAP.", $request->all());

        $data = $request->validate(
            [
                'callback' => 'required|string',
                'hs' => 'required|in:hs-staff',
                'username' => 'required',
                'password' => 'required',
            ],[
                'hs.in' => __('This hotspot server is invalid.'),
            ]
        );

        try {
            $userprincipalname = $data['username'].'@'.Str::after(config('ldap.connections.'.Container::getDefaultConnectionName().'.username'), '@');

            Container::getDefaultConnection()->auth()->bind($userprincipalname, $data['password']);
        } catch (Exception $e) {
            Log::debug("Hotspot from $request->mac : Credentials refused.", [
                'request' => $request->all(),
                'session' => $request->session()->all(),
                'errorType' => get_class($e),
                'error' => $e->getDetailedError(),
            ]);

            if ($e instanceof BindException) {
                $error = match ($e->getCode()) {
                    49 => __('The username or password is incorrect.'),
                };
            } elseif ($e instanceof LdapModelNotFoundException) {
                $error = __('The username or password is incorrect.');
            } else {
                $error = __('Unknown error: ').$e->getDetailedError()->getErrorMessage();
            }

            return back()->withErrors([
                $error,
            ])->withInput();
        }

        $ldapUser = LdapUser::findBy('samaccountname', $data['username'], ['cn', 'sn', 'givenname', 'mail', 'memberof']);
        if (!collect($ldapUser->memberof)->contains(fn ($group) => Str::contains($group, 'CN=Collaborateurs,OU=Groupes'))) {
            return back()->withErrors([
                __('YThis network is only authorized to the Staff of ITIC Paris.'),
            ])->withInput();
        }

        $localUser = LocalUser::unguarded(function () use ($data, $ldapUser) {
            return LocalUser::firstOrCreate(
                [
                    'email' => $ldapUser->mail[0],
                ],
                [
                    'is_staff' => true,
                    'is_student' => false,
                    'is_trainer' => false,
                    'ypareo_id' => null,
                    'lastname' => $ldapUser->sn[0],
                    'firstname' => $ldapUser->givenname[0],
                    'email' => $ldapUser->mail[0],
                    'email_verified_at' => now(),
                    'password' => $data['password'],
                ]
            );
        });
        $callback = $data['callback'] . '?' . http_build_query($request->all());

        $request->session()->keep(['auth.entryPoint']);
        $request->session()->flash('auth.user', $localUser->only(['id', 'fullname']));

        Log::debug("Hotspot from $request->mac : Credentials accepted. Injecting user to session, redirecting to callback", [
            'request' => $request->all(),
            'session' => $request->session()->all(),
            'callback' => $callback,
        ]);

        return redirect($callback);
    }
}
