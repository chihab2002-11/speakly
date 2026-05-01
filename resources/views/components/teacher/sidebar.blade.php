@props([
    'user' => null,
    'currentRoute' => 'dashboard'
])

@php
    $user = $user ?? auth()->user();
    
    $navItems = [
        [
            'name' => 'Dashboard',
            'route' => 'role.dashboard',
            'routeParams' => ['role' => 'teacher'],
            'icon' => 'grid',
        ],
        [
            'name' => 'Manage Attendance',
            'route' => 'teacher.attendance',
            'routeParams' => [],
            'icon' => 'calendar-check',
        ],
        [
            'name' => 'Teaching Resources',
            'route' => 'teacher.resources',
            'routeParams' => [],
            'icon' => 'folder-open',
        ],
        [
            'name' => 'Messages',
            'route' => 'role.messages.index',
            'routeParams' => ['role' => 'teacher'],
            'icon' => 'chat',
        ],
    ];
@endphp

{{-- Desktop Sidebar --}}
<aside 
    id="teacher-sidebar"
    class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col transition-transform duration-300 ease-in-out lg:translate-x-0"
    style="background-color: var(--lumina-sidebar-bg);"
>
    {{-- Logo Section --}}
    <div class="flex flex-col gap-12 p-6">
        <div class="flex items-center gap-3">
            {{-- Logo Icon --}}
            <div class="flex h-12 w-12 items-center justify-center rounded-lg shadow-md transition-transform duration-200 hover:scale-105" style="background: linear-gradient(135deg, #2D8C5E 0%, #006A41 100%);">
                <svg class="h-5 w-6 text-white" viewBox="0 0 22 18" fill="currentColor">
                    <path d="M11 0L0 6L4 8.18V14.18L11 18L18 14.18V8.18L20 7.09V14H22V6L11 0ZM17.82 6L11 9.72L4.18 6L11 2.28L17.82 6ZM16 12.99L11 15.72L6 12.99V9.27L11 12L16 9.27V12.99Z"/>
                </svg>
            </div>
            {{-- Academy Name --}}
            <div class="flex flex-col">
                <h1 class="font-calibri text-2xl font-bold leading-tight" style="color: var(--lumina-primary-dark);">
                    Lumina Academy
                </h1>
                <span class="text-xs font-bold tracking-tight" style="color: var(--lumina-text-muted);">
                    Teacher Portal
                </span>
            </div>
        </div>
    </div>

{{-- Navigation --}}
<nav class="flex flex-1 flex-col gap-1 pl-4 pr-0">
    @foreach($navItems as $item)
        @php
            $isActive = $currentRoute === $item['route'] || request()->routeIs($item['route']);
        @endphp
        <div class="relative">
            {{-- Top curve connector for active item --}}
            @if($isActive)
                <div class="absolute -top-4 right-0 h-4 w-4 overflow-hidden">
                    <div class="absolute bottom-0 right-0 h-8 w-8 rounded-br-2xl" style="box-shadow: 8px 8px 0 var(--lumina-bg-section);"></div>
                </div>
            @endif
            
            <a 
                href="{{ Route::has($item['route']) ? route($item['route'], $item['routeParams'] ?? []) : '#' }}"
                class="relative flex items-center gap-4 px-4 py-3 text-sm font-medium transition-all duration-200 {{ $isActive ? 'rounded-l-2xl' : 'rounded-xl mr-4 hover:bg-white/30' }}"
                @if($isActive)
                    style="background-color: var(--lumina-bg-section); color: var(--lumina-primary-dark); font-weight: 700;"
                @else
                    style="color: var(--lumina-primary-dark);"
                @endif
                wire:navigate
            >
                {{-- Icon --}}
                <span class="flex h-5 w-5 items-center justify-center transition-transform duration-200 {{ $isActive ? '' : 'group-hover:scale-110' }}">
                    @switch($item['icon'])
                        @case('grid')
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                            @break
                        @case('calendar-check')
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm-8 4H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/>
                            </svg>
                            @break
                        @case('folder-open')
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20 6h-8l-2-2H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 12H4V8h16v10z"/>
                            </svg>
                            @break
                        @case('chat')
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M21 6h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1zm-4 6V3c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v14l4-4h10c.55 0 1-.45 1-1z"/>
                            </svg>
                            @break
                    @endswitch
                </span>
                {{-- Label --}}
                <span class="tracking-tight">{{ $item['name'] }}</span>
            </a>
            
            {{-- Bottom curve connector for active item --}}
            @if($isActive)
                <div class="absolute -bottom-4 right-0 h-4 w-4 overflow-hidden">
                    <div class="absolute top-0 right-0 h-8 w-8 rounded-tr-2xl" style="box-shadow: 8px -8px 0 var(--lumina-bg-section);"></div>
                </div>
            @endif
        </div>
    @endforeach
</nav>

    {{-- Bottom Section: Support & Logout --}}
    <div class="border-t p-6" style="border-color: rgba(226, 232, 240, 0.5);">
        <div class="flex flex-col gap-2">
            {{-- Support --}}
            <a 
                href="{{ route('support') }}"
                class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold transition-all duration-200 hover:bg-white/50"
                style="color: var(--lumina-text-muted);"
            >
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/>
                </svg>
                <span>Support</span>
            </a>
            
            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button 
                    type="submit" 
                    class="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold transition-all duration-200 hover:bg-red-50 btn-press"
                    style="color: var(--lumina-accent-red);"
                >
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/>
                    </svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>
