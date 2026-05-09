<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\User;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    public function show(User $user): View
    {

        $user->load('location');

        $user->loadCount([
            'listings as active_listings_count' => function ($query) {
                $query->publicVisible();
            },
        ]);

        $activeListings = Listing::query()
            ->with(['images', 'location', 'category'])
            ->where('user_id', $user->id)
            ->publicVisible()
            ->latest('created_at')
            ->paginate(24, ['*'], 'listings_page');

        $reviews = $user->reviewsReceived()
            ->with(['reviewer', 'trade'])
            ->latest('created_at')
            ->paginate(20, ['*'], 'reviews_page');

        return view('users.show', [
            'profileUser' => $user,
            'activeListings' => $activeListings,
            'reviews' => $reviews,
        ]);
    }
}
