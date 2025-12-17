<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

// Controllers
use App\Http\Controllers\Auth\EmailRegistrationController;
use App\Http\Controllers\ListingController;

/*
|--------------------------------------------------------------------------
| Avalikud lehed
|--------------------------------------------------------------------------
|
| Lehed, mis on kättesaadavad ilma sisselogimiseta.
|
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
|
| Peale sisselogimist kuvatav kasutaja avaleht.
|
*/
Route::view('dashboard', 'dashboard')
    ->middleware(['auth'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| EHNET: Kahe-sammuline registreerimine (email → vorm)
|--------------------------------------------------------------------------
|
| 1. Kasutaja sisestab e-posti ja nõustub tingimustega
| 2. Saadame e-kirjaga kinnituse lingi
| 3. Link viib "päris" registreerimisvormile
|
*/
Route::post('/register', [EmailRegistrationController::class, 'store'])
    ->name('register.store');

Route::get('/register/complete/{token}', [EmailRegistrationController::class, 'showCompleteForm'])
    ->name('register.complete');

Route::post('/register/complete/{token}', [EmailRegistrationController::class, 'complete'])
    ->name('register.complete.post');

/*
|--------------------------------------------------------------------------
| Autenditud kasutaja alad
|--------------------------------------------------------------------------
|
| Kõik allolevad teed on kättesaadavad ainult sisselogitud kasutajale.
|
*/
Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Kasutaja seaded (Livewire / Volt)
    |--------------------------------------------------------------------------
    */
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')
        ->name('profile.edit');

    Volt::route('settings/password', 'settings.password')
        ->name('user-password.edit');

    Volt::route('settings/appearance', 'settings.appearance')
        ->name('appearance.edit');

    /*
    |--------------------------------------------------------------------------
    | Kuulutused (Listingud)
    |--------------------------------------------------------------------------
    |
    | Kuulutuste lisamine (hiljem ka muutmine, kustutamine jne).
    |
    */
    Route::get('/listings/create', [ListingController::class, 'create'])
        ->name('listings.create');

    Route::post('/listings', [ListingController::class, 'store'])
        ->name('listings.store');

    /*
    |--------------------------------------------------------------------------
    | Kahefaktoriline autentimine (valikuline / edasiarendus)
    |--------------------------------------------------------------------------
    */
    Volt::route('settings/two-factor', 'settings.two-factor')
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
