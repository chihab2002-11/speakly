<x-layouts.parent
    :title="'Messages'"
    :pageTitle="'Messages'"
    :currentRoute="'messages'"
    :user="$user ?? null"
    :children="$children ?? []"
>
    @include('messages.partials.role-chat', [
        'messageDescription' => "Communicate with your children's teachers and school administration.",
    ])
</x-layouts.parent>
