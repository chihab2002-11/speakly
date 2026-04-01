@php
    $tabs = [
        'inbox' => ['route' => 'messages.inbox', 'label' => __('Inbox')],
        'sent' => ['route' => 'messages.sent', 'label' => __('Sent')],
        'create' => ['route' => 'messages.create', 'label' => __('New message')],
    ];
@endphp

<nav
    class="flex flex-wrap gap-1 rounded-xl border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-900/60"
    aria-label="{{ __('Messages navigation') }}"
>
    @foreach ($tabs as $key => $tab)
        @php
            $isActive = $active === $key;
        @endphp
        <a
            href="{{ route($tab['route']) }}"
            wire:navigate
            @class([
                'inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium transition',
                'bg-white text-zinc-900 shadow-sm dark:bg-zinc-800 dark:text-white' => $isActive,
                'text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white' => ! $isActive,
            ])
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</nav>
