<x-layouts::app :title="__('Sent')">
    <div class="mx-auto max-w-4xl space-y-6 p-4 sm:p-6">
        @include('messages.partials.nav', ['active' => 'sent'])

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Sent') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Messages you have sent.') }}</flux:text>
            </div>
            <flux:button :href="route('messages.create')" variant="primary" wire:navigate>
                {{ __('Compose') }}
            </flux:button>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle" :heading="session('success')" />
        @endif

        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            @forelse ($messages as $message)
                <a
                    href="{{ route('messages.show', $message) }}"
                    wire:navigate
                    class="block border-b border-zinc-100 p-4 transition last:border-b-0 hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800/50"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">
                                {{ __('To: :name', ['name' => $message->receiver->name ?? __('Unknown')]) }}
                            </p>
                            <p class="mt-1 truncate text-sm text-zinc-800 dark:text-zinc-200">
                                {{ $message->subject ?: __('(No subject)') }}
                            </p>
                            <p class="mt-0.5 line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ \Illuminate\Support\Str::limit($message->body, 120) }}
                            </p>
                        </div>
                        <time
                            class="shrink-0 text-xs text-zinc-400 dark:text-zinc-500"
                            datetime="{{ $message->created_at->toIso8601String() }}"
                        >
                            {{ $message->created_at->format('M j, H:i') }}
                        </time>
                    </div>
                </a>
            @empty
                <div class="p-10 text-center">
                    <flux:text>{{ __('You have not sent any messages yet.') }}</flux:text>
                    <flux:button :href="route('messages.create')" class="mt-4" variant="primary" wire:navigate>
                        {{ __('Compose') }}
                    </flux:button>
                </div>
            @endforelse
        </div>

        @if ($messages->hasPages())
            <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
                {{ $messages->links() }}
            </div>
        @endif
    </div>
</x-layouts::app>
