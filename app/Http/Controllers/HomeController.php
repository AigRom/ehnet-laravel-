<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Listing;
use App\Services\Statistics\PlatformStatisticsService;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index(PlatformStatisticsService $statistics)
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name_et')
            ->get(['id', 'name_et']);
        //Esile tõstetud kuulutuste kuvamine, enne selle lõpliku funktsionaalsuse arendamist. Random 4 kuulutust vahetuvad iga 10min järel.
        $featured = Cache::remember('home_featured_random_listings', now()->addMinutes(10), function () {
            return Listing::query()
                ->publicVisible()
                ->with(['category', 'location', 'images'])
                ->inRandomOrder()
                ->limit(4)
                ->get();
        });

        $latest = Listing::query()
            ->homeFeed()
            ->with(['category', 'location', 'images'])
            ->limit(12)
            ->get();

        $stats = $statistics->publicSummary();

        $usersCount = $stats['usersCount'];
        $listingsCount = $stats['listingsCount'];
        $savedCo2 = $stats['savedCo2'];

        return view('home', compact(
            'featured',
            'latest',
            'categories',
            'usersCount',
            'listingsCount',
            'savedCo2'
        ));
    }
}