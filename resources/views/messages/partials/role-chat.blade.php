@php
    $currentRole = $currentRole ?? \App\Support\DashboardRedirector::roleFor(auth()->user());
    $messageActor = $messageActor ?? auth()->user();
    $messageIndexRouteName = $messageIndexRouteName ?? 'role.messages.index';
    $messageIndexRouteParams = $messageIndexRouteParams ?? ['role' => $currentRole];
    $messageConversationRouteName = $messageConversationRouteName ?? 'role.messages.conversation';
    $messageConversationRouteParams = $messageConversationRouteParams ?? ['role' => $currentRole];
    $messageStoreRouteName = $messageStoreRouteName ?? 'role.messages.store';
    $messageStoreRouteParams = $messageStoreRouteParams ?? ['role' => $currentRole];
    $messageRecipientsRouteName = $messageRecipientsRouteName ?? 'role.messages.recipients';
    $messageRecipientsRouteParams = $messageRecipientsRouteParams ?? ['role' => $currentRole];
    $messageDescription = $messageDescription ?? 'Communicate with your school community.';
    $showNewConversation = $showNewConversation ?? true;
    $avatarInitials = static fn ($person): string => $person?->initials() ?: strtoupper(substr((string) ($person?->name ?? '?'), 0, 1));
@endphp

<style>
    .role-chat-shell {
        min-height: calc(100vh - 230px);
        height: calc(100vh - 230px);
    }

    .role-chat-panel {
        width: min(100%, 820px);
    }

    .role-chat-thread {
        background:
            radial-gradient(circle at 10px 10px, rgba(6, 106, 65, 0.045) 1px, transparent 1px),
            linear-gradient(180deg, #eef8f2 0%, #f7fbf8 100%);
        background-size: 24px 24px, 100% 100%;
    }

    .role-chat-avatar {
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.55), 0 8px 18px rgba(3, 76, 60, 0.08);
    }

    .role-chat-bubble {
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.06);
        transition: transform .16s ease, box-shadow .16s ease;
    }

    .role-chat-bubble:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
    }

    @media (max-width: 767px) {
        .role-chat-shell {
            height: calc(100vh - 190px);
            min-height: 620px;
        }

        .role-chat-panel {
            width: 100%;
        }
    }
</style>

<div class="mb-6">
    <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
        Messages
    </h1>
    <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
        {{ $messageDescription }}
    </p>
</div>

<div
    class="role-chat-shell flex min-w-0 overflow-hidden rounded-3xl border shadow-sm"
    style="background-color: #FFFFFF; border-color: var(--lumina-border-light, rgba(190, 201, 191, 0.35));"
