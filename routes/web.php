<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

// Kontrollerid
use App\Http\Controllers\Auth\EmailRegistrationController;
use App\Http\Controllers\ListingController;

/*
|--------------------------------------------------------------------------
| Avalikud lehed
|--------------------------------------------------------------------------
| Lehed, mis on kättesaadavad ka ilma sisselogimiseta.
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Kasutaja dashboard (vajab autentimist)
|--------------------------------------------------------------------------
*/
Route::view('/dashboard', 'dashboard')
    ->middleware(['auth'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| EHNET: kaheastmeline registreerimine (email → vorm)
|--------------------------------------------------------------------------
| 1. Kasutaja sisestab e-posti
| 2. Saadame kinnituskirja
| 3. Link viib registreerimisvormile
*/
Route::post('/register', [EmailRegistrationController::class, 'store'])
    ->name('register.store');

Route::get('/register/complete/{token}', [EmailRegistrationController::class, 'showCompleteForm'])
    ->name('register.complete');

Route::post('/register/complete/{token}', [EmailRegistrationController::class, 'complete'])
    ->name('register.complete.post');

/*
|--------------------------------------------------------------------------
| Autenditud kasutaja ala
|--------------------------------------------------------------------------
| Kõik allolevad teed eeldavad sisselogimist.
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Kasutaja seaded (Livewire / Volt)
    |--------------------------------------------------------------------------
    */
    Route::redirect('/settings', '/settings/profile');

    Volt::route('/settings/profile', 'settings.profile')
        ->name('profile.edit');

    Volt::route('/settings/password', 'settings.password')
        ->name('user-password.edit');

    Volt::route('/settings/appearance', 'settings.appearance')
        ->name('appearance.edit');

    /*
    |--------------------------------------------------------------------------
    | Kuulutused (Listings)
    |--------------------------------------------------------------------------
    */

    /*
    | Kuulutuse lisamine
    */
    Route::get('/listings/create', [ListingController::class, 'create'])
        ->name('listings.create');

    Route::post('/listings', [ListingController::class, 'store'])
        ->name('listings.store');

    /*
    |--------------------------------------------------------------------------
    | Minu kuulutused
    |--------------------------------------------------------------------------
    | Kasutaja enda kuulutuste haldus:
    | - vaatamine
    | - muutmine
    | - peatamine / aktiveerimine
    | - müüduks märkimine
    | - kustutamine
    */
    Route::prefix('/my-listings')->group(function () {

        // Minu kuulutuste loend
        Route::get('/', [ListingController::class, 'mine'])
            ->name('listings.mine');

        // Ühe kuulutuse detailvaade (minu vaade)
        Route::get('/{listing}', [ListingController::class, 'showMine'])
            ->name('listings.mine.show');

        // Kuulutuse muutmise vorm
        Route::get('/{listing}/edit', [ListingController::class, 'editMine'])
            ->name('listings.mine.edit');

        // Kuulutuse salvestamine pärast muutmist
        Route::patch('/{listing}', [ListingController::class, 'updateMine'])
            ->name('listings.mine.update');

        // Kuulutuse peatamine / aktiveerimine
        Route::patch('/{listing}/toggle', [ListingController::class, 'toggleMine'])
            ->name('listings.mine.toggle');

        // Kuulutuse kustutamine
        Route::delete('/{listing}', [ListingController::class, 'destroyMine'])
            ->name('listings.mine.destroy');

        // Märgi müüduks
        Route::patch('/{listing}/sold', [ListingController::class, 'markSold'])
            ->name('listings.mine.sold');

        // Taasta müük (müüdud → aktiivne)
        Route::patch('/{listing}/unsold', [ListingController::class, 'markUnsold'])
            ->name('listings.mine.unsold');

        // Mustand -> avalda (Aktiveeri)
        Route::patch('/{listing}/publish', [ListingController::class, 'publishMine'])
            ->name('listings.mine.publish');

    });

    /*
    |--------------------------------------------------------------------------
    | Kahefaktoriline autentimine (edasiarendus)
    |--------------------------------------------------------------------------
    */
    Volt::route('/settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                && Features::optionEnabled(
                    Features::twoFactorAuthentication(),
                    'confirmPassword'
                ),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
