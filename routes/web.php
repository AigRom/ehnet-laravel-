<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

// Kontrollerid
use App\Http\Controllers\Auth\EmailRegistrationController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\ListingQuickController;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Avalikud lehed
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('listings')->group(function () {
    // kõik kuulutused
    Route::get('/', [ListingController::class, 'index'])->name('listings.index');

    // detail (ainult number -> ei söö /listings/create ära)
    Route::get('/{listing}', [ListingController::class, 'show'])
        ->whereNumber('listing')
        ->name('listings.show');
});

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
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Kasutaja seaded (Livewire / Volt)
    |--------------------------------------------------------------------------
    */
    Route::redirect('/settings', '/settings/profile');

    Volt::route('/settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('/settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('/settings/appearance', 'settings.appearance')->name('appearance.edit');

    /*
    |--------------------------------------------------------------------------
    | Kuulutuse lisamine (auth)
    |--------------------------------------------------------------------------
    */
    Route::prefix('listings')->group(function () {
        Route::get('/create', [ListingController::class, 'create'])->name('listings.create');
        Route::post('/', [ListingController::class, 'store'])->name('listings.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Minu kuulutused
    |--------------------------------------------------------------------------
    */
    Route::prefix('my-listings')->group(function () {

        Route::get('/', [ListingController::class, 'mine'])->name('listings.mine');

        Route::get('/{listing}', [ListingController::class, 'showMine'])->name('listings.mine.show');

        Route::get('/{listing}/edit', [ListingController::class, 'editMine'])->name('listings.mine.edit');

        Route::patch('/{listing}', [ListingController::class, 'updateMine'])->name('listings.mine.update');

        Route::patch('/{listing}/toggle', [ListingController::class, 'toggleMine'])->name('listings.mine.toggle');

        Route::delete('/{listing}', [ListingController::class, 'destroyMine'])->name('listings.mine.destroy');

        Route::patch('/{listing}/sold', [ListingController::class, 'markSold'])->name('listings.mine.sold');

        Route::patch('/{listing}/unsold', [ListingController::class, 'markUnsold'])->name('listings.mine.unsold');

        Route::patch('/{listing}/publish', [ListingController::class, 'publishMine'])->name('listings.mine.publish');

        // ✅ Parandus: relist loogiliselt my-listings alla
        Route::patch('/{listing}/relist', [ListingController::class, 'relistMine'])->name('listings.mine.relist');
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