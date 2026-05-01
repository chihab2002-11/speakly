<x-layouts.admin :title="__('Messages')" :current-route="'role.messages.index'">
    @include('messages.partials.role-chat', [
        'messageDescription' => 'Communicate with students, parents, and staff.',
    ])
</x-layouts.admin>
