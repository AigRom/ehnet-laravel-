{{-- resources/views/components/layouts/app.blade.php (või components/layouts/app/app.blade.php sõltuvalt failist) --}}
@props(['title' => null, 'container' => true])

<x-layouts.app.public :title="$title" :container="$container">
    {{ $slot }}
</x-layouts.app.public>