<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;

class NeutralPasswordResetLinkResponse implements SuccessfulPasswordResetLinkRequestResponse, FailedPasswordResetLinkRequestResponse
{
    public function toResponse($request)
    {
        return back()->with(
            'status',
            __('Kui selle e-posti aadressiga konto on olemas, saadeti Sulle parooli lähtestamise link.')
        );
    }
}