<x-layouts.teacher :title="__('Messages')" :currentRoute="'messages'">
    @include('messages.partials.role-chat', [
        'messageDescription' => 'Communicate with students, parents, and school administration.',
    ])
</x-layouts.teacher>
