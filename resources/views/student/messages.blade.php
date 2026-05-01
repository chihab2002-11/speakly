<x-dynamic-component
    :component="$layoutComponent ?? 'layouts.student'"
    :title="__('Messages')"
    :currentRoute="$currentRoute ?? 'messages'"
    :pageTitle="'Child Messages'"
    :user="$user ?? null"
    :portalParent="$portalParent ?? null"
    :portalChildren="$portalChildren ?? []"
    :portalSelectedChild="$portalSelectedChild ?? null"
>
    @php
        $currentRole = \App\Support\DashboardRedirector::roleFor(auth()->user());
        $messageActor = $messageActor ?? auth()->user();
    @endphp

    @include('messages.partials.role-chat', [
        'currentRole' => $currentRole,
        'messageActor' => $messageActor,
        'messageIndexRouteName' => $messageIndexRouteName ?? 'role.messages.index',
        'messageIndexRouteParams' => $messageIndexRouteParams ?? ['role' => $currentRole],
        'messageConversationRouteName' => $messageConversationRouteName ?? 'role.messages.conversation',
        'messageConversationRouteParams' => $messageConversationRouteParams ?? ['role' => $currentRole],
        'messageStoreRouteName' => $messageStoreRouteName ?? 'role.messages.store',
        'messageStoreRouteParams' => $messageStoreRouteParams ?? ['role' => $currentRole],
        'messageDescription' => 'Communicate with your teachers and school administration.',
        'showNewConversation' => ! isset($portalParent),
    ])
</x-dynamic-component>
