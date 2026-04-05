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
            'routeParams' => ['role' => 'student'],
            'icon' => 'grid',
        ],
        [
            'name' => 'Academic information',
            'route' => 'student.academic',
            'routeParams' => [],
            'icon' => 'academic-cap',
        ],
        [
            'name' => 'Financial Information',
            'route' => 'student.financial',
            'routeParams' => [],
            'icon' => 'credit-card',
        ],
        [
            'name' => 'Learning Materials',
            'route' => 'student.materials',
            'routeParams' => [],
            'icon' => 'folder',
        ],
        [
            'name' => 'Messages',
            'route' => 'role.messages.index',
            'routeParams' => ['role' => 'student'],
            'icon' => 'chat',
        ],
    ];
@endphp

{{-- Desktop Sidebar --}}
<aside 
    id="student-sidebar"
    class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col border-r transition-transform duration-300 lg:relative lg:translate-x-0"
    style="background-color: var(--lumina-sidebar-bg); border-color: var(--lumina-border);"
>
    {{-- Logo Section --}}
    <div class="flex flex-col gap-12 p-6">
        <div class="flex items-center gap-3">
            {{-- Logo Icon --}}
            <div class="flex h-12 w-12 items-center justify-center rounded-lg" style="background: linear-gradient(0deg, #2D8C5E, #2D8C5E);">
                <svg class="h-5 w-6 text-white" viewBox="0 0 22 18" fill="currentColor">
                    <path d="M11 0L0 6L4 8.18V14.18L11 18L18 14.18V8.18L20 7.09V14H22V6L11 0ZM17.82 6L11 9.72L4.18 6L11 2.28L17.82 6ZM16 12.99L11 15.72L6 12.99V9.27L11 12L16 9.27V12.99Z"/>
                </svg>
            </div>
            {{-- Academy Name --}}
            <div class="flex flex-col">
                <h1 class="font-calibri text-2xl font-bold leading-tight" style="color: var(--lumina-primary-dark);">
                    Lumina Academy
                </h1>
                <span class="text-xs tracking-tight" style="color: var(--lumina-text-muted);">
                    Student Portal
                </span>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex flex-1 flex-col gap-2 px-6">
        @foreach($navItems as $item)
            @php
                $isActive = $currentRoute === $item['route'] || request()->routeIs($item['route']);
            @endphp
            <a 
                href="{{ Route::has($item['route']) ? route($item['route'], $item['routeParams'] ?? []) : '#' }}"
                class="flex items-center gap-4 rounded-l-2xl px-4 py-3 text-sm font-medium transition-all duration-200"
                style="{{ $isActive 
                    ? 'background-color: var(--lumina-bg-section); color: var(--lumina-primary-dark); font-weight: 700;' 
                    : 'color: var(--lumina-primary-dark);' }}"
                wire:navigate
            >
                {{-- Icon --}}
                <span class="flex h-5 w-5 items-center justify-center">
                    @switch($item['icon'])
                        @case('grid')
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4h4v4H4V4zm6 0h4v4h-4V4zm6 0h4v4h-4V4zM4 10h4v4H4v-4zm6 0h4v4h-4v-4zm6 0h4v4h-4v-4zM4 16h4v4H4v-4zm6 0h4v4h-4v-4zm6 0h4v4h-4v-4z"/>
                            </svg>
                            @break
                        @case('academic-cap')
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z"/>
                            </svg>
                            @break
                        @case('credit-card')
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                            </svg>
                            @break
                        @case('folder')
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M10 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2h-8l-2-2z"/>
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
        @endforeach
    </nav>

    {{-- Bottom Section: Support & Logout --}}
    <div class="border-t p-6" style="border-color: rgba(226, 232, 240, 0.5);">
        <div class="flex flex-col gap-2">
            {{-- Support --}}
            <a 
                href="#" 
                class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold transition-all duration-200 hover:bg-white/50 cursor-pointer"
                style="color: var(--lumina-primary-dark);"
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
                    class="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold transition-all duration-200 hover:bg-red-50 cursor-pointer"
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
