<?php

namespace App\Providers;

use App\Services\Messaging\UnreadMessageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(UnreadMessageService $unreadMessageService): void
    {
        View::composer('components.layouts.app.header', function ($view) use ($unreadMessageService) {
            $unreadConversationsCount = Auth::check()
                ? $unreadMessageService->unreadConversationsCountFor(Auth::user())
                : 0;

            $view->with('unreadConversationsCount', $unreadConversationsCount);
        });
    }
}