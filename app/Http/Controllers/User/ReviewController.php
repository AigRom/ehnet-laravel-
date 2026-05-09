<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function received(Request $request): View
    {
        $user = $request->user();

        $reviews = $user->receivedReviews()
            ->with([
                'reviewer',
                'trade.listing',
            ])
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('user.reviews.received', [
            'reviews' => $reviews,
        ]);
    }
}
