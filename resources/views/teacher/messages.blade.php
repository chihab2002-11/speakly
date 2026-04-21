<x-layouts.teacher :title="__('Messages')" :currentRoute="'messages'">
    @php
        $currentRole = \App\Support\DashboardRedirector::roleFor(auth()->user());
    @endphp

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
            Messages
        </h1>
        <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
            Communicate with students, parents, and school administration.
        </p>
    </div>

    {{-- Messages Container --}}
    <div 
        class="flex min-w-0 overflow-hidden rounded-3xl border"
        style="background-color: #FFFFFF; border-color: var(--lumina-border-light); min-height: calc(100vh - 280px);"
    >
        {{-- Conversations Sidebar --}}
        <div class="flex w-full shrink-0 flex-col border-r md:w-80 lg:w-96 {{ $selectedUser ? 'hidden md:flex' : 'flex' }}" style="border-color: var(--lumina-border);">
            {{-- Sidebar Header --}}
            <div class="flex items-center justify-between border-b p-4" style="border-color: var(--lumina-border);">
                <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                    Conversations
                </h3>
                {{-- New Conversation Button --}}
                <button 
                    onclick="document.getElementById('newConversationModal').classList.remove('hidden')"
                    class="flex h-8 w-8 items-center justify-center rounded-lg transition-all hover:bg-gray-100 cursor-pointer"
                    title="New Conversation"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-primary);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
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
                                style="background-color: var(--lumina-accent-green-light);"
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
        <div class="min-w-0 flex-1 flex-col {{ $selectedUser ? 'flex' : 'hidden md:flex' }}">
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
                            style="background-color: var(--lumina-accent-green-light);"
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
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Messages Container --}}
                <div class="flex-1 overflow-y-auto p-6" id="messages-container">
                    <div class="mx-auto max-w-2xl space-y-4">
                        @forelse($selectedConversation ?? [] as $message)
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

    {{-- New Conversation Modal --}}
    <div 
        id="newConversationModal" 
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
        onclick="if(event.target === this) this.classList.add('hidden')"
    >
        <div 
            class="w-full max-w-lg rounded-3xl"
            style="background-color: #FFFFFF;"
            onclick="event.stopPropagation()"
        >
            {{-- Modal Header --}}
            <div class="flex items-center justify-between border-b p-4" style="border-color: var(--lumina-border);">
                <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                    New Conversation
                </h3>
                <button 
                    onclick="document.getElementById('newConversationModal').classList.add('hidden')"
                    class="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-gray-100 cursor-pointer"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Filters --}}
            <div class="border-b p-4 space-y-3" style="border-color: var(--lumina-border);">
                {{-- Role Filter --}}
                <div>
                    <label for="recipient-role-filter" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Filter by Role</label>
                    <select 
                        id="recipient-role-filter"
                        class="w-full rounded-xl border px-4 py-2 text-sm outline-none transition-all focus:ring-2 cursor-pointer"
                        style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card);"
                    >
                        <option value="">All Roles</option>
                        <option value="student">Students</option>
                        <option value="parent">Parents</option>
                        <option value="teacher">Teachers</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                {{-- Search --}}
                <div>
                    <label for="recipient-search" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Search by Name or Email</label>
                    <div class="flex items-center gap-2 rounded-xl px-4 py-2" style="background-color: var(--lumina-bg-card);">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input 
                            type="text" 
                            id="recipient-search"
                            placeholder="Search by name or email..." 
                            class="flex-1 border-none bg-transparent text-sm outline-none placeholder:text-gray-400"
                        >
                    </div>
                </div>
            </div>

            {{-- Users List --}}
            <div class="max-h-80 overflow-y-auto" id="recipients-list">
                {{-- Loading State --}}
                <div id="recipients-loading" class="flex items-center justify-center p-8">
                    <svg class="h-6 w-6 animate-spin" fill="none" viewBox="0 0 24 24" style="color: var(--lumina-primary);">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="ml-2 text-sm" style="color: var(--lumina-text-muted);">Loading recipients...</span>
                </div>

                {{-- Recipients will be populated by JavaScript --}}
                <div id="recipients-container" class="hidden"></div>
            </div>

            {{-- No Results Message --}}
            <div id="recipients-no-results" class="hidden p-8 text-center">
                <div class="mb-3 flex justify-center">
                    <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold" style="color: var(--lumina-text-primary);">No users found</p>
                <p class="mt-1 text-xs" style="color: var(--lumina-text-muted);">Try adjusting your filters or search query</p>
            </div>
        </div>
    </div>

    {{-- Script for Modal Display and Recipients Loading --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('newConversationModal');
            const recipientsList = document.getElementById('recipients-list');
            const recipientsLoading = document.getElementById('recipients-loading');
            const recipientsContainer = document.getElementById('recipients-container');
            const recipientsNoResults = document.getElementById('recipients-no-results');
            const searchInput = document.getElementById('recipient-search');
            const roleFilter = document.getElementById('recipient-role-filter');
            
            // Base URL for conversations (built server-side, user ID appended client-side)
            const conversationBaseUrl = '/{{ $currentRole }}/messages/';
            
            let allRecipients = [];
            let recipientsLoaded = false;

            // Make modal flex when visible
            modal.style.display = 'none';
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'class') {
                        if (modal.classList.contains('hidden')) {
                            modal.style.display = 'none';
                        } else {
                            modal.style.display = 'flex';
                            if (!recipientsLoaded) {
                                loadRecipients();
                            }
                        }
                    }
                });
            });
            observer.observe(modal, { attributes: true });

            // Load recipients from API
            async function loadRecipients() {
                try {
                    const response = await fetch('{{ route("teacher.messages.recipients") }}');
                    const data = await response.json();
                    allRecipients = data.users;
                    recipientsLoaded = true;
                    renderRecipients(allRecipients);
                } catch (error) {
                    console.error('Error loading recipients:', error);
                    recipientsLoading.innerHTML = '<p class="text-sm text-red-500">Error loading recipients. Please try again.</p>';
                }
            }

            // Render recipients list
            function renderRecipients(recipients) {
                recipientsLoading.classList.add('hidden');
                
                if (recipients.length === 0) {
                    recipientsContainer.classList.add('hidden');
                    recipientsNoResults.classList.remove('hidden');
                    return;
                }

                recipientsNoResults.classList.add('hidden');
                recipientsContainer.classList.remove('hidden');
                
                const roleColors = {
                    student: { bg: 'bg-blue-100', text: 'text-blue-700', badgeBg: 'bg-blue-50', badgeText: 'text-blue-700' },
                    parent: { bg: 'bg-purple-100', text: 'text-purple-700', badgeBg: 'bg-purple-50', badgeText: 'text-purple-700' },
                    teacher: { bg: 'bg-green-100', text: 'text-green-700', badgeBg: 'bg-green-50', badgeText: 'text-green-700' },
                    admin: { bg: 'bg-orange-100', text: 'text-orange-700', badgeBg: 'bg-orange-50', badgeText: 'text-orange-700' },
                };

                recipientsContainer.innerHTML = recipients.map(user => {
                    const colors = roleColors[user.role] || roleColors.student;
                    return `
                        <a 
                            href="${conversationBaseUrl}${user.id}"
                            class="recipient-item flex items-center gap-4 border-b p-4 transition-colors hover:bg-gray-50"
                            style="border-color: var(--lumina-border);"
                            data-name="${user.name.toLowerCase()}"
                            data-email="${user.email.toLowerCase()}"
                            data-role="${user.role || ''}"
                        >
                            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full ${colors.bg}">
                                <span class="text-sm font-bold ${colors.text}">
                                    ${user.name.charAt(0).toUpperCase()}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold truncate" style="color: var(--lumina-text-primary);">
                                    ${user.name}
                                </h4>
                                <p class="text-xs truncate" style="color: var(--lumina-text-muted);">
                                    ${user.email}
                                    ${user.role ? `<span class="ml-1 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ${colors.badgeBg} ${colors.badgeText}">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</span>` : ''}
                                </p>
                            </div>
                            <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    `;
                }).join('');
            }

            // Filter recipients
            function filterRecipients() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedRole = roleFilter.value.toLowerCase();

                const filtered = allRecipients.filter(user => {
                    const matchesSearch = searchTerm === '' || 
                        user.name.toLowerCase().includes(searchTerm) || 
                        user.email.toLowerCase().includes(searchTerm);
                    const matchesRole = selectedRole === '' || user.role === selectedRole;
                    return matchesSearch && matchesRole;
                });

                renderRecipients(filtered);
            }

            // Add event listeners for filtering
            searchInput.addEventListener('input', filterRecipients);
            roleFilter.addEventListener('change', filterRecipients);
        });
    </script>
</x-layouts.teacher>
