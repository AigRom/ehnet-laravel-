<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\User;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    /**
     * Kuvab avaliku kasutajaprofiili koos aktiivsete kuulutuste ja tagasisidega.
     */
    public function show(User $user): View
    {
        // Laeme profiili jaoks vajalikud seosed.
        $user->load('location');

        // Loeme kokku avalikult nähtavad aktiivsed kuulutused.
        $user->loadCount([
            'listings as active_listings_count' => function ($query) {
                $query->publicVisible();
            },
        ]);

        // Kasutaja aktiivsed kuulutused profiili "kuulutused" vaate jaoks.
        $activeListings = Listing::query()
            ->with(['images', 'location', 'category'])
            ->where('user_id', $user->id)
            ->publicVisible()
            ->latest('created_at')
            ->paginate(12, ['*'], 'listings_page');

        // Kasutajale jäetud tagasisided profiili "tagasiside" vaate jaoks.
        $reviews = $user->reviewsReceived()
            ->with(['reviewer', 'trade'])
            ->latest('created_at')
            ->paginate(10, ['*'], 'reviews_page');

        return view('users.show', [
            'profileUser' => $user,
            'activeListings' => $activeListings,
            'reviews' => $reviews,
        ]);
    }
}