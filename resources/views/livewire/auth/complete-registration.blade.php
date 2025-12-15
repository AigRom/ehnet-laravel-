<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Complete your EHNET account')"
            :description="__('Fill in the required details to finish registration.')"
        />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.complete.post', $token) }}" class="flex flex-col gap-6" id="completeRegForm">
            @csrf

            <!-- Email (read-only) -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="$email"
                type="email"
                disabled
            />

            <!-- Account type -->
            <div class="space-y-2">
                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-200">
                    {{ __('Account type') }}
                </p>

                <div class="flex flex-col gap-2 text-sm text-zinc-700 dark:text-zinc-200">
                    <label class="inline-flex items-center gap-2">
                        <input
                            type="radio"
                            name="type"
                            value="customer"
                            class="h-4 w-4 border-zinc-300 text-zinc-900"
                            {{ old('type', 'customer') === 'customer' ? 'checked' : '' }}
                        >
                        <span>{{ __('Private person') }}</span>
                    </label>

                    <label class="inline-flex items-center gap-2">
                        <input
                            type="radio"
                            name="type"
                            value="business"
                            class="h-4 w-4 border-zinc-300 text-zinc-900"
                            {{ old('type') === 'business' ? 'checked' : '' }}
                        >
                        <span>{{ __('Business account') }}</span>
                    </label>
                </div>

                @error('type')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- PRIVATE PERSON FIELDS -->
            <div id="privateFields" class="flex flex-col gap-6">
                <flux:input
                    name="first_name"
                    :label="__('First name')"
                    :value="old('first_name')"
                    type="text"
                    autocomplete="given-name"
                    :placeholder="__('First name')"
                />
                @error('first_name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

                <flux:input
                    name="last_name"
                    :label="__('Last name')"
                    :value="old('last_name')"
                    type="text"
                    autocomplete="family-name"
                    :placeholder="__('Last name')"
                />
                @error('last_name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

                <flux:input
                    name="date_of_birth"
                    :label="__('Date of birth (optional)')"
                    :value="old('date_of_birth')"
                    type="date"
                />
                @error('date_of_birth') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- BUSINESS FIELDS -->
            <div id="businessFields" class="flex flex-col gap-6">
                <flux:input
                    name="contact_first_name"
                    :label="__('Contact first name')"
                    :value="old('contact_first_name')"
                    type="text"
                    autocomplete="given-name"
                    :placeholder="__('Contact first name')"
                />
                @error('contact_first_name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

                <flux:input
                    name="contact_last_name"
                    :label="__('Contact last name')"
                    :value="old('contact_last_name')"
                    type="text"
                    autocomplete="family-name"
                    :placeholder="__('Contact last name')"
                />
                @error('contact_last_name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

                <flux:input
                    name="company_name"
                    :label="__('Company name')"
                    :value="old('company_name')"
                    type="text"
                    :placeholder="__('Company name')"
                />
                @error('company_name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

                <flux:input
                    name="company_reg_no"
                    :label="__('Company registration number')"
                    :value="old('company_reg_no')"
                    type="text"
                    :placeholder="__('Registration number')"
                />
                @error('company_reg_no') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- COMMON FIELDS -->
            <flux:input
                name="phone"
                :label="__('Phone number')"
                :value="old('phone')"
                type="tel"
                required
                autocomplete="tel"
                :placeholder="__('Phone number')"
            />
            @error('phone') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

            <flux:input
                name="region"
                :label="__('County / Region')"
                :value="old('region')"
                type="text"
                required
                :placeholder="__('County / Region')"
            />
            @error('region') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

            <flux:input
                name="city"
                :label="__('City')"
                :value="old('city')"
                type="text"
                required
                :placeholder="__('City')"
            />
            @error('city') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                viewable
            />
            @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror

            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                viewable
            />

            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Create account') }}
            </flux:button>
        </form>

        <script>
            (function () {
                const privateFields = document.getElementById('privateFields');
                const businessFields = document.getElementById('businessFields');
                const radios = document.querySelectorAll('input[name="type"]');

                function applyVisibility() {
                    const selected = document.querySelector('input[name="type"]:checked')?.value || 'customer';
                    const isBusiness = selected === 'business';

                    businessFields.style.display = isBusiness ? '' : 'none';
                    privateFields.style.display = isBusiness ? 'none' : '';

                    businessFields.querySelectorAll('input, select, textarea').forEach(el => el.disabled = !isBusiness);
                    privateFields.querySelectorAll('input, select, textarea').forEach(el => el.disabled = isBusiness);
                }

                radios.forEach(r => r.addEventListener('change', applyVisibility));
                applyVisibility();
            })();
        </script>
    </div>
</x-layouts.auth>
