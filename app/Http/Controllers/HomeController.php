<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Listing;

class HomeController extends Controller
{
    public function index() {
        // Esiletõstetud kuulutused - Praegu on “featured” lihtsalt 4 sama feed’i kõige värskemat. Hiljem vahetame selle päris featured-loogika vastu.
        $featured = Listing::query()
            ->homeFeed()
            ->with(['category', 'location', 'images'])
            ->limit(4)
            ->get();

        //Viimati lisatud kuulutused
        $latest = Listing::query()
            ->homeFeed()
            ->with(['category', 'location','images'])
            ->limit(12)
            ->get();

        return view('home', compact('featured', 'latest'));
    }

}
