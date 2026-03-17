<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('components.layouts.app.header', function ($view) {
            $unreadConversationsCount = 0;

            if (Auth::check()) {
                $userId = Auth::id();

                $unreadConversationsCount = Message::query()
                    ->whereNull('read_at')
                    ->where('sender_id', '!=', $userId)
                    ->whereHas('conversation', function ($query) use ($userId) {
                        $query->where('seller_id', $userId)
                            ->orWhere('buyer_id', $userId);
                    })
                    ->distinct('conversation_id')
                    ->count('conversation_id');
            }

            $view->with('unreadConversationsCount', $unreadConversationsCount);
        });
    }
}
