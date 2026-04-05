<x-layouts::app :title="__('Secretary Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <flux:card class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="lg">{{ __('Secretary Dashboard') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Overview of your conversations and notifications.') }}</flux:text>
            </div>
            <div class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-sm font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                {{ __('Unread messages: :count', ['count' => $unreadMessagesCount]) }}
            </div>
        </flux:card>

        <div class="grid gap-4 xl:grid-cols-2">
            <x-messages-preview :conversations="$recentConversations" />
            <x-notifications-dropdown :notifications="$latestNotifications" :unread-count="$unreadNotificationsCount" />
        </div>
    </div>
</x-layouts::app>
