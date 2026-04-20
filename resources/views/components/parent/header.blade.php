@props([
    'user' => null,
    'pageTitle' => 'Dashboard'
])

@php
    $user = $user ?? auth()->user();
    
    // Get user's actual role
    $userRole = 'Parent'; // Default
    try {
        if ($user->hasRole('admin')) {
            $userRole = 'Admin';
        } elseif ($user->hasRole('teacher')) {
            $userRole = 'Teacher';
        } elseif ($user->hasRole('parent')) {
            $userRole = 'Parent';
        } elseif ($user->hasRole('secretary')) {
            $userRole = 'Secretary';
        } elseif ($user->hasRole('student')) {
            $userRole = 'Student';
        }
    } catch (\Exception $e) {
        // Fallback to requested_role
        $userRole = ucfirst($user->requested_role ?? 'Parent');
    }
    
    // Get unread notifications count
    $unreadNotificationsCount = $user ? $user->unreadNotifications()->count() : 0;
@endphp

<header 
    class="sticky top-0 z-30 flex items-center justify-between border-b px-4 py-3 md:px-8"
    style="background: rgba(255, 255, 255, 0.85); border-color: rgba(226, 232, 240, 0.5); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);"
>
    {{-- Left Side: Mobile Menu Toggle --}}
    <div class="flex items-center gap-4">
        <button 
            onclick="toggleSidebar()"
            class="flex h-9 w-9 items-center justify-center rounded-full transition-all duration-200 hover:bg-gray-100 active:scale-95 lg:hidden"
            aria-label="Toggle sidebar"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </div>

    {{-- Right Side: Actions & User Profile --}}
    <div class="flex items-center gap-4 md:gap-6">
        <div class="flex items-center gap-2 md:gap-4">
            {{-- Notifications Button --}}
            <a 
                href="{{ Route::has('parent.notifications') ? route('parent.notifications') : '#' }}" 
                class="relative flex h-9 w-9 items-center justify-center rounded-full transition-all duration-200 hover:bg-gray-100 active:scale-95"
                wire:navigate
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                @if($unreadNotificationsCount > 0)
                    <span class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold text-white animate-pulse" style="background-color: var(--lumina-accent-red);">
                        {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                    </span>
                @endif
            </a>

            {{-- Help Button --}}
            <button class="flex h-9 w-9 items-center justify-center rounded-full transition-all duration-200 hover:bg-gray-100 active:scale-95">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>
            
            {{-- Settings Button --}}
            <a 
                href="{{ Route::has('parent.settings') ? route('parent.settings') : '#' }}" 
                class="flex h-9 w-9 items-center justify-center rounded-full transition-all duration-200 hover:bg-gray-100 active:scale-95"
                wire:navigate
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </a>
        </div>

        <div class="hidden h-8 w-px md:block" style="background-color: var(--lumina-border);"></div>

        <a 
            href="{{ Route::has('parent.settings') ? route('parent.settings') : '#' }}" 
            class="flex items-center gap-3 rounded-xl p-2 transition-all duration-200 hover:bg-gray-50"
            wire:navigate
        >
            <div class="hidden flex-col items-end md:flex">
                <span class="text-xs font-medium" style="color: #0F172A;">
                    {{ $user->name ?? 'Sarah Henderson' }}
                </span>
                <span class="text-[10px] font-medium" style="color: var(--lumina-text-muted);">
                    {{ $userRole }}
                </span>
            </div>
            
            <div class="relative h-10 w-10 overflow-hidden rounded-full border-2 transition-all duration-200 hover:border-emerald-300" style="border-color: var(--lumina-border);">
                @if($user && $user->avatar)
                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full w-full items-center justify-center text-sm font-semibold" style="background-color: var(--lumina-accent-green-light); color: var(--lumina-accent-green-dark);">
                        {{ $user ? strtoupper(substr($user->name, 0, 1)) : 'S' }}
                    </div>
                @endif
            </div>
        </a>
    </div>
</header>
