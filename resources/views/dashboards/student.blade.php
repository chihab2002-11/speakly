<x-layouts::app :title="__('Student Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <flux:card class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="lg">{{ __('Student Dashboard') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Overview of your conversations and notifications.') }}</flux:text>
            </div>
            <div class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                {{ __('Unread messages: :count', ['count' => $unreadMessagesCount]) }}
            </div>
        </flux:card>

        <div class="grid gap-4 xl:grid-cols-2">
            <x-messages-preview :conversations="$recentConversations" />
            <x-notifications-dropdown :notifications="$latestNotifications" :unread-count="$unreadNotificationsCount" />
        </div>
    </div>
</x-layouts::app>
