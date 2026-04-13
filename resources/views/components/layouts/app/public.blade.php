@props(['title' => null, 'container' => true])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    @livewireStyles
</head>
<body class="min-h-screen bg-white text-zinc-900">
    {{-- Üldine toast kogu rakenduse jaoks --}}
    <x-ui.toast />

    <x-layouts.app.header :title="$title" />

    @if($container)
        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            {{ $slot }}
        </main>
    @else
        <main>
            {{ $slot }}
        </main>
    @endif

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

            // Kui jääd samale lehele, vabasta lukk uuesti
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

            // Lase submitil kõigepealt käivituda, lukusta kohe järgmises tickis
            setTimeout(() => {
                lockForm(form);
            }, 0);
        }, true);

        // Kui leht tuleb tagasi browseri cache’ist
        window.addEventListener('pageshow', function () {
            document.querySelectorAll('form').forEach(unlockForm);
        });
    })();
    </script>

    </body>
    </html>