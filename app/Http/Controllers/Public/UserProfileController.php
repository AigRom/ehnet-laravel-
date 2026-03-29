<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\User;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    /**
     * Kuvab avaliku kasutajaprofiili koos aktiivsete kuulutustega.
     */
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
            ->paginate(12);

        return view('users.show', [
            'profileUser' => $user,
            'activeListings' => $activeListings,
            'score' => null,
            'reviewsCount' => null,
            'reviews' => collect(),
        ]);
    }
}