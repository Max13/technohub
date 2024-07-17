<?php

namespace App\Http\Controllers\Hotspot;

use App\Http\Controllers\Controller as BaseController;
use App\Services\Mikrotik\Hotspot;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Base controller for Hotspot
 */
abstract class Controller extends BaseController
{
    /**
     * Validate callback request
     *
     * @param  \Illuminate\Http\Request $request
     * @param  array                    $additionnalRules
     * @return \Illuminate\Http\Response|array
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateCallback(Request $request, array $additionnalRules = [])
    {
        // Validates hotspot + auth data
        $rules = array_merge([
            'captive'         => 'required|url',
            'dst'             => 'nullable|url',
            'hs'              => 'required|string',
            'ip'              => 'required|ip',
            'mac'             => 'required|mac_address',
            'auth.entryPoint' => 'required|string',
        ], $additionnalRules);

        $request->merge([
            'auth' => [
                'entryPoint' => $request->session()->pull('auth.entryPoint'),
                'user'       => $request->session()->pull('auth.user'),
            ],
        ]);
        $validator = validator($request->all(), $rules);

        throw_if(
            $validator->fails(),
            ValidationException::class,
            $validator,
            redirect()->to($request->input('auth.entryPoint'))->withErrors($validator)
        );
        // End hotspot + auth data validation

        return $validator->validated();
    }

    /**
     * Hotspot authentication callback
     *
     * @param  \Illuminate\Http\Request       $request
     * @param  \App\Services\Mikrotik\Hotspot $hotspot
     * @return \Illuminate\Http\Response
     */
    abstract public function callback(Request $request, Hotspot $hotspot);
}
