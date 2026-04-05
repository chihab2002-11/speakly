@props([
    'conversations' => collect(),
    'title' => __('Recent Conversations'),
])

@php
    $currentRole = \App\Support\DashboardRedirector::roleFor(auth()->user());
@endphp

<flux:card>
    <div class="mb-4 flex items-center justify-between">
        <flux:heading size="md">{{ $title }}</flux:heading>
        <flux:button :href="route('role.messages.index', ['role' => $currentRole])" variant="ghost" size="sm" wire:navigate>
            {{ __('Open messages') }}
        </flux:button>
    </div>

    @if (collect($conversations)->isEmpty())
        <flux:text class="text-zinc-500">{{ __('No conversations yet.') }}</flux:text>
    @else
        <div class="space-y-3">
            @foreach ($conversations as $conversation)
                @php
                    $partner = $conversation['user'];
                    $lastMessage = $conversation['lastMessage'];
                    $unreadCount = $conversation['unreadCount'];
                @endphp

                <a
                    href="{{ route('role.messages.conversation', ['role' => $currentRole, 'conversation' => $partner->id]) }}"
                    class="flex items-center justify-between rounded-lg border border-zinc-200 p-3 transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800"
                >
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-zinc-900 dark:text-white">{{ $partner->name }}</p>
                        <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                            {{ $lastMessage?->body ?? __('No messages yet.') }}
                        </p>
                    </div>

                    <div class="ml-3 flex flex-col items-end gap-1">
                        @if ($lastMessage)
                            <span class="text-[11px] text-zinc-400 dark:text-zinc-500">
                                {{ $lastMessage->created_at->diffForHumans() }}
                            </span>
                        @endif
                        @if ($unreadCount > 0)
                            <span class="inline-flex min-w-5 justify-center rounded-full bg-blue-500 px-1.5 py-0.5 text-[11px] font-semibold text-white">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</flux:card>
