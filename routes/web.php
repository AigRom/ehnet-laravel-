<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

// Kontrollerid
use App\Http\Controllers\Auth\EmailRegistrationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListingQuickController;
use App\Http\Controllers\Messaging\MessageController;
use App\Http\Controllers\Messaging\ConversationController;
use App\Http\Controllers\Public\ListingController as PublicListingController;
use App\Http\Controllers\User\ListingController as UserListingController;

/*
|--------------------------------------------------------------------------
| Avalikud lehed
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('listings')->group(function () {
    // kõik kuulutused (public)
    Route::get('/', [PublicListingController::class, 'index'])->name('listings.index');

    // detail (ainult number -> ei söö /listings/create ära)
    Route::get('/{listing}', [PublicListingController::class, 'show'])
        ->whereNumber('listing')
        ->name('listings.show');
});

Route::view('/terms', 'legal.terms')->name('terms');
Route::view('/privacy', 'legal.privacy')->name('privacy');

/*
|--------------------------------------------------------------------------
| Kasutaja dashboard (vajab autentimist)
|--------------------------------------------------------------------------
*/
Route::view('/dashboard', 'user.dashboard')
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

    /*
    |--------------------------------------------------------------------------
    | Kuulutuse lisamine (auth)
    |--------------------------------------------------------------------------
    */
    Route::prefix('listings')->group(function () {
        Route::get('/create', [UserListingController::class, 'create'])->name('listings.create');
        Route::post('/', [UserListingController::class, 'store'])->name('listings.store');

        // Avab olemasoleva vestluse või loob uue kuulutuse detailvaatest
        Route::post('/{listing}/open-conversation', [ConversationController::class, 'openFromListing'])
            ->whereNumber('listing')
            ->name('listings.conversation.open');
    });

    /*
    |--------------------------------------------------------------------------
    | Sõnumid / vestlused
    |--------------------------------------------------------------------------
    */
    Route::get('/messages', [ConversationController::class, 'index'])
        ->name('messages.index');

    Route::get('/messages/{conversation}', [ConversationController::class, 'show'])
        ->name('messages.show');

    Route::post('/messages/{conversation}', [MessageController::class, 'storeInConversation'])
        ->name('messages.store');

    // Peidab vestluse ainult текущise kasutaja vaatest
    Route::delete('/messages/{conversation}', [ConversationController::class, 'destroy'])
        ->name('messages.destroy');

    /*
    |--------------------------------------------------------------------------
    | Minu kuulutused
    |--------------------------------------------------------------------------
    */
    Route::prefix('my-listings')->group(function () {
        Route::get('/', [UserListingController::class, 'mine'])->name('listings.mine');

        Route::get('/{listing}', [UserListingController::class, 'showMine'])->name('listings.mine.show');

        Route::get('/{listing}/edit', [UserListingController::class, 'editMine'])->name('listings.mine.edit');

        Route::patch('/{listing}', [UserListingController::class, 'updateMine'])->name('listings.mine.update');

        Route::patch('/{listing}/toggle', [UserListingController::class, 'toggleMine'])->name('listings.mine.toggle');

        Route::delete('/{listing}', [UserListingController::class, 'destroyMine'])->name('listings.mine.destroy');

        Route::patch('/{listing}/sold', [UserListingController::class, 'markSold'])->name('listings.mine.sold');

        Route::patch('/{listing}/unsold', [UserListingController::class, 'markUnsold'])->name('listings.mine.unsold');

        Route::patch('/{listing}/publish', [UserListingController::class, 'publishMine'])->name('listings.mine.publish');

        // relist loogiliselt my-listings alla
        Route::patch('/{listing}/relist', [UserListingController::class, 'relistMine'])->name('listings.mine.relist');
    });

    /*
    |--------------------------------------------------------------------------
    | Lemmik kuulutused
    |--------------------------------------------------------------------------
    */
    Route::get('/favorites', [UserListingController::class, 'favorites'])
        ->name('favorites.index');

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