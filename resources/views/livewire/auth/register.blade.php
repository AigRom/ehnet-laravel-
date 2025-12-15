<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Create an account')"
            :description="__('Enter your email below, we will send you a confirmation link to complete your registration.')"
        />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        {{-- Samm 1: ainult e-post + tingimused --}}
        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Tingimustega nõustumine -->
            <flux:checkbox
                name="terms"
                value="1"
                class="mt-2"
            >
                {{ __('I agree to the Terms of Service and Privacy Policy') }}
            </flux:checkbox>

            @error('terms')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Send confirmation link') }}
                </flux:button>
            </div>
        </form>

         



        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts.auth>
