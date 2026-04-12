@props([
    'currentRoute' => 'role.dashboard',
])

@php
    $navItems = [
        ['name' => 'Dashboard', 'route' => 'role.dashboard', 'routeParams' => ['role' => 'admin'], 'icon' => 'grid'],
        ['name' => 'Manage employees', 'route' => null, 'routeParams' => [], 'icon' => 'users'],
        ['name' => 'Academic Catalog', 'route' => null, 'routeParams' => [], 'icon' => 'book'],
        ['name' => 'Manage Payments', 'route' => null, 'routeParams' => [], 'icon' => 'credit-card'],
        ['name' => 'Manage Schedule', 'route' => null, 'routeParams' => [], 'icon' => 'calendar'],
        ['name' => 'Room Allocation', 'route' => null, 'routeParams' => [], 'icon' => 'building'],
    ];

    $secretaryItems = [
        ['name' => 'Student Registration', 'icon' => 'user-plus'],
        ['name' => 'Student Payments', 'icon' => 'wallet'],
        ['name' => 'Manage Groups', 'icon' => 'layers'],
        ['name' => 'Manage Accounts', 'icon' => 'account'],
        ['name' => 'Publish Notifications', 'icon' => 'bell'],
    ];
@endphp

<aside
    id="admin-sidebar"
    class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col transition-transform duration-300 ease-in-out lg:translate-x-0"
    style="background-color: var(--lumina-sidebar-bg);"
>
    <div class="flex flex-col gap-12 p-6">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg shadow-md transition-transform duration-200 hover:scale-105" style="background: linear-gradient(135deg, #2D8C5E 0%, #006A41 100%);">
                <svg class="h-5 w-6 text-white" viewBox="0 0 22 18" fill="currentColor">
                    <path d="M11 0L0 6L4 8.18V14.18L11 18L18 14.18V8.18L20 7.09V14H22V6L11 0ZM17.82 6L11 9.72L4.18 6L11 2.28L17.82 6ZM16 12.99L11 15.72L6 12.99V9.27L11 12L16 9.27V12.99Z"/>
                </svg>
            </div>
            <div class="flex flex-col">
                <h1 class="font-calibri text-2xl font-bold leading-tight" style="color: var(--lumina-primary-dark);">
                    Lumina Academy
                </h1>
                <span class="text-xs font-bold tracking-tight" style="color: var(--lumina-text-muted);">
                    School Management
                </span>
            </div>
        </div>
    </div>

    <nav class="flex flex-1 flex-col gap-1 pl-4 pr-0">
        @foreach($navItems as $item)
            @php
                $isActive = $item['route'] !== null
                    ? request()->routeIs($item['route']) && request()->route('role') === 'admin'
                    : false;
            @endphp

            <div class="relative">
                @if($isActive)
                    <div class="absolute -top-4 right-0 h-4 w-4 overflow-hidden">
                        <div class="absolute bottom-0 right-0 h-8 w-8 rounded-br-2xl" style="box-shadow: 8px 8px 0 var(--lumina-bg-main);"></div>
                    </div>
                @endif

                @if($item['route'])
                    <a
                        href="{{ route($item['route'], $item['routeParams']) }}"
                        class="group relative flex items-center gap-4 px-4 py-3 text-sm transition-all duration-200 {{ $isActive ? 'rounded-l-2xl bg-[#F3F8F5] font-bold' : 'rounded-xl mr-4 hover:bg-white/30 font-medium' }}"
                        style="color: var(--lumina-primary-dark);"
                    >
                        <span class="flex h-5 w-5 items-center justify-center transition-transform duration-200 {{ $isActive ? '' : 'group-hover:scale-110' }}">
                            @switch($item['icon'])
                                @case('grid')
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                    @break
                                @case('users')
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2a3 3 0 00-5-2.83M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2a3 3 0 015-2.83m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    @break
                                @case('book')
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    @break
                                @case('credit-card')
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
                                    @break
                                @case('calendar')
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    @break
                                @case('building')
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l8-4v18m6 0V11l-6-4"/></svg>
                                    @break
                            @endswitch
                        </span>
                        <span class="tracking-tight">{{ $item['name'] }}</span>
                    </a>
                @else
                    <button
                        type="button"
                        class="group relative mr-4 flex items-center gap-4 rounded-xl px-4 py-3 text-left text-sm font-medium transition-all duration-200 hover:bg-white/30"
                        style="color: var(--lumina-primary-dark);"
                    >
                        <span class="flex h-5 w-5 items-center justify-center transition-transform duration-200 group-hover:scale-110">
                            @switch($item['icon'])
                                @case('users')
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2a3 3 0 00-5-2.83M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2a3 3 0 015-2.83m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    @break
                                @case('book')
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    @break
                                @case('credit-card')
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></svg>
                                    @break
                                @case('calendar')
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    @break
                                @case('building')
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l8-4v18m6 0V11l-6-4"/></svg>
                                    @break
                            @endswitch
                        </span>
                        <span class="tracking-tight">{{ $item['name'] }}</span>
                    </button>
                @endif

                @if($isActive)
                    <div class="absolute -bottom-4 right-0 h-4 w-4 overflow-hidden">
                        <div class="absolute top-0 right-0 h-8 w-8 rounded-tr-2xl" style="box-shadow: 8px -8px 0 var(--lumina-bg-main);"></div>
                    </div>
                @endif
            </div>
        @endforeach

        <div class="mt-8 px-4 text-xs font-bold uppercase tracking-[1.1px]" style="color: #64748B;">Secretary Role</div>
        @foreach($secretaryItems as $item)
            <button type="button" class="group relative mr-4 flex items-center gap-4 rounded-xl px-4 py-3 text-left text-sm font-medium transition-all duration-200 hover:bg-white/30" style="color: var(--lumina-primary-dark);">
                <span class="flex h-5 w-5 items-center justify-center transition-transform duration-200 group-hover:scale-110">
                    @switch($item['icon'])
                        @case('user-plus')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v6m3-3h-6m-6 8a6 6 0 1112 0H3zm6-10a4 4 0 100-8 4 4 0 000 8z"/></svg>
                            @break
                        @case('wallet')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                            @break
                        @case('layers')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7l8-4 8 4-8 4-8-4zm0 5l8 4 8-4m-16 5l8 4 8-4"/></svg>
                            @break
                        @case('account')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.398 0 4.655.593 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            @break
                        @case('bell')
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            @break
                    @endswitch
                </span>
                <span class="tracking-tight">{{ $item['name'] }}</span>
            </button>
        @endforeach
    </nav>

    <div class="border-t p-6" style="border-color: rgba(226, 232, 240, 0.5);">
        <div class="flex flex-col gap-2">
            <a href="mailto:admin@speakly.com" class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold transition-all duration-200 hover:bg-white/50" style="color: var(--lumina-text-muted);">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Support</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold transition-all duration-200 hover:bg-red-50" style="color: var(--lumina-accent-red);">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>
