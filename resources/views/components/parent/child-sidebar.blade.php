@props([
    'parent' => null,
    'child' => null,
    'children' => [],
    'currentRoute' => 'dashboard',
])

@php
    $parent = $parent ?? auth()->user();
    $child = $child ?? null;
    $children = collect($children ?? [])->values();

    $childNavItems = [
        [
            'name' => 'Dashboard',
            'route' => 'parent.child.dashboard',
            'routeParams' => ['child' => $child['id'] ?? 0],
            'icon' => 'grid',
            'active' => $currentRoute === 'dashboard',
        ],
        [
            'name' => 'Academic information',
            'route' => 'parent.child.academic',
            'routeParams' => ['child' => $child['id'] ?? 0],
            'icon' => 'academic-cap',
            'active' => $currentRoute === 'academic',
        ],
        [
            'name' => 'Learning Materials',
            'route' => 'parent.child.materials',
            'routeParams' => ['child' => $child['id'] ?? 0],
            'icon' => 'folder',
            'active' => $currentRoute === 'materials',
        ],
        [
            'name' => 'Messages',
            'route' => 'parent.child.messages',
            'routeParams' => ['child' => $child['id'] ?? 0],
            'icon' => 'chat',
            'active' => request()->routeIs('parent.child.messages*'),
        ],
        [
            'name' => 'Notifications',
            'route' => 'parent.child.notifications',
            'routeParams' => ['child' => $child['id'] ?? 0],
            'icon' => 'bell',
            'active' => $currentRoute === 'notifications',
        ],
    ];
@endphp

<aside
    id="parent-child-sidebar"
    class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col transition-transform duration-300 ease-in-out lg:translate-x-0"
    style="background-color: var(--lumina-sidebar-bg);"
