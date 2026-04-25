@props(['title' => null, 'container' => true])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    @livewireStyles
</head>

<body class="min-h-screen overflow-x-hidden bg-[#eef3ed] text-zinc-900 antialiased">
    {{-- Üldine toast kogu rakenduse jaoks --}}
    <x-ui.toast />

    {{-- Sticky nav peab olema väljaspool overflow wrapperit --}}
    <x-layouts.app.header :title="$title" />

    <div class="relative flex min-h-screen flex-col">
        {{-- Pehmed taustakihid --}}
        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute -top-32 left-1/2 h-96 w-96 -translate-x-1/2 rounded-full bg-emerald-200/60 blur-3xl"></div>
            <div class="absolute top-96 -left-32 h-80 w-80 rounded-full bg-lime-200/50 blur-3xl"></div>
            <div class="absolute right-0 top-40 h-72 w-72 rounded-full bg-stone-300/60 blur-3xl"></div>
        </div>

        @if($container)
            <main class="flex-1 px-4 py-8 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-[1500px]">
                    {{ $slot }}
                </div>
            </main>
        @else
            <main class="flex-1">
                {{ $slot }}
            </main>
        @endif

        <x-layouts.app.footer />
    </div>

    @livewireScripts

    <script>
    (function () {
        function lockForm(form) {
            if (!form) return;

            form.dataset.submitting = '1';

            const buttons = form.querySelectorAll('button[type="submit"]');
            buttons.forEach((btn) => {
                btn.disabled = true;
                btn.classList.add('opacity-60');
            });

            if (form.__unlockTimer) {
                clearTimeout(form.__unlockTimer);
            }

            form.__unlockTimer = setTimeout(() => {
                unlockForm(form);
            }, 8000);
        }

        function unlockForm(form) {
            if (!form) return;

            form.dataset.submitting = '0';

            const buttons = form.querySelectorAll('button[type="submit"]');
            buttons.forEach((btn) => {
                btn.disabled = false;
                btn.classList.remove('opacity-60');
            });

            if (form.__unlockTimer) {
                clearTimeout(form.__unlockTimer);
                form.__unlockTimer = null;
            }
        }

        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) return;

            if (form.dataset.submitting === '1') {
                e.preventDefault();
                return;
            }

            setTimeout(() => {
                lockForm(form);
            }, 0);
        }, true);

        window.addEventListener('pageshow', function () {
            document.querySelectorAll('form').forEach(unlockForm);
        });
    })();
    </script>
</body>
</html>