>
    <div class="flex w-full shrink-0 flex-col border-r md:w-80 lg:w-[21rem] {{ $selectedUser ? 'hidden md:flex' : 'flex' }}" style="border-color: var(--lumina-border); background: #FBFEFC;">
        <div class="flex items-center justify-between border-b px-5 py-4" style="border-color: var(--lumina-border);">
            <div>
                <h3 class="text-lg font-extrabold" style="color: var(--lumina-text-primary);">Conversations</h3>
                <p class="text-xs" style="color: var(--lumina-text-muted);">
                    {{ count($conversations ?? []) }} active thread{{ count($conversations ?? []) === 1 ? '' : 's' }}
                </p>
            </div>
            @if($showNewConversation)
                <button
                    type="button"
                    onclick="document.getElementById('newConversationModal')?.classList.remove('hidden')"
                    class="flex h-9 w-9 items-center justify-center rounded-xl transition-all hover:-translate-y-0.5 hover:bg-emerald-50 cursor-pointer"
                    title="New Conversation"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-primary);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            @endif
        </div>

        <div class="px-4 py-3">
            <form method="GET" action="{{ route($messageIndexRouteName, $messageIndexRouteParams) }}">
                @if($selectedUser)
                    <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                @endif
                <div
                    class="flex items-center gap-2 rounded-2xl border px-4 py-2.5 transition focus-within:ring-2 focus-within:ring-emerald-100"
                    style="background-color: #F5FAF7; border-color: var(--lumina-border);"
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

        <div class="flex-1 space-y-1 overflow-y-auto px-3 pb-3">
            @forelse($conversations ?? [] as $conv)
                <a
                    href="{{ route($messageConversationRouteName, array_merge($messageConversationRouteParams, ['conversation' => $conv['user']->id])) }}"
                    class="group flex items-start gap-3 rounded-2xl border p-3.5 transition-all hover:-translate-y-0.5 hover:shadow-sm {{ $selectedUser && $selectedUser->id === $conv['user']->id ? 'border-emerald-200 bg-emerald-50/80 shadow-sm' : 'border-transparent hover:border-slate-100 hover:bg-white' }}"
                >
                    <div class="relative">
                        <div class="role-chat-avatar flex h-12 w-12 items-center justify-center rounded-2xl" style="background: linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%);">
                            <span class="text-sm font-bold" style="color: var(--lumina-primary);">{{ $avatarInitials($conv['user']) }}</span>
                        </div>
                        @if($conv['unreadCount'] > 0)
                            <span class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                                {{ $conv['unreadCount'] }}
                            </span>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <h4 class="truncate text-sm font-semibold {{ $conv['unreadCount'] > 0 ? 'font-extrabold' : '' }}" style="color: var(--lumina-text-primary);">
                                {{ $conv['user']->name }}
                            </h4>
                            @if($conv['lastMessage'])
                                <span class="shrink-0 text-[10px]" style="color: var(--lumina-text-muted);">
                                    {{ $conv['lastMessage']->created_at->format('M j, H:i') }}
                                </span>
                            @endif
                        </div>
                        <p class="truncate text-xs" style="color: var(--lumina-text-muted);">{{ $conv['user']->email }}</p>
                        @if($conv['lastMessage'])
                            <p class="mt-1 truncate text-xs {{ $conv['unreadCount'] > 0 ? 'font-semibold' : '' }}" style="color: var(--lumina-text-secondary);">
                                @if((int) $conv['lastMessage']->sender_id === (int) ($messageActor->id ?? auth()->id()))
                                    You:
                                @endif
                                {{ ltrim($conv['lastMessage']->body) }}
                            </p>
                        @endif
                    </div>
                </a>
            @empty
                <div class="flex flex-col items-center justify-center p-8 text-center">
                    <p class="text-sm" style="color: var(--lumina-text-muted);">
                        {{ ($search ?? '') ? 'No conversations match your search.' : 'No conversations yet.' }}
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="min-w-0 flex-1 {{ $selectedUser ? 'flex' : 'hidden md:flex' }}" style="background: linear-gradient(180deg, #f7fbf8 0%, #eef8f2 100%);">
        <div class="role-chat-panel flex min-w-0 flex-col border-r shadow-sm" style="background: #FFFFFF; border-color: var(--lumina-border);">
            @if($selectedUser)
                <div class="flex items-center justify-between border-b px-4 py-3 md:px-6" style="border-color: var(--lumina-border); background: rgba(255, 255, 255, 0.92);">
                    <div class="flex items-center gap-3">
                        <a
                            href="{{ route($messageIndexRouteName, $messageIndexRouteParams) }}"
                            class="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-gray-100 md:hidden"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--lumina-text-muted);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <div class="role-chat-avatar flex h-11 w-11 items-center justify-center rounded-2xl" style="background: linear-gradient(135deg, #D1FAE5 0%, #99F6BF 100%);">
                            <span class="text-sm font-bold" style="color: var(--lumina-primary);">{{ $avatarInitials($selectedUser) }}</span>
                        </div>
                        <div>
                            <h4 class="font-extrabold leading-tight" style="color: var(--lumina-text-primary);">{{ $selectedUser->name }}</h4>
                            <p class="text-xs leading-tight" style="color: var(--lumina-text-muted);">{{ $selectedUser->email }}</p>
                        </div>
                    </div>
                    <div class="hidden items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold md:flex" style="background: #ECFDF3; color: #047857;">
                        <span class="h-2 w-2 rounded-full" style="background: #10B981;"></span>
                        Thread open
                    </div>
                </div>

                <div class="role-chat-thread flex-1 overflow-y-auto px-3 py-4 md:px-5 md:py-5" id="messages-container">
                    <div class="mx-auto flex max-w-3xl flex-col gap-3.5">
                        @forelse($selectedConversation ?? [] as $message)
                            @php
                                $isMine = (int) $message->sender_id === (int) ($messageActor->id ?? auth()->id());
                                $messageAuthor = $isMine ? $messageActor : $message->sender;
                            @endphp
                            @if($isMine)
                                <div class="flex items-end justify-end gap-2.5">
                                    <div class="max-w-[86%] sm:max-w-[76%]">
                                        @if($message->subject)
                                            <p class="mb-1 text-right text-xs font-semibold" style="color: var(--lumina-text-muted);">Re: {{ $message->subject }}</p>
                                        @endif
                                        <div class="role-chat-bubble rounded-2xl rounded-br-md border px-3.5 py-2.5" style="background: linear-gradient(135deg, #0E7A4E 0%, #047857 100%); border-color: rgba(255,255,255,0.2); color: white;">
                                            <p class="whitespace-pre-wrap text-left text-sm leading-snug">{{ ltrim($message->body) }}</p>
                                        </div>
                                        <p class="mt-1 text-right text-[10px]" style="color: var(--lumina-text-muted);">You · {{ $message->created_at->format('M j, H:i') }}</p>
                                    </div>
                                    <div class="role-chat-avatar flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl" style="background: #D1FAE5; color: #047857;">
                                        <span class="text-xs font-black">{{ $avatarInitials($messageAuthor) }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-end justify-start gap-2.5">
                                    <div class="role-chat-avatar flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl" style="background: #E8F9F1; color: #034C3C;">
                                        <span class="text-xs font-black">{{ $avatarInitials($messageAuthor) }}</span>
                                    </div>
                                    <div class="max-w-[86%] sm:max-w-[76%]">
                                        @if($message->subject)
                                            <p class="mb-1 text-xs font-semibold" style="color: var(--lumina-text-muted);">Re: {{ $message->subject }}</p>
                                        @endif
                                        <div class="role-chat-bubble rounded-2xl rounded-bl-md border px-3.5 py-2.5" style="background-color: #F8FAFC; border-color: #DDE7E2;">
                                            <p class="whitespace-pre-wrap text-left text-sm leading-snug" style="color: var(--lumina-text-primary);">{{ ltrim($message->body) }}</p>
                                        </div>
                                        <p class="mt-1 text-[10px]" style="color: var(--lumina-text-muted);">{{ $messageAuthor?->name ?? 'User' }} · {{ $message->created_at->format('M j, H:i') }}</p>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="py-16 text-center">
                                <p class="text-sm" style="color: var(--lumina-text-muted);">No messages yet. Start the conversation!</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="border-t px-3 py-2.5 md:px-5" style="border-color: var(--lumina-border); background: #FCFEFC;">
                    <form action="{{ route($messageStoreRouteName, $messageStoreRouteParams) }}" method="POST">
                        @csrf
                        <input type="hidden" name="receiver_id" value="{{ $selectedUser->id }}">
                        <div class="mb-2">
                            <input
                                type="text"
                                name="subject"
                                placeholder="Subject (optional)"
                                value="{{ old('subject') }}"
                                class="w-full rounded-2xl border px-3.5 py-2 text-sm outline-none transition-all focus:border-emerald-300 focus:ring-2 focus:ring-emerald-100"
                                style="border-color: var(--lumina-border); background-color: #F8FBF8;"
                            >
                            @error('subject')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-end gap-2.5 rounded-3xl border p-1.5 shadow-sm" style="border-color: #DCE8E1; background: #F4FAF6;">
                            <div class="flex-1">
                                <textarea
                                    name="body"
                                    rows="1"
                                    placeholder="Type your message..."
                                    required
                                    class="max-h-32 min-h-[40px] w-full resize-none border-none bg-transparent px-3 py-2.5 text-sm leading-snug outline-none placeholder:text-slate-400"
                                >{{ old('body') }}</textarea>
                                @error('body')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <button
                                type="submit"
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl transition-all hover:-translate-y-0.5 hover:shadow-lg cursor-pointer"
                                style="background: linear-gradient(135deg, #047857 0%, #006A41 100%);"
                                title="Send message"
                            >
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="flex flex-1 flex-col items-center justify-center p-8 text-center">
                    <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-full" style="background-color: var(--lumina-bg-card, #F0F5EE);">
                        <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <h3 class="mb-2 text-xl font-bold" style="color: var(--lumina-text-primary);">Select a Conversation</h3>
                    <p class="text-sm" style="color: var(--lumina-text-muted);">Choose a conversation from the list to start messaging.</p>
                </div>
            @endif
        </div>
    </div>
</div>

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

@if($showNewConversation)
    <div
        id="newConversationModal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
        onclick="if(event.target === this) this.classList.add('hidden')"
    >
        <div class="w-full max-w-lg rounded-3xl" style="background-color: #FFFFFF;" onclick="event.stopPropagation()">
            <div class="flex items-center justify-between border-b p-4" style="border-color: var(--lumina-border);">
                <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">New Conversation</h3>
                <button type="button" onclick="document.getElementById('newConversationModal').classList.add('hidden')" class="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-gray-100 cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="border-b p-4 space-y-3" style="border-color: var(--lumina-border);">
                <div>
                    <label for="recipient-role-filter" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Filter by Role</label>
                    <select id="recipient-role-filter" class="w-full rounded-xl border px-4 py-2 text-sm outline-none transition-all focus:ring-2 cursor-pointer" style="border-color: var(--lumina-border); background-color: var(--lumina-bg-card, #F0F5EE);">
                        <option value="">All Roles</option>
                        <option value="student">Students</option>
                        <option value="parent">Parents</option>
                        <option value="teacher">Teachers</option>
                        <option value="admin">Admin</option>
                        <option value="secretary">Secretaries</option>
                    </select>
                </div>
                <div>
                    <label for="recipient-search" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Search by Name or Email</label>
                    <div class="flex items-center gap-2 rounded-xl px-4 py-2" style="background-color: var(--lumina-bg-card, #F0F5EE);">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                        <input type="text" id="recipient-search" placeholder="Search by name or email..." class="flex-1 border-none bg-transparent text-sm outline-none placeholder:text-gray-400">
                    </div>
                </div>
            </div>
            <div class="max-h-80 overflow-y-auto" id="recipients-list">
                <div id="recipients-loading" class="flex items-center justify-center p-8">
                    <svg class="h-6 w-6 animate-spin" fill="none" viewBox="0 0 24 24" style="color: var(--lumina-primary);">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="ml-2 text-sm" style="color: var(--lumina-text-muted);">Loading recipients...</span>
                </div>
                <div id="recipients-container" class="hidden"></div>
            </div>
            <div id="recipients-no-results" class="hidden p-8 text-center">
                <p class="text-sm font-semibold" style="color: var(--lumina-text-primary);">No users found</p>
                <p class="mt-1 text-xs" style="color: var(--lumina-text-muted);">Try adjusting your filters or search query</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('newConversationModal');
            const recipientsLoading = document.getElementById('recipients-loading');
            const recipientsContainer = document.getElementById('recipients-container');
            const recipientsNoResults = document.getElementById('recipients-no-results');
            const searchInput = document.getElementById('recipient-search');
            const roleFilter = document.getElementById('recipient-role-filter');
            const conversationBaseUrl = @json(url('/'.$currentRole.'/messages').'/');
            let allRecipients = [];
            let recipientsLoaded = false;

            if (!modal) {
                return;
            }

            modal.style.display = 'none';
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName !== 'class') {
                        return;
                    }

                    if (modal.classList.contains('hidden')) {
                        modal.style.display = 'none';
                    } else {
                        modal.style.display = 'flex';
                        if (!recipientsLoaded) {
                            loadRecipients();
                        }
                    }
                });
            });
            observer.observe(modal, { attributes: true });

            async function loadRecipients() {
                try {
                    const response = await fetch(@json(route($messageRecipientsRouteName, $messageRecipientsRouteParams)));
                    const data = await response.json();
                    allRecipients = data.users || [];
                    recipientsLoaded = true;
                    renderRecipients(allRecipients);
                } catch (error) {
                    recipientsLoading.innerHTML = '<p class="text-sm text-red-500">Error loading recipients. Please try again.</p>';
                }
            }

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
                    parent: { bg: 'bg-violet-100', text: 'text-violet-700', badgeBg: 'bg-violet-50', badgeText: 'text-violet-700' },
                    teacher: { bg: 'bg-green-100', text: 'text-green-700', badgeBg: 'bg-green-50', badgeText: 'text-green-700' },
                    admin: { bg: 'bg-amber-100', text: 'text-amber-700', badgeBg: 'bg-amber-50', badgeText: 'text-amber-700' },
                    secretary: { bg: 'bg-slate-100', text: 'text-slate-700', badgeBg: 'bg-slate-50', badgeText: 'text-slate-700' },
                };

                recipientsContainer.innerHTML = recipients.map(user => {
                    const colors = roleColors[user.role] || roleColors.student;
                    const name = String(user.name || 'User');
                    const email = String(user.email || '');
                    const role = String(user.role || '');

                    return `
                        <a
                            href="${conversationBaseUrl}${user.id}"
                            class="recipient-item flex items-center gap-4 border-b p-4 transition-colors hover:bg-gray-50"
                            style="border-color: var(--lumina-border);"
                            data-name="${name.toLowerCase()}"
                            data-email="${email.toLowerCase()}"
                            data-role="${role}"
                        >
                            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full ${colors.bg}">
                                <span class="text-sm font-bold ${colors.text}">${name.charAt(0).toUpperCase()}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-semibold truncate" style="color: var(--lumina-text-primary);">${name}</h4>
                                <p class="text-xs truncate" style="color: var(--lumina-text-muted);">
                                    ${email}
                                    ${role ? `<span class="ml-1 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold ${colors.badgeBg} ${colors.badgeText}">${role.charAt(0).toUpperCase() + role.slice(1)}</span>` : ''}
                                </p>
                            </div>
                            <svg class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    `;
                }).join('');
            }

            function filterRecipients() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedRole = roleFilter.value.toLowerCase();
                const filtered = allRecipients.filter(user => {
                    const name = String(user.name || '').toLowerCase();
                    const email = String(user.email || '').toLowerCase();
                    const role = String(user.role || '').toLowerCase();

                    return (searchTerm === '' || name.includes(searchTerm) || email.includes(searchTerm))
                        && (selectedRole === '' || role === selectedRole);
                });

                renderRecipients(filtered);
            }

            searchInput.addEventListener('input', filterRecipients);
            roleFilter.addEventListener('change', filterRecipients);
        });
    </script>
@endif
