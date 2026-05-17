<?php

namespace App\Http\Responses;

use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Cookie;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $currentUserId = $request->user()?->id;
        $previousUserId = $request->cookie('ehnet_last_auth_user_id');

        $isDifferentUser = $previousUserId
            && $currentUserId
            && (int) $previousUserId !== (int) $currentUserId;

        Cookie::queue(cookie(
            name: 'ehnet_last_auth_user_id',
            value: (string) $currentUserId,
            minutes: 60 * 24 * 30,
            path: '/',
            domain: null,
            secure: null,
            httpOnly: true,
            raw: false,
            sameSite: 'lax'
        ));

        $request->session()->forget('url.intended');

        if ($isDifferentUser) {
            return redirect()->route('home');
        }

        $redirect = $this->safeRedirect($request->input('redirect'), $request)
            ?? $this->safeRedirect($request->session()->pull('url.intended'), $request)
            ?? route('home');

        return redirect()->to($redirect);
    }

    private function safeRedirect(mixed $redirect, Request $request): ?string
    {
        if (! is_string($redirect) || trim($redirect) === '') {
            return null;
        }

        $redirect = trim($redirect);

        if (str_starts_with($redirect, '//')) {
            return null;
        }

        if (str_starts_with($redirect, '/')) {
            $path = parse_url($redirect, PHP_URL_PATH) ?: '/';
            $query = parse_url($redirect, PHP_URL_QUERY);
        } else {
            $host = parse_url($redirect, PHP_URL_HOST);

            if (! $host || $host !== $request->getHost()) {
                return null;
            }

            $path = parse_url($redirect, PHP_URL_PATH) ?: '/';
            $query = parse_url($redirect, PHP_URL_QUERY);
        }

        $path = '/'.ltrim($path, '/');

        return $this->allowedPath($path, $query);
    }

    private function allowedPath(string $path, ?string $query = null): ?string
    {
        $cleanPath = trim($path, '/');

        $blockedPrefixes = [
            'login',
            'logout',
            'register',
            'register/complete',
            'forgot-password',
            'reset-password',
            'email/verify',
            'user/confirm-password',
        ];

        foreach ($blockedPrefixes as $blockedPrefix) {
            if ($cleanPath === $blockedPrefix || str_starts_with($cleanPath, $blockedPrefix.'/')) {
                return null;
            }
        }

        if ($path === '/') {
            return route('home');
        }

        if ($path === '/dashboard') {
            return route('dashboard');
        }

        if ($path === '/listings') {
            return $query ? route('listings.index').'?'.$query : route('listings.index');
        }

        if ($path === '/listings/create') {
            return route('listings.create');
        }

        if (preg_match('#^/listings/(\d+)$#', $path, $matches)) {
            $listingId = (int) $matches[1];

            if (Listing::query()->whereKey($listingId)->exists()) {
                return route('listings.show', $listingId);
            }

            return null;
        }

        if ($path === '/messages' || preg_match('#^/messages/\d+$#', $path)) {
            return route('messages.index');
        }

        if ($path === '/my-listings' || str_starts_with($path, '/my-listings/')) {
            return route('listings.mine');
        }

        if ($path === '/my/purchases' || str_starts_with($path, '/my/purchases/')) {
            return route('purchases.index');
        }

        if ($path === '/favorites') {
            return route('favorites.index');
        }

        if ($path === '/settings/profile') {
            return route('profile.edit');
        }

        if ($path === '/settings/password') {
            return route('user-password.edit');
        }

        return null;
    }
}