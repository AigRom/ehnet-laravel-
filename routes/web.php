<?php

use App\Http\Controllers\Auth\EmailRegistrationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Messaging\ConversationController;
use App\Http\Controllers\Messaging\MessageAttachmentController;
use App\Http\Controllers\Messaging\MessageController;
use App\Http\Controllers\Messaging\UserBlockController;
use App\Http\Controllers\Messaging\UserReportController;
use App\Http\Controllers\Public\ListingController as PublicListingController;
use App\Http\Controllers\Public\UserProfileController;
use App\Http\Controllers\Trade\ReviewController;
use App\Http\Controllers\Trade\TradeController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\ListingController as UserListingController;
use App\Http\Controllers\User\PasswordController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\PurchaseController;
use App\Http\Controllers\User\ReviewController as UserReviewController;
use App\Http\Controllers\Support\SupportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('listings')->group(function () {
    Route::get('/', [PublicListingController::class, 'index'])
        ->name('listings.index');

    Route::get('/{listing}', [PublicListingController::class, 'show'])
        ->whereNumber('listing')
        ->name('listings.show');
});

Route::get('/users/{user}', [UserProfileController::class, 'show'])
    ->whereNumber('user')
    ->name('users.show');

Route::view('/terms', 'legal.terms')->name('terms');
Route::view('/privacy', 'legal.privacy')->name('privacy');

Route::post('/support', [SupportController::class, 'store'])
    ->middleware('throttle:support')
    ->name('support.store');

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

Route::get('/dashboard', DashboardController::class)
    ->middleware('auth')
    ->name('dashboard');

Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()
        ->route('login')
        ->with('status', 'Oled edukalt välja logitud.');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
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

    Route::prefix('listings')->group(function () {
        Route::get('/create', [UserListingController::class, 'create'])
            ->name('listings.create');

        Route::post('/', [UserListingController::class, 'store'])
            ->name('listings.store');

        Route::post('/{listing}/open-conversation', [ConversationController::class, 'openFromListing'])
            ->whereNumber('listing')
            ->name('listings.conversation.open');

        Route::post('/{listing}/buy-intent', [TradeController::class, 'expressInterestFromListing'])
            ->whereNumber('listing')
            ->name('listings.buy-intent');
    });

    Route::get('/messages', [ConversationController::class, 'index'])
        ->name('messages.index');

    Route::get('/messages/unread-count', [ConversationController::class, 'unreadCount'])
        ->name('messages.unread-count');

    Route::get('/messages/{conversation}', [ConversationController::class, 'show'])
        ->whereNumber('conversation')
        ->name('messages.show');

    Route::get('/messages/{conversation}/listing', [ConversationController::class, 'showListing'])
        ->whereNumber('conversation')
        ->name('messages.listing.show');

    Route::get('/messages/{conversation}/poll', [ConversationController::class, 'poll'])
        ->whereNumber('conversation')
        ->name('messages.poll');

    Route::patch('/messages/{conversation}/mark-read', [ConversationController::class, 'markRead'])
        ->whereNumber('conversation')
        ->name('messages.mark-read');

    Route::post('/messages/{conversation}', [MessageController::class, 'storeInConversation'])
        ->whereNumber('conversation')
        ->name('messages.store');

    Route::get('/messages/attachments/{attachment}/download', [MessageAttachmentController::class, 'download'])
        ->whereNumber('attachment')
        ->name('messages.attachments.download');

    Route::patch('/messages/{conversation}/interest', [TradeController::class, 'expressInterest'])
        ->whereNumber('conversation')
        ->name('messages.interest');

    Route::patch('/messages/{conversation}/reserve', [TradeController::class, 'reserve'])
        ->whereNumber('conversation')
        ->name('messages.reserve');

    Route::patch('/messages/{conversation}/complete', [TradeController::class, 'complete'])
        ->whereNumber('conversation')
        ->name('messages.complete');

    Route::patch('/messages/{conversation}/trades/confirm-received', [TradeController::class, 'confirmReceived'])
        ->whereNumber('conversation')
        ->name('messages.trades.confirm');

    Route::patch('/messages/{conversation}/trades/{trade}/cancel', [TradeController::class, 'cancel'])
        ->whereNumber('conversation')
        ->whereNumber('trade')
        ->name('messages.trades.cancel');

    Route::post('/messages/{conversation}/trades/{trade}/reviews', [ReviewController::class, 'store'])
        ->whereNumber('conversation')
        ->whereNumber('trade')
        ->name('messages.trades.reviews.store');

    Route::delete('/messages/{conversation}', [ConversationController::class, 'destroy'])
        ->whereNumber('conversation')
        ->name('messages.destroy');

    Route::post('/user-blocks/{user}', [UserBlockController::class, 'store'])
        ->whereNumber('user')
        ->name('user-blocks.store');

    Route::delete('/user-blocks/{user}', [UserBlockController::class, 'destroy'])
        ->whereNumber('user')
        ->name('user-blocks.destroy');

    Route::post('/user-reports', [UserReportController::class, 'store'])
        ->name('user-reports.store');

    Route::prefix('my-listings')->group(function () {
        Route::get('/', [UserListingController::class, 'mine'])
            ->name('listings.mine');

        Route::get('/{listing}', [UserListingController::class, 'showMine'])
            ->whereNumber('listing')
            ->name('listings.mine.show');

        Route::get('/{listing}/edit', [UserListingController::class, 'editMine'])
            ->whereNumber('listing')
            ->name('listings.mine.edit');

        Route::patch('/{listing}', [UserListingController::class, 'updateMine'])
            ->whereNumber('listing')
            ->name('listings.mine.update');

        Route::patch('/{listing}/toggle', [UserListingController::class, 'toggleMine'])
            ->whereNumber('listing')
            ->name('listings.mine.toggle');

        Route::delete('/{listing}', [UserListingController::class, 'destroyMine'])
            ->whereNumber('listing')
            ->name('listings.mine.destroy');

        Route::patch('/{listing}/sold', [UserListingController::class, 'markSold'])
            ->whereNumber('listing')
            ->name('listings.mine.sold');

        Route::patch('/{listing}/unsold', [UserListingController::class, 'markUnsold'])
            ->whereNumber('listing')
            ->name('listings.mine.unsold');

        Route::patch('/{listing}/publish', [UserListingController::class, 'publishMine'])
            ->whereNumber('listing')
            ->name('listings.mine.publish');

        Route::patch('/{listing}/relist', [UserListingController::class, 'relistMine'])
            ->whereNumber('listing')
            ->name('listings.mine.relist');
    });

    Route::get('/my/purchases', [PurchaseController::class, 'index'])
        ->name('purchases.index');

    Route::get('/my/purchases/{trade}', [PurchaseController::class, 'show'])
        ->whereNumber('trade')
        ->name('purchases.show');

    Route::patch('/my/purchases/{trade}/hide', [PurchaseController::class, 'hide'])
        ->whereNumber('trade')
        ->name('purchases.hide');

    Route::get('/favorites', [UserListingController::class, 'favorites'])
        ->name('favorites.index');

    Route::get('/my/reviews', [UserReviewController::class, 'received'])
        ->name('reviews.received');
});