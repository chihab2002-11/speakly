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

{{-- Top Navigation Header --}}
<header 
    class="sticky top-0 z-30 flex items-center justify-between px-8 py-4"
    style="background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(6px); box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);"
>
    {{-- Page Title --}}
    <h1 class="text-xl font-black" style="color: #065F46;">
        {{ $pageTitle }}
    </h1>

    {{-- Right Side: Search, Icons, Profile --}}
    <div class="flex items-center gap-6">
        {{-- Search Bar --}}
        <div 
            class="hidden items-center gap-2 rounded-full px-4 py-2 md:flex"
            style="background-color: #F1F5F9; width: 373px;"
        >
            <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #94A3B8;">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
            <input 
                type="text" 
                placeholder="Search data..." 
                class="flex-1 border-none bg-transparent text-sm outline-none placeholder:text-gray-500"
            >
        </div>

        {{-- Action Icons --}}
        <div class="flex items-center gap-4">
            {{-- Notifications --}}
            <a href="{{ route('parent.notifications') }}" class="relative opacity-80 transition-opacity hover:opacity-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #475569;">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                {{-- Notification Badge (only show if unread) --}}
                @if($unreadNotificationsCount > 0)
                    <span class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                        {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                    </span>
                @endif
            </a>

            {{-- Settings --}}
            <a href="{{ route('parent.settings') }}" class="opacity-80 transition-opacity hover:opacity-100" title="Settings">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #475569;">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
            </a>
        </div>

        {{-- Profile Section --}}
        <div class="flex items-center gap-3">
            {{-- User Info --}}
            <div class="hidden flex-col items-end sm:flex">
                <span class="text-xs font-medium" style="color: #0F172A;">
                    {{ $user->name ?? 'Sarah Henderson' }}
                </span>
                <span class="text-[10px] font-medium" style="color: #64748B;">
                    {{ $userRole }}
                </span>
            </div>

            {{-- Profile Avatar --}}
            <div 
                class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-full"
                style="border: 2px solid #D1FAE5;"
            >
                @if($user && $user->avatar)
                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full w-full items-center justify-center" style="background-color: var(--lumina-primary);">
                        <span class="text-xs font-bold text-white">
                            {{ $user ? strtoupper(substr($user->name, 0, 1)) : 'S' }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</header>
