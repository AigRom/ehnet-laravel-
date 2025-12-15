<x-layouts.app.sidebar :title="$title ?? 'EHNET'">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
