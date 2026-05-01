@props([
    'user' => null,
    'currentRoute' => 'dashboard',
    'children' => [],
    'selectedChild' => null,
    'hideFinancial' => false,
])

@php
    $user = $user ?? auth()->user();

    $navItems = [
        [
            'name' => 'Dashboard',
            'route' => 'role.dashboard',
            'routeParams' => ['role' => 'parent'],
            'icon' => 'grid',
        ],
        [
            'name' => 'Messages',
            'route' => 'role.messages.index',
            'routeParams' => ['role' => 'parent'],
            'icon' => 'chat',
        ],
    ];

    if (! $hideFinancial) {
        array_splice($navItems, 1, 0, [[
            'name' => 'Financial Information',
            'route' => 'parent.financial',
            'routeParams' => [],
            'icon' => 'credit-card',
        ]]);
    }

    $childrenList = collect($children ?? [
        ['id' => 1, 'name' => 'Alex Johnson', 'initials' => 'A', 'grade' => 'Grade 10'],
        ['id' => 2, 'name' => 'Sophie Johnson', 'initials' => 'S', 'grade' => 'Grade 8'],
    ])->values();

    $currentChild = $selectedChild ?? $childrenList->first();
@endphp

<aside 
    id="parent-sidebar"
    class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col transition-transform duration-300 ease-in-out lg:translate-x-0"
    style="background-color: var(--lumina-sidebar-bg);"
>
    <div class="flex flex-col gap-8 p-6">
        <div class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg shadow-md transition-transform duration-200 hover:scale-105" style="background: linear-gradient(135deg, #2D8C5E 0%, #006A41 100%);">
                <svg class="h-5 w-6 text-white" viewBox="0 0 22 18" fill="currentColor">
                    <path d="M11 0L0 6L4 8.18V14.18L11 18L18 14.18V8.18L20 7.09V14H22V6L11 0ZM17.82 6L11 9.72L4.18 6L11 2.28L17.82 6ZM16 12.99L11 15.72L6 12.99V9.27L11 12L16 9.27V12.99Z"/>
                </svg>
            </div>
            <div class="flex flex-col">
                <h1 class="font-calibri text-2xl font-bold leading-tight" style="color: var(--lumina-primary-dark);">Lumina Academy</h1>
                <span class="text-xs font-bold tracking-tight" style="color: var(--lumina-text-muted);">Parent Portal</span>
            </div>
        </div>

    </div>

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
                    <span class="flex h-5 w-5 items-center justify-center transition-transform duration-200 {{ $isActive ? '' : 'group-hover:scale-110' }}">
                        @switch($item['icon'])
                            @case('grid')
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                @break
                            @case('credit-card')
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2zm0 4H4V6h16v2z"/></svg>
                                @break
                            @case('chat')
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M21 6h-2v9H6v2a1 1 0 001 1h11l4 4V7a1 1 0 00-1-1zm-4 6V3a1 1 0 00-1-1H3a1 1 0 00-1 1v14l4-4h10a1 1 0 001-1z"/></svg>
                                @break
                        @endswitch
                    </span>
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

    <div class="border-t p-6" style="border-color: rgba(226, 232, 240, 0.5);">
        <div class="flex flex-col gap-2">
            <div class="mb-3 flex flex-col gap-3">
                <span class="px-1 text-[10px] font-black uppercase tracking-wider" style="color: #034C3C; letter-spacing: 1px;">
                    Access Child Account
                </span>

                <div class="relative">
                    <button
                        id="child-selector-button"
                        onclick="toggleChildSelector()"
                        class="flex w-full items-center justify-between rounded-xl border p-3 transition-all duration-200 hover:shadow-md"
                        style="background-color: #FFFFFF; border-color: #E2E8F0;"
                    >
                        <span class="text-sm font-semibold" style="color: var(--lumina-text-primary);">Choose Child Account</span>
                        <svg
                            id="child-selector-icon"
                            class="h-3 w-3 transition-transform duration-200"
                            viewBox="0 0 12 8"
                            fill="none"
                            style="color: #94A3B8;"
                        >
                            <path d="M1 1.5L6 6.5L11 1.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div
                        id="child-selector-dropdown"
                        class="absolute bottom-full left-0 right-0 z-30 mb-2 hidden flex-col gap-1 rounded-xl border p-2 shadow-lg"
                        style="background-color: #FFFFFF; border-color: #E2E8F0;"
                    >
                        @foreach($childrenList as $child)
                            <a
                                href="{{ route('parent.child.dashboard', ['child' => $child['id']]) }}"
                                class="flex items-center gap-3 rounded-lg p-2 transition-colors hover:bg-gray-50"
                            >
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg" style="background-color: var(--lumina-accent-green-bg); color: #034C3C;">
                                    <span class="text-xs font-black">{{ $child['initials'] ?? substr($child['name'], 0, 1) }}</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold" style="color: var(--lumina-text-primary);">{{ $child['name'] }}</span>
                                    <span class="text-[10px]" style="color: var(--lumina-text-muted);">{{ $child['grade'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <a href="{{ route('support') }}" class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold transition-all duration-200 hover:bg-white/50" style="color: var(--lumina-text-muted);">
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a10 10 0 100 20 10 10 0 000-20zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92c-.72.73-1.17 1.4-1.17 2.83V15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26a2 2 0 10-3.41-1.41H8a4 4 0 118 0c0 .88-.36 1.68-.93 2.25z"/></svg>
                <span>Support</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold transition-all duration-200 hover:bg-red-50 btn-press" style="color: var(--lumina-accent-red);">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4a2 2 0 00-2 2v14a2 2 0 002 2h8v-2H4V5z"/></svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>

<button 
    onclick="toggleSidebar()"
    class="fixed left-4 top-4 z-40 flex h-10 w-10 items-center justify-center rounded-lg lg:hidden"
    style="background-color: var(--lumina-primary); color: white;"
>
    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
</button>
