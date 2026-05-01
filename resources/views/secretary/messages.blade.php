<x-layouts.secretary :title="__('Messages')" :current-route="'role.messages.index'">
    @include('messages.partials.role-chat', [
        'messageDescription' => 'Communicate with students, parents, faculty, and administrators.',
    ])
</x-layouts.secretary>
