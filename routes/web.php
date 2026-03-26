<?php

use Illuminate\Support\Facades\Route;

// Kontrollerid
use App\Http\Controllers\Auth\EmailRegistrationController;
use App\Http\Controllers\HomeController;

use App\Http\Controllers\Messaging\ConversationController;
use App\Http\Controllers\Messaging\MessageController;
use App\Http\Controllers\Messaging\UserBlockController;
use App\Http\Controllers\Messaging\UserReportController;
use App\Http\Controllers\User\PasswordController;
use App\Http\Controllers\User\ProfileController;
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
    | Kasutaja seaded
    |--------------------------------------------------------------------------
    */
    Route::redirect('/settings', '/settings/profile');

    Route::get('/settings/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/settings/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::get('/settings/password', [PasswordController::class, 'edit'])
        ->name('user-password.edit');

    Route::put('/settings/password', [PasswordController::class, 'update'])
        ->name('user-password.update');

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

    // Peidab vestluse ainult kasutaja vaatest
    Route::delete('/messages/{conversation}', [ConversationController::class, 'destroy'])
        ->name('messages.destroy');

    /*
    |--------------------------------------------------------------------------
    | Kasutajate blokeerimine
    |--------------------------------------------------------------------------
    */
    Route::post('/user-blocks/{user}', [UserBlockController::class, 'store'])
        ->name('user-blocks.store');

    Route::delete('/user-blocks/{user}', [UserBlockController::class, 'destroy'])
        ->name('user-blocks.destroy');

    /*
    |--------------------------------------------------------------------------
    | Kasutajast teatamine
    |--------------------------------------------------------------------------
    */
    Route::post('/user-reports', [UserReportController::class, 'store'])
        ->name('user-reports.store');

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
});