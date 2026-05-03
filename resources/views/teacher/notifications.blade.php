<x-layouts.teacher :title="__('Notifications')" :currentRoute="'notifications'">
    {{-- Page Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
                Notifications
            </h1>
            <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
                Stay updated with messages, attendance alerts, and school announcements.
            </p>
        </div>

        {{-- Mark All as Read Button --}}
        @if($notifications->where('read_at', null)->count() > 0)
            <form method="POST" action="{{ route('teacher.notifications.read-all') }}">
                @csrf
                <button 
                    type="submit" 
                    class="rounded-xl px-4 py-2 text-sm font-semibold transition-all hover:opacity-90 hover:scale-[1.02] active:scale-[0.98] cursor-pointer"
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
        data-live-notification-list
        data-live-notification-read-route-template="{{ route('teacher.notifications.read', ['id' => '__ID__']) }}"
    >
        @forelse($notifications as $notification)
            @php
                $data = (array) $notification->data;
                $notificationType = $data['type'] ?? null;
                $notificationTitle = $data['title'] ?? $data['type'] ?? 'Notification';
                $notificationMessage = $data['message'] ?? $data['body'] ?? $data['text'] ?? 'You have a new notification.';
                $notificationUrl = $data['url'] ?? $data['action_url'] ?? null;
                if (in_array($notificationType, ['teacher_group_assigned', 'teacher_group_removed'], true)) {
                    $notificationUrl = route('role.dashboard', ['role' => 'teacher']);
                }
            @endphp
            <div 
                class="flex items-start gap-4 border-b p-6 transition-colors hover:bg-gray-50 {{ $notification->read_at ? 'opacity-60' : '' }}"
                style="border-color: var(--lumina-border);"
                data-live-notification-item
                data-live-notification-id="{{ $notification->id }}"
            >
                {{-- Notification Icon --}}
                <div 
                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full"
                    style="background-color: {{ $notification->read_at ? '#F1F5F9' : 'var(--lumina-accent-green)' }};"
                >
                    @if($notificationType === 'message')
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: {{ $notification->read_at ? '#64748B' : 'var(--lumina-primary)' }};">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    @elseif($notificationType === 'attendance')
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: {{ $notification->read_at ? '#64748B' : 'var(--lumina-primary)' }};">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    @elseif($notificationType === 'resource')
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: {{ $notification->read_at ? '#64748B' : 'var(--lumina-primary)' }};">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    @else
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: {{ $notification->read_at ? '#64748B' : 'var(--lumina-primary)' }};">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    @endif
                </div>

                {{-- Notification Content --}}
                <div class="flex-1">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <h3 class="text-base font-bold" style="color: var(--lumina-text-primary);">
                                {{ $notificationTitle }}
                            </h3>
                            <p class="mt-1 text-sm" style="color: var(--lumina-text-secondary);">
                                {{ $notificationMessage }}
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
                        @if(!empty($notificationUrl))
                            <a 
                                href="{{ $notificationUrl }}"
                                class="text-xs font-semibold transition-colors hover:underline"
                                style="color: var(--lumina-primary);"
                            >
                                View Details →
                            </a>
                        @endif

                        {{-- Mark as Read Button --}}
                        @if(!$notification->read_at)
                            <form method="POST" action="{{ route('teacher.notifications.read', $notification->id) }}" class="ml-auto">
                                @csrf
                                <button 
                                    type="submit"
                                    class="text-xs font-semibold transition-colors hover:underline cursor-pointer"
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
            <div class="flex flex-col items-center justify-center py-16 text-center" data-live-notification-empty>
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
</x-layouts.teacher>
