@props([
    'notifications' => collect(),
    'unreadCount' => 0,
    'title' => __('Notifications'),
])

<flux:card>
    <div class="mb-4 flex items-center justify-between">
        <flux:heading size="md">{{ $title }}</flux:heading>
        @if ($unreadCount > 0)
            <span class="inline-flex min-w-5 justify-center rounded-full bg-red-500 px-2 py-0.5 text-xs font-semibold text-white">
                {{ $unreadCount }}
            </span>
        @endif
    </div>

    @if (collect($notifications)->isEmpty())
        <flux:text class="text-zinc-500">{{ __('No notifications yet.') }}</flux:text>
    @else
        <div class="space-y-3">
            @foreach ($notifications as $notification)
                @php
                    $data = (array) $notification->data;
                    $notificationTitle = $data['title'] ?? $data['type'] ?? __('Notification');
                    $notificationMessage = $data['message'] ?? $data['body'] ?? $data['text'] ?? '';
                    $notificationUrl = $data['url'] ?? $data['action_url'] ?? null;
                @endphp
                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                        {{ $notificationTitle }}
                    </p>
                    <p class="mt-1 text-xs text-zinc-600 dark:text-zinc-400">
                        {{ $notificationMessage }}
                    </p>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-[11px] text-zinc-400 dark:text-zinc-500">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                        @if (! empty($notificationUrl))
                            <a href="{{ $notificationUrl }}" class="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400">
                                {{ __('Open') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-4">
        <flux:button :href="route('notifications.index')" variant="ghost" size="sm" wire:navigate>
            {{ __('View all notifications') }}
        </flux:button>
    </div>
</flux:card>
