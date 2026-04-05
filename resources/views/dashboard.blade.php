<x-layouts::app :title="__('Dashboard')">
    @php
        $currentRole = \App\Support\DashboardRedirector::roleFor(auth()->user());
    @endphp

    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <flux:card class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="lg">{{ __('Messages') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Read your inbox or send a message to another user.') }}</flux:text>
            </div>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('role.messages.index', ['role' => $currentRole])" variant="primary" icon="inbox" wire:navigate>
                    {{ __('Open inbox') }}
                </flux:button>
                <flux:button :href="route('role.messages.create', ['role' => $currentRole])" variant="ghost" icon="paper-airplane" wire:navigate>
                    {{ __('New message') }}
                </flux:button>
            </div>
        </flux:card>

        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts::app>
