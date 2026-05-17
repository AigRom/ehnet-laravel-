<?php

namespace App\Providers;

use App\Services\Messaging\UnreadMessageService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(UnreadMessageService $unreadMessageService): void
    {
        RateLimiter::for('support', function (Request $request) {
            return Limit::perMinute(3)->by($request->user()?->id ?: $request->ip());
        });

        View::composer('components.layouts.app.header', function ($view) use ($unreadMessageService) {
            $unreadConversationsCount = Auth::check()
                ? $unreadMessageService->unreadConversationsCountFor(Auth::user())
                : 0;

            $view->with('unreadConversationsCount', $unreadConversationsCount);
        });
    }
}