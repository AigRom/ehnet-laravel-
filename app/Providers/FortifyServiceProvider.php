<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Http\Responses\InvalidPasswordResetResponse;
use App\Http\Responses\LoginResponse;
use App\Http\Responses\NeutralPasswordResetLinkResponse;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\FailedPasswordResetResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);

        $this->app->singleton(
            SuccessfulPasswordResetLinkRequestResponse::class,
            NeutralPasswordResetLinkResponse::class
        );

        $this->app->singleton(
            FailedPasswordResetLinkRequestResponse::class,
            NeutralPasswordResetLinkResponse::class
        );

        $this->app->singleton(
            FailedPasswordResetResponse::class,
            InvalidPasswordResetResponse::class
        );
    }

    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    private function configureViews(): void
    {
        Fortify::loginView(fn () => view('livewire.auth.login'));
        Fortify::verifyEmailView(fn () => view('livewire.auth.verify-email'));
        Fortify::confirmPasswordView(fn () => view('livewire.auth.confirm-password'));
        Fortify::registerView(fn () => view('livewire.auth.register'));

        Fortify::resetPasswordView(function (Request $request) {
            $email = (string) $request->query('email');
            $token = (string) $request->route('token');

            $user = User::where('email', $email)->first();

            if (! $user || ! Password::broker()->tokenExists($user, $token)) {
                return redirect()
                    ->route('password.request')
                    ->with(
                        'error',
                        __('Parooli lähtestamise link on aegunud või kehtetu. Palun küsi uus link.')
                    );
            }

            return view('livewire.auth.reset-password');
        });

        Fortify::requestPasswordResetLinkView(fn () => view('livewire.auth.forgot-password'));
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(
                Str::lower($request->input(Fortify::username())).'|'.$request->ip()
            );

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
