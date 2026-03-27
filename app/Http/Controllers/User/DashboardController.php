<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $activeListingsCount = $user->listings()
            ->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->count();

        $favoritesCount = $user->favorites()->count();

        return view('user.dashboard', [
            'activeListingsCount' => $activeListingsCount,
            'favoritesCount' => $favoritesCount,
        ]);
    }
}