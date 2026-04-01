<x-layouts::app :title="__('Message')">
    <div class="mx-auto max-w-3xl space-y-6 p-4 sm:p-6">
        @include('messages.partials.nav', ['active' => ''])

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <flux:heading size="xl">{{ __('Message') }}</flux:heading>
            <div class="flex flex-wrap gap-2">
                <flux:button :href="route('messages.inbox')" variant="ghost" size="sm" wire:navigate>
                    {{ __('Inbox') }}
                </flux:button>
                <flux:button :href="route('messages.sent')" variant="ghost" size="sm" wire:navigate>
                    {{ __('Sent') }}
                </flux:button>
            </div>
        </div>

        <article
            class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
        >
            <div class="border-b border-zinc-100 px-6 py-4 dark:border-zinc-800">
                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('From') }}</dt>
                        <dd class="mt-0.5 text-zinc-900 dark:text-white">
                            {{ $message->sender->name }}
                            <span class="block text-xs font-normal text-zinc-500 dark:text-zinc-400">
                                {{ $message->sender->email }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('To') }}</dt>
                        <dd class="mt-0.5 text-zinc-900 dark:text-white">
                            {{ $message->receiver->name }}
                            <span class="block text-xs font-normal text-zinc-500 dark:text-zinc-400">
                                {{ $message->receiver->email }}
                            </span>
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</dt>
                        <dd class="mt-0.5 text-zinc-800 dark:text-zinc-200">
                            <time datetime="{{ $message->created_at->toIso8601String() }}">
                                {{ $message->created_at->format('l, F j, Y \a\t H:i') }}
                            </time>
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="font-medium text-zinc-500 dark:text-zinc-400">{{ __('Subject') }}</dt>
                        <dd class="mt-0.5 font-medium text-zinc-900 dark:text-white">
                            {{ $message->subject ?: __('(No subject)') }}
                        </dd>
                    </div>
                </dl>
            </div>
            <div class="px-6 py-5">
                <div class="prose prose-zinc max-w-none dark:prose-invert">
                    <p class="whitespace-pre-wrap text-zinc-800 dark:text-zinc-200">{{ $message->body }}</p>
                </div>
            </div>
        </article>

        @if (auth()->id() === $message->receiver_id && is_null($message->read_at))
            <form action="{{ route('messages.read', $message) }}" method="POST" class="flex">
                @csrf
                @method('PATCH')
                <flux:button type="submit" variant="primary">
                    {{ __('Mark as read') }}
                </flux:button>
            </form>
        @endif
    </div>
</x-layouts::app>
