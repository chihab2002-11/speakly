<x-layouts::app :title="__('Messages')">
    <div class="flex h-screen flex-col overflow-hidden bg-zinc-50 dark:bg-zinc-950 sm:p-4">
        <div class="flex flex-1 gap-4 overflow-hidden rounded-xl bg-white dark:bg-zinc-900">
            <!-- Conversations List (Left Sidebar) -->
            <div
                @class([
                    'w-full flex-col border-r border-zinc-200 dark:border-zinc-800 sm:w-80',
                    // On mobile: hide list when a conversation is selected. On desktop: always show.
                    'hidden sm:flex' => $selectedUser,
                    'flex' => !$selectedUser,
                ])>

                <!-- Header with Search -->
                <div class="border-b border-zinc-200 p-4 dark:border-zinc-800">
                    <div class="mb-4">
                        <flux:heading size="lg">{{ __('Messages') }}</flux:heading>
                    </div>

                    <!-- Search -->
                    <form method="GET" action="{{ route('messages.index') }}" class="space-y-2">
                        @if (request('user_id'))
                            <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                        @endif

                        <div class="relative">
                            <flux:input
                                type="search"
                                name="search"
                                :value="$search"
                                placeholder="{{ __('Search conversations...') }}"
                                class="pr-10"
                            />
                            <button type="submit" class="absolute right-2 top-2">
                                <svg class="h-5 w-5 text-zinc-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Conversations List -->
                <div class="flex-1 overflow-y-auto">
                    @forelse ($conversations as $conv)
                        <a href="{{ route('messages.index', ['user_id' => $conv['user']->id, 'search' => $search]) }}"
                            @class([
                                'flex items-start gap-3 border-b border-zinc-100 p-3 transition hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800',
                                'bg-blue-50 dark:bg-blue-900/20' =>
                                    $selectedUser && $selectedUser->id === $conv['user']->id,
                            ])>
                            <!-- Avatar -->
                            <div class="mt-1 flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-400 to-blue-600">
                                <span class="text-sm font-bold text-white">
                                    {{ substr($conv['user']->name, 0, 1) }}
                                </span>
                            </div>

                            <!-- User Info & Last Message -->
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <p @class([
                                        'text-sm font-semibold truncate',
                                        'text-zinc-900 dark:text-white' => $conv['unreadCount'] > 0,
                                        'text-zinc-700 dark:text-zinc-300' => $conv['unreadCount'] === 0,
                                    ])>
                                        {{ $conv['user']->name }}
                                    </p>
                                    @if ($conv['unreadCount'] > 0)
                                        <span
                                            class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-blue-500 text-xs font-bold text-white">
                                            {{ $conv['unreadCount'] }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Last message preview -->
                                @if ($conv['lastMessage'])
                                    <p class="mt-1 truncate text-xs text-zinc-500 dark:text-zinc-400">
                                        @if ($conv['lastMessage']->sender_id === auth()->id())
                                            {{ __('You:') }}
                                        @endif
                                        {{ $conv['lastMessage']->body }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">
                                        {{ $conv['lastMessage']->created_at->format('M j, H:i') }}
                                    </p>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center">
                            <flux:text class="text-zinc-500">
                                @if ($search)
                                    {{ __('No conversations match your search.') }}
                                @else
                                    {{ __('No conversations yet. Send a message to start.') }}
                                @endif
                            </flux:text>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Conversation Thread (Right Side) -->
            <div
                @class([
                    'flex-1 flex-col overflow-hidden',
                    // On mobile: show only when a conversation is selected. On desktop: always show.
                    'hidden sm:flex' => !$selectedUser,
                    'flex' => $selectedUser,
                ])>

                @if ($selectedUser)
                    <!-- Conversation Header -->
                    <div class="border-b border-zinc-200 p-4 dark:border-zinc-800">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <!-- Back button for mobile -->
                                <a href="{{ route('messages.index') }}"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 sm:hidden">
                                    <svg class="h-5 w-5 text-zinc-600 dark:text-zinc-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </a>
                                <div>
                                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                                        {{ $selectedUser->name }}
                                    </h2>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ $selectedUser->email }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Thread -->
                    <div class="flex-1 space-y-3 overflow-y-auto p-4">
                        @forelse ($selectedConversation as $message)
                            <div @class([
                                'flex gap-2',
                                'flex-row-reverse' => auth()->id() === $message->sender_id,
                            ])>
                                <!-- Avatar -->
                                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-xs font-bold text-white"
                                    @class([
                                        'bg-gradient-to-br from-blue-400 to-blue-600' =>
                                            auth()->id() === $message->sender_id,
                                        'bg-gradient-to-br from-gray-400 to-gray-600' =>
                                            auth()->id() !== $message->sender_id,
                                    ])>
                                    {{ substr($message->sender->name, 0, 1) }}
                                </div>

                                <!-- Message Bubble -->
                                <div @class([
                                    'max-w-xs rounded-lg p-3',
                                    'bg-blue-500 text-white' => auth()->id() === $message->sender_id,
                                    'bg-zinc-100 dark:bg-zinc-800' => auth()->id() !==
                                        $message->sender_id,
                                ])>
                                    @if ($message->subject)
                                        <p class="mb-1 text-xs font-semibold opacity-75">
                                            {{ __('Subject:') }} {{ $message->subject }}
                                        </p>
                                    @endif
                                    <p class="whitespace-pre-wrap text-sm">
                                        {{ $message->body }}
                                    </p>
                                    <p class="mt-1 text-xs opacity-75">
                                        {{ $message->created_at->format('H:i') }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center">
                                <flux:text class="text-zinc-500">
                                    {{ __('No messages yet. Start the conversation!') }}
                                </flux:text>
                            </div>
                        @endforelse
                    </div>

                    <!-- Reply Form -->
                    <div class="border-t border-zinc-200 p-4 dark:border-zinc-800">
                        <form action="{{ route('messages.store') }}" method="POST" class="space-y-3">
                            @csrf

                            <input type="hidden" name="receiver_id" value="{{ $selectedUser->id }}">

                            <!-- Subject (optional) -->
                            <div>
                                <flux:input
                                    type="text"
                                    name="subject"
                                    placeholder="{{ __('Subject (optional)') }}"
                                    value="{{ old('subject') }}"
                                />
                                @error('subject')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Message body -->
                            <div>
                                <div class="flex gap-2">
                                    <flux:textarea
                                        name="body"
                                        rows="3"
                                        placeholder="{{ __('Type a message...') }}"
                                        required
                                        class="resize-none"
                                    >{{ old('body') }}</flux:textarea>
                                    <button type="submit"
                                        class="flex items-end rounded-lg bg-blue-500 px-4 py-3 text-white transition hover:bg-blue-600">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M16.6915026,12.4744748 L3.50612381,13.2599618 C3.19218622,13.2599618 3.03521743,13.4170592 3.03521743,13.5741566 L1.15159189,18.1879699 C0.8376543,18.5598582 0.99,19.1272231 0.99,19.1272231 C0.99,20.1165713 1.77946707,20.8460602 2.77946707,20.8460602 C3.50612381,20.8460602 4.13399899,20.4741718 4.13399899,19.97788 L5.51261453,14.6818231 L13.4147147,14.6818231 L14.6563168,19.97788 C14.6563168,20.4741718 15.2052657,20.8460602 15.6563168,20.8460602 C16.6626077,20.8460602 17.1624581,20.0591402 17.1624581,19.1272231 L15.2788226,13.5741566 C15.2788226,13.4170592 15.1272231,13.2599618 14.8129045,13.2599618 L15.6563168,3.89208144 C15.656318,3.33007734 15.1624581,2.6562046 14.6563168,2.6562046 L2.40612381,2.6562046 C1.89989449,2.6562046 1.4747349,3.33007734 1.4747349,3.89208144 L2.31815722,12.4744748 C2.31815722,12.6315722 2.1624581,12.7886697 2.00548931,12.7886697 L0.3847278,12.7886697 C-0.1212625,12.7886697 -0.1212625,13.2599618 0.3847278,13.2599618 L2.15159189,13.2599618 C2.31815722,13.2599618 2.5748,13.4170592 2.5748,13.5741566 L0.6911645,18.1879699 C0.377246,18.5598582 0.533215,19.1272231 0.533215,19.1272231 C0.533215,20.1165713 1.27946707,21.0498542 2.27946707,21.0498542 C3.00612381,21.0498542 3.63399899,20.5585625 3.63399899,20.0641706 L5.01261453,14.6818231 L13.4147147,14.6818231 L14.6563168,20.0641706 C14.6563168,20.5585625 15.2052657,21.0498542 15.6563168,21.0498542 C16.6563168,21.0498542 17.4,20.2166813 17.4,19.1272231 L15.3127625,13.5741566 C15.3127625,13.4170592 15.4688,13.2599618 15.7827238,13.2599618 Z" />
                                        </svg>
                                    </button>
                                </div>
                                @error('body')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </form>
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="flex flex-1 flex-col items-center justify-center">
                        <svg class="mb-4 h-16 w-16 text-zinc-300 dark:text-zinc-700" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ __('Select a conversation') }}
                        </h3>
                        <p class="mt-2 text-center text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Choose a conversation from the list to start messaging.') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
