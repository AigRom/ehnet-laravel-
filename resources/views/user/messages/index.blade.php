<x-layouts.app.public :title="__('Sõnumid')">
    <x-messaging.message-center
        :conversations="$conversations"
        :active-conversation="$activeConversation"
    />
</x-layouts.app.public>