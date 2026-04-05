<x-layouts.admin :title="__('Messages')">
    @php
        $currentRole = \App\Support\DashboardRedirector::roleFor(auth()->user());
    @endphp

    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
                Messages
            </h1>
            <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
                Communicate with students, parents, and staff.
            </p>
        </div>
        
        {{-- New Message Button --}}
        <a 
            href="{{ route('admin.messages.new') }}"
            class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white transition-all hover:opacity-90"
            style="background-color: var(--lumina-primary);"
        >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Message
        </a>
    </div>

    {{-- Messages Container --}}
    <div 
        class="flex overflow-hidden rounded-3xl border"
        style="background-color: #FFFFFF; border-color: var(--lumina-border-light); min-height: calc(100vh - 280px);"
    >
        {{-- Conversations Sidebar --}}
        <div class="flex w-full flex-col border-r md:w-80 lg:w-96 {{ $selectedUser ? 'hidden md:flex' : 'flex' }}" style="border-color: var(--lumina-border);">
            {{-- Sidebar Header --}}
            <div class="flex items-center justify-between border-b p-4" style="border-color: var(--lumina-border);">
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
                    <div 
                        class="flex items-center gap-2 rounded-xl px-4 py-2"
                        style="background-color: var(--lumina-bg-card);"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input 
                            type="text" 
                            name="search"
                            value="{{ $search ?? '' }}"
                            placeholder="Search conversations..." 
                            class="flex-1 border-none bg-transparent text-sm outline-none placeholder:text-gray-400"
                        >
                    </div>
                </form>
            </div>

            {{-- Conversations List --}}
            <div class="flex-1 overflow-y-auto">
                @forelse($conversations ?? [] as $conv)
                    <a 
                        href="{{ route('role.messages.conversation', ['role' => $currentRole, 'conversation' => $conv['user']->id]) }}"
                        class="flex items-start gap-3 border-b p-4 transition-colors hover:bg-gray-50 {{ $selectedUser && $selectedUser->id === $conv['user']->id ? 'bg-gray-50' : '' }}"
                        style="border-color: var(--lumina-border);"
                    >
                        {{-- Avatar --}}
                        <div class="relative">
                            <div 
                                class="flex h-12 w-12 items-center justify-center rounded-full"
                                style="background-color: var(--lumina-accent-green-bg);"
                            >
                                <span class="text-sm font-bold" style="color: var(--lumina-primary);">
                                    {{ substr($conv['user']->name, 0, 1) }}
                                </span>
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
                                <h4 class="truncate text-sm font-semibold {{ $conv['unreadCount'] > 0 ? 'font-bold' : '' }}" style="color: var(--lumina-text-primary);">
                                    {{ $conv['user']->name }}
                                </h4>
                                @if($conv['lastMessage'])
                                    <span class="shrink-0 text-[10px]" style="color: var(--lumina-text-muted);">
                                        {{ $conv['lastMessage']->created_at->format('M j, H:i') }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">
                                {{ $conv['user']->email }}
                                @if($conv['user']->roles->isNotEmpty())
                                    • {{ ucfirst($conv['user']->roles->first()->name) }}
                                @endif
                            </p>
                            @if($conv['lastMessage'])
                                <p class="mt-1 truncate text-xs {{ $conv['unreadCount'] > 0 ? 'font-semibold' : '' }}" style="color: var(--lumina-text-secondary);">
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
                        <svg class="mb-4 h-16 w-16 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
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
        <div class="flex-1 flex-col {{ $selectedUser ? 'flex' : 'hidden md:flex' }}">
            @if($selectedUser)
                {{-- Chat Header --}}
                <div class="flex items-center justify-between border-b p-4" style="border-color: var(--lumina-border);">
                    <div class="flex items-center gap-3">
                        {{-- Back button for mobile --}}
                        <a 
                            href="{{ route('role.messages.index', ['role' => $currentRole]) }}"
                            class="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-gray-100 md:hidden"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--lumina-text-muted);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <div 
                            class="flex h-10 w-10 items-center justify-center rounded-full"
                            style="background-color: var(--lumina-accent-green-bg);"
                        >
                            <span class="text-sm font-bold" style="color: var(--lumina-primary);">
                                {{ substr($selectedUser->name, 0, 1) }}
                            </span>
                        </div>
                        <div>
                            <h4 class="font-semibold" style="color: var(--lumina-text-primary);">
                                {{ $selectedUser->name }}
                            </h4>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">
                                {{ $selectedUser->email }}
                                @if($selectedUser->roles->isNotEmpty())
                                    • {{ ucfirst($selectedUser->roles->first()->name) }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Messages Container --}}
                <div class="flex-1 overflow-y-auto p-6" id="messages-container">
                    <div class="mx-auto max-w-2xl space-y-4">
                        @forelse($selectedConversation as $message)
                            @if($message->sender_id === auth()->id())
                                {{-- My Message (Right) --}}
                                <div class="flex justify-end">
                                    <div class="max-w-[70%]">
                                        @if($message->subject)
                                            <p class="mb-1 text-right text-xs font-semibold" style="color: var(--lumina-text-muted);">
                                                Re: {{ $message->subject }}
                                            </p>
                                        @endif
                                        <div 
                                            class="rounded-2xl rounded-tr-sm px-4 py-3"
                                            style="background-color: var(--lumina-primary); color: white;"
                                        >
                                            <p class="whitespace-pre-wrap text-sm">{{ $message->body }}</p>
                                        </div>
                                        <p class="mt-1 text-right text-[10px]" style="color: var(--lumina-text-muted);">
                                            {{ $message->created_at->format('H:i') }}
                                        </p>
                                    </div>
                                </div>
                            @else
                                {{-- Other's Message (Left) --}}
                                <div class="flex justify-start">
                                    <div class="max-w-[70%]">
                                        @if($message->subject)
                                            <p class="mb-1 text-xs font-semibold" style="color: var(--lumina-text-muted);">
                                                Re: {{ $message->subject }}
                                            </p>
                                        @endif
                                        <div 
                                            class="rounded-2xl rounded-tl-sm px-4 py-3"
                                            style="background-color: var(--lumina-bg-card);"
                                        >
                                            <p class="whitespace-pre-wrap text-sm" style="color: var(--lumina-text-primary);">
                                                {{ $message->body }}
                                            </p>
                                        </div>
                                        <p class="mt-1 text-[10px]" style="color: var(--lumina-text-muted);">
                                            {{ $message->created_at->format('H:i') }}
                                        </p>
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
                <div class="border-t p-4" style="border-color: var(--lumina-border);">
                    <form method="POST" action="{{ route('role.messages.store', ['role' => $currentRole]) }}">
                        @csrf
                        <input type="hidden" name="receiver_id" value="{{ $selectedUser->id }}">
                        
                        {{-- Subject (optional) --}}
                        <div class="mb-3">
                            <input 
                                type="text" 
                                name="subject" 
                                placeholder="Subject (optional)"
                                class="w-full rounded-xl border px-4 py-2 text-sm outline-none transition-all focus:ring-2"
                                style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                            >
                        </div>

                        <div class="flex items-end gap-3">
                            {{-- Text Input --}}
                            <div class="flex-1">
                                <textarea 
                                    name="body" 
                                    rows="2"
                                    required
                                    placeholder="Type your message..."
                                    class="w-full resize-none rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2"
                                    style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                                ></textarea>
                            </div>

                            {{-- Send Button --}}
                            <button 
                                type="submit"
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl transition-all hover:opacity-90 cursor-pointer"
                                style="background-color: var(--lumina-primary);"
                            >
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            @else
                {{-- No Conversation Selected --}}
                <div class="flex flex-1 flex-col items-center justify-center p-8 text-center">
                    <div 
                        class="mb-4 flex h-20 w-20 items-center justify-center rounded-full"
                        style="background-color: var(--lumina-bg-card);"
                    >
                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
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
    @if($selectedUser)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('messages-container');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        </script>
    @endif
</x-layouts.admin>
