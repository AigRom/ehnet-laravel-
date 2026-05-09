@props(['title' => null, 'container' => true])

<x-layouts.app.public :title="$title" :container="$container">
    {{ $slot }}
</x-layouts.app.public>