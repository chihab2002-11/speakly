<x-layouts.student :title="__('Notifications')" :currentRoute="'notifications'">
    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
                Notifications
            </h1>
            <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
                Stay updated with your latest messages and activities.
            </p>
        </div>

        {{-- Mark All as Read Button --}}
        @if($notifications->where('read_at', null)->count() > 0)
            <form method="POST" action="{{ route('student.notifications.read-all') }}">
                @csrf
                <button 
                    type="submit" 
                    class="rounded-xl px-4 py-2 text-sm font-semibold transition-all hover:opacity-90"
                    style="background-color: var(--lumina-primary); color: white;"
                >
                    Mark All as Read
                </button>
            </form>
        @endif
    </div>

    {{-- Notifications Container --}}
    <div 
        class="overflow-hidden rounded-3xl border"
        style="background-color: #FFFFFF; border-color: var(--lumina-border-light);"
    >
        @forelse($notifications as $notification)
            @php
                $isRead = (bool) $notification->read_at;
                $iconBgClass = $isRead ? 'bg-slate-100' : 'bg-emerald-100';
                $iconColorClass = $isRead ? 'text-slate-500' : 'text-emerald-700';
            @endphp
            <div 
                class="flex items-start gap-4 border-b p-6 transition-colors hover:bg-gray-50 {{ $notification->read_at ? 'opacity-60' : '' }}"
                style="border-color: var(--lumina-border);"
            >
                {{-- Notification Icon --}}
                <div 
                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full {{ $iconBgClass }}"
                >
                    @if(isset($notification->data['type']) && $notification->data['type'] === 'message')
                        <svg class="h-6 w-6 {{ $iconColorClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    @else
                        <svg class="h-6 w-6 {{ $iconColorClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    @endif
                </div>

                {{-- Notification Content --}}
                <div class="flex-1">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <h3 class="text-base font-bold" style="color: var(--lumina-text-primary);">
                                {{ $notification->data['title'] ?? 'Notification' }}
                            </h3>
                            <p class="mt-1 text-sm" style="color: var(--lumina-text-secondary);">
                                {{ $notification->data['message'] ?? 'You have a new notification.' }}
                            </p>
                        </div>

                        {{-- Unread Badge --}}
                        @if(!$notification->read_at)
                            <div class="flex-shrink-0">
                                <span class="flex h-2 w-2 rounded-full bg-red-500"></span>
                            </div>
                        @endif
                    </div>

                    {{-- Timestamp and Actions --}}
                    <div class="mt-3 flex items-center gap-4">
                        <span class="text-xs" style="color: var(--lumina-text-muted);">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>

                        {{-- Action Button (if URL provided) --}}
                        @if(!empty($notification->data['url']))
                            <a 
                                href="{{ $notification->data['url'] }}"
                                class="text-xs font-semibold transition-colors hover:underline"
                                style="color: var(--lumina-primary);"
                            >
                                View Message →
                            </a>
                        @endif

                        {{-- Mark as Read Button --}}
                        @if(!$notification->read_at)
                            <form method="POST" action="{{ route('student.notifications.read', $notification->id) }}" class="ml-auto">
                                @csrf
                                <button 
                                    type="submit"
                                    class="text-xs font-semibold transition-colors hover:underline"
                                    style="color: var(--lumina-text-muted);"
                                >
                                    Mark as Read
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div 
                    class="mb-4 flex h-20 w-20 items-center justify-center rounded-full"
                    style="background-color: var(--lumina-bg-card);"
                >
                    <svg class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                    No Notifications Yet
                </h3>
                <p class="mt-2 text-sm" style="color: var(--lumina-text-muted);">
                    When you receive notifications, they'll appear here.
                </p>
            </div>
        @endforelse
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div 
            class="mt-4 rounded-xl border p-4"
            style="background-color: #D1FAE5; border-color: #A7F3D0; color: #065F46;"
        >
            <p class="text-sm font-semibold">{{ session('success') }}</p>
        </div>
    @endif
</x-layouts.student>
