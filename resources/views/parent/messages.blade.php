<x-layouts.parent 
    :title="'Messages'"
    :pageTitle="'Messages'"
    :currentRoute="'messages'"
    :user="$user ?? null"
>
    {{-- Messages Container --}}
    <div 
        class="flex h-[calc(100vh-180px)] overflow-hidden rounded-3xl border"
        style="background-color: #FFFFFF; border-color: var(--lumina-border-light);"
    >
        {{-- Conversations Sidebar --}}
        <div class="flex w-full flex-col border-r md:w-80 lg:w-96" style="border-color: var(--lumina-border);">
            {{-- Sidebar Header --}}
            <div class="flex items-center justify-between border-b p-4" style="border-color: var(--lumina-border);">
                <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                    Conversations
                </h3>
                <button 
                    class="flex h-9 w-9 items-center justify-center rounded-lg transition-colors hover:bg-gray-100"
                    title="New Message"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-primary);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            </div>

            {{-- Search --}}
            <div class="p-4">
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
                        placeholder="Search conversations..." 
                        class="flex-1 border-none bg-transparent text-sm outline-none placeholder:text-gray-400"
                    >
                </div>
            </div>

            {{-- Filter Tabs --}}
            <div class="flex gap-2 px-4 pb-2">
                <button 
                    class="rounded-full px-3 py-1 text-xs font-semibold"
                    style="background-color: var(--lumina-primary); color: white;"
                >
                    All
                </button>
                <button 
                    class="rounded-full px-3 py-1 text-xs font-semibold transition-colors hover:bg-gray-100"
                    style="color: var(--lumina-text-muted);"
                >
                    Teachers
                </button>
                <button 
                    class="rounded-full px-3 py-1 text-xs font-semibold transition-colors hover:bg-gray-100"
                    style="color: var(--lumina-text-muted);"
                >
                    Office
                </button>
            </div>

            {{-- Conversations List --}}
            <div class="flex-1 overflow-y-auto">
                @forelse($conversations ?? [] as $index => $conversation)
                    <a 
                        href="#"
                        class="flex items-start gap-3 border-b p-4 transition-colors hover:bg-gray-50 {{ $index === 0 ? 'bg-gray-50' : '' }}"
                        style="border-color: var(--lumina-border);"
                    >
                        {{-- Avatar --}}
                        <div class="relative">
                            <div 
                                class="flex h-12 w-12 items-center justify-center rounded-full"
                                style="background-color: {{ $index === 0 ? 'var(--lumina-accent-green-bg)' : ($index === 1 ? '#DDE1FF' : ($index === 2 ? '#FEF3C7' : 'var(--lumina-bg-card)')) }};"
                            >
                                <span 
                                    class="text-sm font-bold"
                                    style="color: {{ $index === 0 ? 'var(--lumina-primary)' : ($index === 1 ? '#4F46E5' : ($index === 2 ? '#D97706' : 'var(--lumina-text-secondary)')) }};"
                                >
                                    {{ substr($conversation['name'], 0, 2) }}
                                </span>
                            </div>
                            @if(($conversation['unread'] ?? 0) > 0)
                                <span class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                                    {{ $conversation['unread'] }}
                                </span>
                            @endif
                        </div>

                        {{-- Conversation Info --}}
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <h4 class="truncate text-sm font-semibold" style="color: var(--lumina-text-primary);">
                                    {{ $conversation['name'] }}
                                </h4>
                                <span class="shrink-0 text-[10px]" style="color: var(--lumina-text-muted);">
                                    {{ $conversation['time'] }}
                                </span>
                            </div>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">
                                {{ $conversation['role'] }}
                            </p>
                            <p class="mt-1 truncate text-xs {{ ($conversation['unread'] ?? 0) > 0 ? 'font-semibold' : '' }}" style="color: var(--lumina-text-secondary);">
                                {{ $conversation['lastMessage'] }}
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="flex flex-col items-center justify-center p-8 text-center">
                        <svg class="mb-4 h-16 w-16 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p class="text-sm" style="color: var(--lumina-text-muted);">No conversations yet</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Chat Area --}}
        <div class="hidden flex-1 flex-col md:flex">
            @if($activeConversation ?? false)
                {{-- Chat Header --}}
                <div class="flex items-center justify-between border-b p-4" style="border-color: var(--lumina-border);">
                    <div class="flex items-center gap-3">
                        <div 
                            class="flex h-10 w-10 items-center justify-center rounded-full"
                            style="background-color: var(--lumina-accent-green-bg);"
                        >
                            <span class="text-sm font-bold" style="color: var(--lumina-primary);">
                                {{ substr($activeConversation['name'], 0, 2) }}
                            </span>
                        </div>
                        <div>
                            <h4 class="font-semibold" style="color: var(--lumina-text-primary);">
                                {{ $activeConversation['name'] }}
                            </h4>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">
                                {{ $activeConversation['role'] }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="flex h-9 w-9 items-center justify-center rounded-lg transition-colors hover:bg-gray-100">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </button>
                        <button class="flex h-9 w-9 items-center justify-center rounded-lg transition-colors hover:bg-gray-100">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Messages Container --}}
                <div class="flex-1 overflow-y-auto p-6">
                    <div class="mx-auto max-w-2xl space-y-4">
                        {{-- Date Separator --}}
                        <div class="flex items-center justify-center">
                            <span class="rounded-full px-3 py-1 text-xs" style="background-color: var(--lumina-bg-card); color: var(--lumina-text-muted);">
                                Today
                            </span>
                        </div>

                        @foreach($messages ?? [] as $message)
                            @if(($message['sender'] ?? '') === 'parent')
                                {{-- Parent's Message (Right) --}}
                                <div class="flex justify-end">
                                    <div class="max-w-[70%]">
                                        <div 
                                            class="rounded-2xl rounded-tr-sm px-4 py-3"
                                            style="background-color: var(--lumina-primary); color: white;"
                                        >
                                            <p class="text-sm">{{ $message['content'] }}</p>
                                        </div>
                                        <p class="mt-1 text-right text-[10px]" style="color: var(--lumina-text-muted);">
                                            {{ $message['time'] }}
                                        </p>
                                    </div>
                                </div>
                            @else
                                {{-- Teacher's Message (Left) --}}
                                <div class="flex justify-start">
                                    <div class="max-w-[70%]">
                                        <div 
                                            class="rounded-2xl rounded-tl-sm px-4 py-3"
                                            style="background-color: var(--lumina-bg-card);"
                                        >
                                            <p class="text-sm" style="color: var(--lumina-text-primary);">
                                                {{ $message['content'] }}
                                            </p>
                                        </div>
                                        <p class="mt-1 text-[10px]" style="color: var(--lumina-text-muted);">
                                            {{ $message['time'] }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Message Input --}}
                <div class="border-t p-4" style="border-color: var(--lumina-border);">
                    <div class="flex items-end gap-3">
                        {{-- Attachment Button --}}
                        <button 
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-colors hover:bg-gray-100"
                            style="background-color: var(--lumina-bg-card);"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                        </button>

                        {{-- Text Input --}}
                        <div class="flex-1">
                            <textarea 
                                rows="1"
                                placeholder="Type your message..."
                                class="w-full resize-none rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2"
                                style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                            ></textarea>
                        </div>

                        {{-- Send Button --}}
                        <button 
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition-all hover:opacity-90"
                            style="background-color: var(--lumina-primary);"
                        >
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </div>
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
                        Choose a conversation from the list or start a new one
                    </p>
                    <button 
                        class="mt-6 flex items-center gap-2 rounded-xl px-6 py-3 font-semibold transition-all hover:opacity-90"
                        style="background-color: var(--lumina-primary); color: white;"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        New Message
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Quick Contact Cards (Mobile Visible) --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 md:hidden">
        <a href="#" class="flex items-center gap-3 rounded-2xl border p-4 transition-all hover:shadow-md" style="background-color: #FFFFFF; border-color: var(--lumina-border-light);">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl" style="background-color: var(--lumina-accent-green-bg);">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-primary);">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <h4 class="font-semibold" style="color: var(--lumina-text-primary);">School Office</h4>
                <p class="text-xs" style="color: var(--lumina-text-muted);">General inquiries</p>
            </div>
        </a>
        
        <a href="#" class="flex items-center gap-3 rounded-2xl border p-4 transition-all hover:shadow-md" style="background-color: #FFFFFF; border-color: var(--lumina-border-light);">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl" style="background-color: #FEF3C7;">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #D97706;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <h4 class="font-semibold" style="color: var(--lumina-text-primary);">Finance Office</h4>
                <p class="text-xs" style="color: var(--lumina-text-muted);">Payment questions</p>
            </div>
        </a>
        
        <a href="#" class="flex items-center gap-3 rounded-2xl border p-4 transition-all hover:shadow-md" style="background-color: #FFFFFF; border-color: var(--lumina-border-light);">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl" style="background-color: #DDE1FF;">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #4F46E5;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <h4 class="font-semibold" style="color: var(--lumina-text-primary);">Academic Office</h4>
                <p class="text-xs" style="color: var(--lumina-text-muted);">Grades & schedules</p>
            </div>
        </a>
    </div>
</x-layouts.parent>