>
    <div class="flex flex-col gap-5 p-6">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg shadow-md" style="background: linear-gradient(135deg, #2D8C5E 0%, #006A41 100%);">
                <svg class="h-5 w-6 text-white" viewBox="0 0 22 18" fill="currentColor">
                    <path d="M11 0L0 6L4 8.18V14.18L11 18L18 14.18V8.18L20 7.09V14H22V6L11 0ZM17.82 6L11 9.72L4.18 6L11 2.28L17.82 6ZM16 12.99L11 15.72L6 12.99V9.27L11 12L16 9.27V12.99Z"/>
                </svg>
            </div>
            <div class="flex flex-col">
                <h1 class="font-calibri text-2xl font-bold leading-tight" style="color: var(--lumina-primary-dark);">Lumina Academy</h1>
                <span class="text-xs font-bold tracking-tight" style="color: var(--lumina-text-muted);">Child Portal</span>
            </div>
        </div>

        <div class="rounded-xl border bg-white/70 p-3" style="border-color: rgba(226,232,240,.7);">
            <p class="mb-2 text-[10px] font-black uppercase tracking-wider" style="color: var(--lumina-text-muted);">Viewing Child</p>
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg" style="background-color: var(--lumina-accent-green-bg); color: #034C3C;">
                    <span class="text-sm font-black">{{ $child['initials'] ?? 'C' }}</span>
                </div>
                <div>
                    <p class="text-sm font-bold" style="color: var(--lumina-text-primary);">{{ $child['name'] ?? 'Child' }}</p>
                    <p class="text-[11px]" style="color: var(--lumina-text-muted);">Parent: {{ $parent->name ?? 'Parent' }}</p>
                </div>
            </div>
        </div>

        <div class="relative">
            <button
                id="child-portal-selector-button"
                onclick="toggleChildPortalSelector()"
                class="flex w-full items-center justify-between rounded-xl border p-3 transition-all duration-200 hover:shadow-md"
                style="background-color: #FFFFFF; border-color: #E2E8F0;"
            >
                <span class="text-sm font-semibold" style="color: var(--lumina-text-primary);">Switch Child Account</span>
                <svg id="child-portal-selector-icon" class="h-3 w-3 transition-transform duration-200" viewBox="0 0 12 8" fill="none" style="color: #94A3B8;">
                    <path d="M1 1.5L6 6.5L11 1.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>

            <div id="child-portal-selector-dropdown" class="absolute left-0 right-0 top-full z-30 mt-2 hidden flex-col gap-1 rounded-xl border p-2 shadow-lg" style="background-color: #FFFFFF; border-color: #E2E8F0;">
                @foreach($children as $portalChild)
                    <a href="{{ route('parent.child.dashboard', ['child' => $portalChild['id']]) }}" class="flex items-center gap-3 rounded-lg p-2 transition-colors hover:bg-gray-50">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg" style="background-color: var(--lumina-accent-green-bg); color: #034C3C;">
                            <span class="text-xs font-black">{{ $portalChild['initials'] ?? substr($portalChild['name'], 0, 1) }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-sm font-semibold" style="color: var(--lumina-text-primary);">{{ $portalChild['name'] }}</span>
                            <span class="text-[10px]" style="color: var(--lumina-text-muted);">{{ $portalChild['grade'] ?? 'Student' }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <nav class="flex flex-1 flex-col gap-1 pl-4 pr-0">
        @foreach($childNavItems as $item)
            <div class="relative">
                @if($item['active'])
                    <div class="absolute -top-4 right-0 h-4 w-4 overflow-hidden">
                        <div class="absolute bottom-0 right-0 h-8 w-8 rounded-br-2xl" style="box-shadow: 8px 8px 0 var(--lumina-bg-section);"></div>
                    </div>
                @endif

                <a
                    href="{{ route($item['route'], $item['routeParams']) }}"
                    class="group relative sidebar-link flex items-center gap-4 px-4 py-3 text-sm transition-all duration-200 {{ $item['active'] ? 'rounded-l-2xl font-bold' : 'rounded-xl mr-4 hover:bg-white/30 font-medium' }}"
                    @if($item['active'])
                        style="background-color: var(--lumina-bg-section); color: var(--lumina-primary-dark);"
                    @else
                        style="color: var(--lumina-primary-dark);"
                    @endif
                    wire:navigate
                >
                    <span class="flex h-5 w-5 items-center justify-center {{ $item['active'] ? '' : 'group-hover:scale-110 transition-transform duration-200' }}">
                        @switch($item['icon'])
                            @case('grid')
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                @break
                            @case('academic-cap')
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3z"/></svg>
                                @break
                            @case('folder')
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M10 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V8a2 2 0 00-2-2h-8l-2-2z"/></svg>
                                @break
                            @case('chat')
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M21 6h-2v9H6v2a1 1 0 001 1h11l4 4V7a1 1 0 00-1-1zm-4 6V3a1 1 0 00-1-1H3a1 1 0 00-1 1v14l4-4h10a1 1 0 001-1z"/></svg>
                                @break
                            @case('bell')
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                @break
                        @endswitch
                    </span>
                    <span class="tracking-tight">{{ $item['name'] }}</span>
                </a>

                @if($item['active'])
                    <div class="absolute -bottom-4 right-0 h-4 w-4 overflow-hidden">
                        <div class="absolute top-0 right-0 h-8 w-8 rounded-tr-2xl" style="box-shadow: 8px -8px 0 var(--lumina-bg-section);"></div>
                    </div>
                @endif
            </div>
        @endforeach
    </nav>

    <div class="border-t p-6" style="border-color: rgba(226, 232, 240, 0.5);">
        <a href="{{ route('role.dashboard', ['role' => 'parent']) }}" class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold transition-all duration-200 hover:bg-white/50" style="color: var(--lumina-primary-dark);">
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
            <span>Back To Parent Account</span>
        </a>
    </div>
</aside>

<button
    onclick="toggleChildPortalSidebar()"
    class="fixed left-4 top-4 z-40 flex h-10 w-10 items-center justify-center rounded-lg lg:hidden"
    style="background-color: var(--lumina-primary); color: white;"
>
    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
</button>
