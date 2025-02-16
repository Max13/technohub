<?php

namespace App\Http\Middleware;

use App\Exceptions\ProtectedBySebException;
use Closure;
use Illuminate\Http\Request;

class VerifySebIntegrity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $sebConfigHashHeader = 'X-SafeExamBrowser-ConfigKeyHash';
        $assignment = $request->route('assignment');
        $exam = $assignment->exam;

        if ($exam->seb_config_key) {
            if (!$request->hasHeader($sebConfigHashHeader)) {
                throw new ProtectedBySebException(__('User is not using SEB'));
            }

            if (!hash_equals(hash('sha256', $request->fullUrl().$exam->seb_config_key), $request->header($sebConfigHashHeader))) {
                throw new ProtectedBySebException(__('Wrong SEB configuration'));
            }
        }

        return $next($request);
    }
}
