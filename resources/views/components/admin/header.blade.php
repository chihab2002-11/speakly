@props([
    'user' => null,
])

@php
    $user = $user ?? auth()->user();
    $unreadNotificationsCount = $user ? $user->unreadNotifications()->count() : 0;
@endphp

<header class="sticky top-0 z-30 flex items-center justify-between gap-4 border-b px-4 py-3 md:px-8" style="background: rgba(255, 255, 255, 0.85); border-color: rgba(226, 232, 240, 0.5); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);">
    <div class="flex flex-1 items-center gap-3">
        <button
            type="button"
            onclick="toggleAdminSidebar()"
            class="inline-flex h-9 w-9 items-center justify-center rounded-full transition-colors hover:bg-gray-100 lg:hidden"
            aria-label="Toggle admin sidebar"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748B;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>

        <div class="relative w-full max-w-3xl">
            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2" style="color: #94A3B8;">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            </span>
            <input
                type="text"
                placeholder="Search students, faculty, or reports..."
                class="w-full rounded-full border-0 py-2 pl-11 pr-4 text-sm font-medium outline-none"
                style="background-color: #F4F2FC; color: #6B7280;"
            >
        </div>
    </div>

    <div class="flex items-center gap-3 md:gap-5">
        <a href="{{ route('admin.notifications') }}" class="relative inline-flex h-9 w-9 items-center justify-center rounded-full transition-all duration-200 hover:bg-gray-100 active:scale-95">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748B;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            @if($unreadNotificationsCount > 0)
                <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">{{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}</span>
            @endif
        </a>

        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full transition-all duration-200 hover:bg-gray-100 active:scale-95">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748B;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </button>

        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full transition-all duration-200 hover:bg-gray-100 active:scale-95">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748B;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </button>

        <div class="hidden h-8 w-px md:block" style="background-color: #E2E8F0;"></div>

        <div class="flex items-center gap-3">
            <div class="hidden items-end text-right md:flex md:flex-col">
                <span class="text-xs font-bold" style="color: #0F172A;">{{ $user->name ?? 'Admin User' }}</span>
                <span class="text-[10px] font-medium" style="color: #64748B;">Super Administrator</span>
            </div>
            <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full border" style="border-color: #E2E8F0; background-color: #2D8C5E; color: white;">
                {{ $user ? strtoupper(substr($user->name, 0, 1)) : 'A' }}
            </div>
        </div>
    </div>
</header>
