<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\FailedPasswordResetResponse;

class InvalidPasswordResetResponse implements FailedPasswordResetResponse
{
    public function toResponse($request)
    {
        return redirect()
            ->route('password.request')
            ->with(
                'error',
                __('Parooli lähtestamise link on aegunud või kehtetu. Palun küsi uus link.')
            );
    }
}