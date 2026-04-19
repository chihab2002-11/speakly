@props([
    'user' => null,
    'currentRoute' => 'role.dashboard',
])

@php
    $user = $user ?? auth()->user();
    $sharedRole = $user && $user->hasRole('admin') ? 'admin' : 'secretary';

    $navItems = [
        [
            'name' => 'Dashboard',
            'route' => 'role.dashboard',
            'routeParams' => ['role' => $sharedRole],
            'icon' => 'grid',
            'activeMatch' => fn () => request()->routeIs('role.dashboard') && request()->route('role') === $sharedRole,
        ],
        [
            'name' => 'Messages',
            'route' => 'role.messages.index',
            'routeParams' => ['role' => $sharedRole],
            'icon' => 'chat',
            'activeMatch' => fn () => request()->routeIs('role.messages.*') && request()->route('role') === $sharedRole,
        ],
    ];

    if ($user?->can('registrations.manage')) {
        $navItems[] = [
            'name' => 'Registrations',
            'route' => 'secretary.registrations',
            'routeParams' => [],
            'icon' => 'user-plus',
            'activeMatch' => fn () => request()->routeIs('secretary.registrations*'),
        ];
    }

    if ($user?->can('payments.manage')) {
        $navItems[] = [
            'name' => 'Student Payments',
            'route' => 'secretary.payments',
            'routeParams' => [],
            'icon' => 'wallet',
            'activeMatch' => fn () => request()->routeIs('secretary.payments'),
        ];
    }

    if ($user?->can('groups.manage')) {
        $navItems[] = [
            'name' => 'Manage Groups',
            'route' => 'secretary.groups',
            'routeParams' => [],
            'icon' => 'layers',
            'activeMatch' => fn () => request()->routeIs('secretary.groups') || request()->routeIs('secretary.timetable.index'),
        ];
    }

    if ($user?->can('accounts.manage')) {
        $navItems[] = [
            'name' => 'Manage Accounts',
            'route' => 'secretary.accounts',
            'routeParams' => [],
            'icon' => 'account',
            'activeMatch' => fn () => request()->routeIs('secretary.accounts'),
        ];
    }

    if ($user?->can('announcements.publish')) {
        $navItems[] = [
            'name' => 'Publish Notifications',
            'route' => 'secretary.publish-notifications',
            'routeParams' => [],
            'icon' => 'megaphone',
            'activeMatch' => fn () => request()->routeIs('secretary.publish-notifications*'),
        ];
    }
@endphp

<aside
    id="secretary-sidebar"
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
                    Secretary Portal
                </span>
            </div>
        </div>
    </div>

    <nav class="flex flex-1 flex-col gap-1 pl-4 pr-0">
        @foreach ($navItems as $item)
            @php
                $isActive = $item['activeMatch']();
            @endphp

            <div class="relative">
                @if($isActive)
                    <div class="absolute -top-4 right-0 h-4 w-4 overflow-hidden">
                        <div class="absolute bottom-0 right-0 h-8 w-8 rounded-br-2xl" style="box-shadow: 8px 8px 0 var(--lumina-bg-section);"></div>
                    </div>
                @endif

                @if($item['route'])
                    <a
                        href="{{ route($item['route'], $item['routeParams']) }}"
                        class="group relative flex items-center gap-4 px-4 py-3 text-sm transition-all duration-200 {{ $isActive ? 'rounded-l-2xl bg-[#F3F8F5] font-bold' : 'mr-4 rounded-xl font-medium hover:bg-white/30' }}"
                        style="color: var(--lumina-primary-dark);"
                    >
                        <span class="flex h-5 w-5 items-center justify-center transition-transform duration-200 {{ $isActive ? '' : 'group-hover:scale-110' }}">
                            @switch($item['icon'])
                                @case('grid')
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                    @break
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
                                @case('megaphone')
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882a1 1 0 01.993-.883H13a1 1 0 01.928.629l1.367 3.417 4.374 1.75A1 1 0 0120 11.741v.518a1 1 0 01-.331.742l-4.374 3.937-1.367 3.417A1 1 0 0113 21h-1.007A1 1 0 0111 20.118V5.882zM8 10l2 4M4 9l2 6"/></svg>
                                    @break
                                @case('chat')
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M21 6h-2v9H6v2c0 .55.45 1 1 1h11l4 4V7c0-.55-.45-1-1-1zm-4 6V3c0-.55-.45-1-1-1H3c-.55 0-1 .45-1 1v14l4-4h10c.55 0 1-.45 1-1z"/></svg>
                                    @break
                                @case('settings')
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317a1.724 1.724 0 013.35 0 1.724 1.724 0 002.573 1.066 1.724 1.724 0 012.364.997 1.724 1.724 0 001.65 1.233 1.724 1.724 0 011.675 1.675 1.724 1.724 0 01-1.233 1.65 1.724 1.724 0 00-.997 2.364 1.724 1.724 0 01-1.066 2.573 1.724 1.724 0 00-1.232 1.65 1.724 1.724 0 01-1.675 1.675 1.724 1.724 0 01-1.65-1.233 1.724 1.724 0 00-2.364-.997 1.724 1.724 0 01-2.573-1.066 1.724 1.724 0 00-1.65-1.232 1.724 1.724 0 01-1.675-1.675 1.724 1.724 0 011.233-1.65 1.724 1.724 0 00.997-2.364 1.724 1.724 0 011.066-2.573 1.724 1.724 0 001.232-1.65z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15a3 3 0 100-6 3 3 0 000 6z"/></svg>
                                    @break
                            @endswitch
                        </span>
                        <span class="tracking-tight">{{ $item['name'] }}</span>
                    </a>
                @endif

                @if($isActive)
                    <div class="absolute -bottom-4 right-0 h-4 w-4 overflow-hidden">
                        <div class="absolute top-0 right-0 h-8 w-8 rounded-tr-2xl" style="box-shadow: 8px -8px 0 var(--lumina-bg-section);"></div>
                    </div>
                @endif
            </div>
        @endforeach
    </nav>

    <div class="border-t p-6" style="border-color: rgba(226, 232, 240, 0.5);">
        <div class="flex flex-col gap-2">
            <a
                href="mailto:support@speakly.com"
                class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold transition-all duration-200 hover:bg-white/50"
                style="color: var(--lumina-text-muted);"
            >
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/>
                </svg>
                <span>Support</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="btn-press flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold transition-all duration-200 hover:bg-red-50"
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
