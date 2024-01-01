<?php

namespace App\Exceptions\Hotspot;

use Symfony\Component\HttpFoundation\Exception\BadRequestException as BaseBadRequestException;

class BadRequestException extends BaseBadRequestException
{
    /**
     * Get the exception's context information.
     *
     * @return array
     */
    public function context()
    {
        return request()->query();
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        if (app()->environment('production')) {
            return response()->view(
                'hotspot.generic-error',
                ['message' => $this->getMessage()],
                400
            );
        }
    }
}
