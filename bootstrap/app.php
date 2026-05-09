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
                if (! $url) {
                    return null;
                }

                if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
                    return $url;
                }

                $host = parse_url($url, PHP_URL_HOST);

                if ($host && $host === $request->getHost()) {
                    return $url;
                }

                return null;
            };

            $redirectTo = $safeRedirect($request->headers->get('referer'));

            if (! $redirectTo) {
                $redirectTo = route('home');
            }

            $path = parse_url($redirectTo, PHP_URL_PATH);
            $cleanPath = trim((string) $path, '/');

            if (in_array($cleanPath, ['login', 'logout'], true)) {
                $redirectTo = route('home');
            }

            if (auth()->check()) {
                return redirect()
                    ->to($redirectTo)
                    ->with('status', 'Sessioon uuendati. Palun proovi tegevust uuesti.');
            }

            return redirect()
                ->route('login', ['redirect' => $redirectTo])
                ->with('status', 'Sessioon aegus. Palun logi uuesti sisse.');
        });
    })
    ->create();
