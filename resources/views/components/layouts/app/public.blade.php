@props(['title' => null, 'container' => true])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')
    @livewireStyles
</head>
<body class="min-h-screen bg-white text-zinc-900">
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

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('notify', ({ message }) => {
                alert(message); // MVP
            });
        });
    </script>

    @livewireScripts
</body>
</html>