<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;
use App\Http\Controllers\Auth\EmailRegistrationController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth'])
    ->name('dashboard');

// EHNET: kahe-sammuline registreerimine
Route::post('/register', [EmailRegistrationController::class, 'store'])
    ->name('register.store');

Route::get('/register/complete/{token}', [EmailRegistrationController::class, 'showCompleteForm'])
    ->name('register.complete');

Route::post('/register/complete/{token}', [EmailRegistrationController::class, 'complete'])
    ->name('register.complete.post');

// Sisse logitud kasutaja seaded
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
