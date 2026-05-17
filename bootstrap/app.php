<?php

use App\Http\Middleware\PreventAuthPageCache;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            PreventAuthPageCache::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sessioon aegus. Palun värskenda lehte ja proovi uuesti.',
                ], 419);
            }

            $safeRedirect = function (?string $url) use ($request): ?string {
                if (! is_string($url) || trim($url) === '') {
                    return null;
                }

                $url = trim($url);

                if (str_starts_with($url, '//')) {
                    return null;
                }

                if (str_starts_with($url, '/')) {
                    $path = parse_url($url, PHP_URL_PATH) ?: '/';
                    $query = parse_url($url, PHP_URL_QUERY);
                } else {
                    $host = parse_url($url, PHP_URL_HOST);

                    if (! $host || $host !== $request->getHost()) {
                        return null;
                    }

                    $path = parse_url($url, PHP_URL_PATH) ?: '/';
                    $query = parse_url($url, PHP_URL_QUERY);
                }

                $path = '/'.ltrim($path, '/');
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

                return $query ? $path.'?'.$query : $path;
            };

            $redirectTo = $safeRedirect($request->headers->get('referer'));

            if (auth()->check()) {
                return redirect()
                    ->to($redirectTo ?? route('home'))
                    ->with('status', 'Sessioon aegus. Palun proovi tegevust uuesti.');
            }

            if ($redirectTo) {
                return redirect()
                    ->route('login', ['redirect' => $redirectTo])
                    ->with('status', 'Sessioon aegus. Palun logi uuesti sisse.');
            }

            return redirect()
                ->route('login')
                ->with('status', 'Sessioon aegus. Palun logi uuesti sisse.');
        });
    })
    ->create();
