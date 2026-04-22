<x-layouts.parent 
    :title="'Messages'"
    :pageTitle="'Messages'"
    :currentRoute="'messages'"
    :user="$user ?? null"
    :children="$children ?? []"
>
    @php
        $currentRole = \App\Support\DashboardRedirector::roleFor(auth()->user());
    @endphp

    <style>
        .msg-shell {
            background: #fff;
            border: 1px solid rgba(190, 201, 191, 0.35);
            border-radius: 24px;
            min-height: calc(100vh - 280px);
            overflow: hidden;
        }

        .msg-sidebar {
            background: #fbfcfb;
            border-right: 1px solid var(--lumina-border);
        }

        .msg-search {
            width: 100%;
            border: 1px solid var(--lumina-border);
            border-radius: 12px;
            padding: 10px 12px;
            font-size: 13px;
            background: #fff;
            color: var(--lumina-text-primary);
        }

        .msg-search:focus {
            outline: none;
            border-color: #9fb8aa;
            box-shadow: 0 0 0 3px rgba(46, 139, 106, 0.12);
        }

        .msg-conv-item {
            border-bottom: 1px solid var(--lumina-border);
            padding: 16px 18px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
            transition: background-color 0.16s ease;
        }

        .msg-conv-item:hover { background: #f6faf8; }
        .msg-conv-item.active { background: #eef5f1; }

        .msg-avatar {
            width: 56px;
            height: 56px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #dff1e8;
            color: #05603a;
            font-size: 18px;
            font-weight: 800;
            flex-shrink: 0;
            overflow: hidden;
        }

        .msg-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 999px;
        }

        .msg-mini-avatar {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #dff1e8;
            color: #05603a;
            font-size: 13px;
            font-weight: 800;
            flex-shrink: 0;
            overflow: hidden;
        }

        .msg-mini-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .msg-chat-panel {
            background: linear-gradient(180deg, #fafcfa 0%, #f4f8f5 100%);
        }

        .msg-thread {
            max-width: 900px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .msg-message-content {
            max-width: min(78%, 580px);
        }

        .msg-bubble-self {
            background: #0b7a4f;
            color: #fff;
            border-radius: 16px 16px 4px 16px;
            padding: 12px 16px;
        }

        .msg-bubble-other {
            background: #fff;
            color: var(--lumina-text-primary);
            border: 1px solid #e3e8e5;
            border-radius: 16px 16px 16px 4px;
            padding: 12px 16px;
        }
    </style>

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
            Messages
        </h1>
        <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
            Communicate with your children's teachers and school administration.
        </p>
    </div>

    {{-- Messages Container --}}
    <div class="msg-shell flex">
        {{-- Conversations Sidebar --}}
        <div class="msg-sidebar flex w-full flex-col md:w-80 lg:w-96 {{ $selectedUser ? 'hidden md:flex' : 'flex' }}">
            {{-- Sidebar Header --}}
            <div class="flex items-center justify-between border-b p-5" style="border-color: var(--lumina-border);">
                <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                    Conversations
                </h3>
            </div>

            {{-- Search --}}
            <div class="p-4">
                <form method="GET" action="{{ route('role.messages.index', ['role' => $currentRole]) }}">
                    @if($selectedUser)
                        <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                    @endif
                    <input
                        type="text"
                        name="search"
                        value="{{ $search ?? '' }}"
                        placeholder="Search conversations..."
                        class="msg-search"
                    >
                </form>
            </div>

            {{-- Conversations List --}}
            <div class="flex-1 overflow-y-auto">
                @forelse($conversations ?? [] as $conv)
                    <a 
                        href="{{ route('role.messages.conversation', ['role' => $currentRole, 'conversation' => $conv['user']->id]) }}"
                        class="msg-conv-item {{ $selectedUser && $selectedUser->id === $conv['user']->id ? 'active' : '' }}"
                    >
                        {{-- Avatar --}}
                        <div class="relative">
                            <div class="msg-avatar">
                                @if($conv['user']->avatar)
                                    <img src="{{ $conv['user']->avatar }}" alt="{{ $conv['user']->name }} avatar">
                                @else
                                    {{ substr($conv['user']->name, 0, 1) }}
                                @endif
                            </div>
                            @if($conv['unreadCount'] > 0)
                                <span class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                                    {{ $conv['unreadCount'] }}
                                </span>
                            @endif
                        </div>

                        {{-- Conversation Info --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="truncate text-[15px] font-semibold {{ $conv['unreadCount'] > 0 ? 'font-bold' : '' }}" style="color: var(--lumina-text-primary);">
                                    {{ $conv['user']->name }}
                                </h4>
                                @if($conv['lastMessage'])
                                    <span class="shrink-0 text-[11px]" style="color: var(--lumina-text-muted);">
                                        {{ $conv['lastMessage']->created_at->format('M j, H:i') }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-[12px]" style="color: var(--lumina-text-muted);">
                                {{ $conv['user']->email }}
                            </p>
                            @if($conv['lastMessage'])
                                <p class="mt-1 truncate text-[13px] {{ $conv['unreadCount'] > 0 ? 'font-semibold' : '' }}" style="color: var(--lumina-text-secondary);">
                                    @if($conv['lastMessage']->sender_id === auth()->id())
                                        You: 
                                    @endif
                                    {{ $conv['lastMessage']->body }}
                                </p>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="flex flex-col items-center justify-center p-8 text-center">
                        <p class="text-sm" style="color: var(--lumina-text-muted);">
                            @if($search)
                                No conversations match your search.
                            @else
                                No conversations yet.
                            @endif
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Chat Area --}}
        <div class="msg-chat-panel flex-1 flex-col {{ $selectedUser ? 'flex' : 'hidden md:flex' }}">
            @if($selectedUser)
                {{-- Chat Header --}}
                <div class="flex items-center justify-between border-b px-5 py-4" style="border-color: var(--lumina-border); background-color: #fff;">
                    <div class="flex items-center gap-3">
                        {{-- Back button for mobile --}}
                        <a 
                            href="{{ route('role.messages.index', ['role' => $currentRole]) }}"
                            class="rounded-lg px-2 py-1 text-sm font-semibold hover:bg-gray-100 md:hidden"
                            style="color: var(--lumina-text-secondary);"
                        >
                            Back
                        </a>
                        <div class="msg-avatar">
                            @if($selectedUser->avatar)
                                <img src="{{ $selectedUser->avatar }}" alt="{{ $selectedUser->name }} avatar">
                            @else
                                {{ substr($selectedUser->name, 0, 1) }}
                            @endif
                        </div>
                        <div>
                            <h4 class="font-semibold" style="color: var(--lumina-text-primary);">
                                {{ $selectedUser->name }}
                            </h4>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">
                                {{ $selectedUser->email }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Messages Container --}}
                <div class="flex-1 overflow-y-auto p-6" id="messages-container">
                    <div class="msg-thread">
                        @forelse($selectedConversation ?? [] as $message)
                            @if($message->sender_id === auth()->id())
                                {{-- My Message (Right) --}}
                                <div class="flex justify-end">
                                    <div class="flex items-end gap-3">
                                        <div class="msg-message-content">
                                        @if($message->subject)
                                            <p class="mb-1 text-right text-xs font-semibold" style="color: var(--lumina-text-muted);">
                                                Re: {{ $message->subject }}
                                            </p>
                                        @endif
                                        <div class="msg-bubble-self">
                                            <p class="whitespace-pre-wrap text-[15px] leading-relaxed">{{ $message->body }}</p>
                                        </div>
                                        <p class="mt-1 text-right text-[11px]" style="color: var(--lumina-text-muted);">
                                            {{ $message->created_at->format('H:i') }}
                                        </p>
                                        </div>
                                        <div class="msg-mini-avatar">
                                            @if(auth()->user()?->avatar)
                                                <img src="{{ auth()->user()->avatar }}" alt="Your avatar">
                                            @else
                                                {{ substr(auth()->user()->name ?? 'Y', 0, 1) }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- Other's Message (Left) --}}
                                <div class="flex justify-start">
                                    <div class="flex items-end gap-3">
                                        <div class="msg-mini-avatar">
                                            @if($selectedUser->avatar)
                                                <img src="{{ $selectedUser->avatar }}" alt="{{ $selectedUser->name }} avatar">
                                            @else
                                                {{ substr($selectedUser->name, 0, 1) }}
                                            @endif
                                        </div>
                                        <div class="msg-message-content">
                                        @if($message->subject)
                                            <p class="mb-1 text-xs font-semibold" style="color: var(--lumina-text-muted);">
                                                Re: {{ $message->subject }}
                                            </p>
                                        @endif
                                        <div class="msg-bubble-other">
                                            <p class="whitespace-pre-wrap text-[15px] leading-relaxed">{{ $message->body }}</p>
                                        </div>
                                        <p class="mt-1 text-[11px]" style="color: var(--lumina-text-muted);">
                                            {{ $message->created_at->format('H:i') }}
                                        </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="py-8 text-center">
                                <p class="text-sm" style="color: var(--lumina-text-muted);">
                                    No messages yet. Start the conversation!
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Message Input --}}
                <div class="border-t bg-white p-4" style="border-color: var(--lumina-border);">
                    <form action="{{ route('role.messages.store', ['role' => $currentRole]) }}" method="POST">
                        @csrf
                        <input type="hidden" name="receiver_id" value="{{ $selectedUser->id }}">
                        
                        {{-- Subject (optional) --}}
                        <div class="mb-3">
                            <input 
                                type="text"
                                name="subject"
                                placeholder="Subject (optional)"
                                value="{{ old('subject') }}"
                                class="w-full rounded-xl border px-4 py-2 text-sm outline-none transition-all focus:ring-2"
                                style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                            >
                            @error('subject')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-end gap-3">
                            {{-- Text Input --}}
                            <div class="flex-1">
                                <textarea 
                                    name="body"
                                    rows="2"
                                    placeholder="Type your message..."
                                    required
                                    class="w-full resize-none rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2"
                                    style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                                >{{ old('body') }}</textarea>
                                @error('body')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Send Button --}}
                            <button 
                                type="submit"
                                class="flex h-12 shrink-0 items-center justify-center rounded-xl px-5 text-sm font-bold transition-all hover:opacity-90 cursor-pointer"
                                style="background-color: var(--lumina-primary);"
                            >
                                <span class="text-white">Send</span>
                            </button>
                        </div>
                    </form>
                </div>
            @else
                {{-- No Conversation Selected --}}
                <div class="flex flex-1 flex-col items-center justify-center p-8 text-center">
                    <h3 class="mb-2 text-xl font-bold" style="color: var(--lumina-text-primary);">
                        Select a Conversation
                    </h3>
                    <p class="text-sm" style="color: var(--lumina-text-muted);">
                        Choose a conversation from the list to start messaging.
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Auto-scroll to bottom of messages --}}
    @if($selectedUser && count($selectedConversation ?? []) > 0)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('messages-container');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        </script>
    @endif
</x-layouts.parent>
