<?php

namespace App\Http\Controllers\Hotspot;

use App\Models\User;
use App\Services\Mikrotik\Hotspot;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * Hotspot authentication controller for Staff
 */
class StaffController extends Controller
{
    /**
     * @inheritDoc
     *
     * TODO: Merge with StudentController::callback()
     */
    public function callback(Request $request, Hotspot $hotspot)
    {
        Log::debug("Hotspot from $request->mac : Calling ".self::class.'::'.__FUNCTION__.'.', [
            'request' => $request->all(),
            'session' => $request->session()->all(),
        ]);

        // This will merge session's auth.user and auth.entryPoint to Request
        $data = $this->validateCallback($request, [
            'auth.user.id' => [
                'required',
                Rule::exists('users', 'id')->where(function (Builder $query) {
                    return $query->where('is_staff', true)
                                 ->orWhere(function (Builder $query) {
                                     $query->join('role_user', 'users.id', '=', 'role_user.user_id')
                                           ->join('roles', function (JoinClause $join) {
                                               $join->on('role_user.role_id', '=', 'roles.id')
                                                    ->where('roles.name', 'Staff');
                                           });
                                 });
                }),
            ],
            'auth.user.fullname' => 'required|string',
        ]);

        if ($hotspot->createUser($data['hs'], $data['mac'], $data['mac'], $data['auth']['user']['fullname'])) {
            DB::table('hotspot_history')->insert([
                'server' => $data['hs'],
                'user_id' => $data['auth']['user']['id'],
                'mac' => $data['mac'],
                'created_at' => now(),
            ]);

            return redirect()->away($data['captive'] . '?' . http_build_query([
                'dst' => route('hotspot.showConnected'),
                'username' => $data['mac'],
                'password' => $data['mac'],
            ]));
        }

        return redirect($data['auth.entryPoint'])->withErrors([
            __('Hotspot authentication failed. Please try again.'),
        ])->withInput();
    }
}
