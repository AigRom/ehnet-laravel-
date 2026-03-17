<x-layouts.app.public :title="__('Vestlus')">
    <x-messaging.message-center
        :conversations="$conversations"
        :active-conversation="$conversation"
    />
</x-layouts.app.public>