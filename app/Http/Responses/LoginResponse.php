<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $redirect = $request->input('redirect');

        if ($this->isSafeRedirect($redirect, $request)) {
            return redirect()->to($redirect);
        }

        return redirect()->intended(route('home'));
    }

    private function isSafeRedirect(?string $redirect, $request): bool
    {
        if (! $redirect) {
            return false;
        }

        if (str_starts_with($redirect, '/') && ! str_starts_with($redirect, '//')) {
            return true;
        }

        $redirectHost = parse_url($redirect, PHP_URL_HOST);

        return $redirectHost && $redirectHost === $request->getHost();
    }
}
