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
use App\Http\Controllers\Public\UserProfileController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\Trade\TradeController;
use App\Http\Controllers\Trade\ReviewController;
use App\Http\Controllers\User\PurchaseController;
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

/*
|--------------------------------------------------------------------------
| Avalik kasutajaprofiil
|--------------------------------------------------------------------------
*/
Route::get('/users/{user}', [UserProfileController::class, 'show'])
    ->whereNumber('user')
    ->name('users.show');

Route::view('/terms', 'legal.terms')->name('terms');
Route::view('/privacy', 'legal.privacy')->name('privacy');

/*
|--------------------------------------------------------------------------
| EHNET: kaheastmeline registreerimine (email → vorm)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', function () {
        return view('livewire.auth.register');
    })->name('register');

    Route::post('/register', [EmailRegistrationController::class, 'store'])
        ->name('register.store');

    Route::get('/register/complete/{token}', [EmailRegistrationController::class, 'showCompleteForm'])
        ->name('register.complete');

    Route::post('/register/complete/{token}', [EmailRegistrationController::class, 'complete'])
        ->name('register.complete.post');
});

/*
|--------------------------------------------------------------------------
| Kasutaja dashboard (vajab autentimist)
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth'])
    ->name('dashboard');

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

    Route::get('/settings/delete-account', [ProfileController::class, 'delete'])
        ->name('profile.delete');

    Route::delete('/settings/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

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

        // Ostja avaldab ostusoovi otse kuulutuse lehelt
        Route::post('/{listing}/buy-intent', [TradeController::class, 'expressInterestFromListing'])
            ->whereNumber('listing')
            ->name('listings.buy-intent');
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

    Route::get('/messages/{conversation}/listing', [ConversationController::class, 'showListing'])
        ->name('messages.listing.show');

    Route::post('/messages/{conversation}', [MessageController::class, 'storeInConversation'])
        ->name('messages.store');

    /*
    |--------------------------------------------------------------------------
    | Tehingud (Trade)
    |--------------------------------------------------------------------------
    */
    Route::patch('/messages/{conversation}/interest', [TradeController::class, 'expressInterest'])
        ->name('messages.interest');

    Route::patch('/messages/{conversation}/reserve', [TradeController::class, 'reserve'])
        ->name('messages.reserve');

    Route::patch('/messages/{conversation}/complete', [TradeController::class, 'complete'])
        ->name('messages.complete');

    Route::patch('/messages/{conversation}/trades/confirm-received', [TradeController::class, 'confirmReceived'])
        ->name('messages.trades.confirm');

    Route::patch('/messages/{conversation}/trades/{trade}/cancel', [TradeController::class, 'cancel'])
        ->name('messages.trades.cancel');

    Route::post('/messages/{conversation}/trades/{trade}/reviews', [ReviewController::class, 'store'])
        ->name('messages.trades.reviews.store');

    /*
    |--------------------------------------------------------------------------
    | Vestluse peitmine
    |--------------------------------------------------------------------------
    */
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
    | Minu ostud
    |--------------------------------------------------------------------------
    */
    Route::get('/my/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/my/purchases/{trade}', [PurchaseController::class, 'show'])->name('purchases.show');

    /*
    |--------------------------------------------------------------------------
    | Lemmik kuulutused
    |--------------------------------------------------------------------------
    */
    Route::get('/favorites', [UserListingController::class, 'favorites'])
        ->name('favorites.index');
    });