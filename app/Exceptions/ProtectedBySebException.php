<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class ProtectedBySebException extends Exception
{
    /**
     * Render the exception as an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render(Request $request)
    {
        return response()->view('errors.protected_by_seb', [
            'message' => $this->getMessage(),
        ], 403);
    }
}
