@props([
    'user' => null,
    'currentRoute' => 'dashboard',
    'children' => [],
    'selectedChild' => null
])

@php
    $navItems = [
        [
            'name' => 'Dashboard',
            'route' => 'role.dashboard',
            'routeParams' => ['role' => 'parent'],
            'icon' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/>',
        ],
        [
            'name' => 'Calendar',
            'route' => 'parent.calendar',
            'routeParams' => [],
            'icon' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
        ],
        [
            'name' => 'Financial Information',
            'route' => 'parent.financial',
            'routeParams' => [],
            'icon' => '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
        ],
        [
            'name' => 'Messages',
            'route' => 'role.messages.index',
            'routeParams' => ['role' => 'parent'],
            'icon' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        ],
        [
            'name' => 'Settings',
            'route' => 'parent.settings',
            'routeParams' => [],
            'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
        ],
    ];
    
    // Default children data for demo
    $defaultChildren = [
        [
            'id' => 1,
            'name' => 'Ethan H.',
            'full_name' => 'Ethan Henderson',
            'grade' => 'Grade 8',
            'avatar' => null,
            'initials' => 'E',
        ],
        [
            'id' => 2,
            'name' => 'Alex J.',
            'full_name' => 'Alex Johnson',
            'grade' => 'Grade 10',
            'avatar' => null,
            'initials' => 'A',
        ],
        [
            'id' => 3,
            'name' => 'Sophie J.',
            'full_name' => 'Sophie Johnson',
            'grade' => 'Grade 8',
            'avatar' => null,
            'initials' => 'S',
        ],
    ];
    
    $childrenList = count($children) > 0 ? $children : $defaultChildren;
    $currentChild = $selectedChild ?? ($childrenList[0] ?? null);
@endphp

{{-- Parent Sidebar --}}
<aside 
    id="parent-sidebar"
    class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col justify-between p-6 transition-transform duration-300 lg:relative lg:translate-x-0"
    style="background-color: var(--lumina-sidebar-bg);"
>
    {{-- Top Section --}}
    <div class="flex flex-col gap-12">
        {{-- Logo & Brand --}}
        <div class="flex items-center gap-3">
            {{-- Logo Icon --}}
            <div 
                class="flex h-12 w-12 items-center justify-center rounded-lg"
                style="background: linear-gradient(0deg, #2D8C5E, #2D8C5E);"
            >
                <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
            </div>
            {{-- Brand Text --}}
            <div class="flex flex-col">
                <span 
                    class="text-2xl font-bold leading-tight"
                    style="font-family: 'Calibri', sans-serif; color: #034C3C;"
                >
                    Lumina<br>Academy
                </span>
                <span 
                    class="text-[13px] font-semibold tracking-tight"
                    style="color: #3F4941; letter-spacing: -0.4px;"
                >
                    Parent Portal
                </span>
            </div>
        </div>

        {{-- Navigation Links --}}
        <nav class="flex flex-col gap-2">
            @foreach($navItems as $item)
                @php
                    $isActive = request()->routeIs($item['route']);
                @endphp
                <a 
                    href="{{ route($item['route'], $item['routeParams'] ?? []) }}"
                    class="flex items-center gap-4 rounded-l-2xl px-4 py-3 text-[15px] font-medium transition-all duration-200"
                    style="{{ $isActive 
                        ? 'background-color: var(--lumina-bg-section); font-weight: 700; color: #034C3C;' 
                        : 'color: #034C3C;' }}"
                >
                    <svg 
                        class="h-[18px] w-[18px]" 
                        viewBox="0 0 24 24" 
                        fill="none" 
                        stroke="currentColor" 
                        stroke-width="2" 
                        stroke-linecap="round" 
                        stroke-linejoin="round"
                        style="color: {{ $isActive ? '#034C3C' : '#034C3C' }};"
                    >
                        {!! $item['icon'] !!}
                    </svg>
                    <span style="letter-spacing: -0.35px;">{{ $item['name'] }}</span>
                </a>
            @endforeach
        </nav>
        
        {{-- Divider --}}
        <div class="border-t" style="border-color: #E2E8F0;"></div>
        
        {{-- Access Child Account Section --}}
        <div class="flex flex-col gap-3">
            <span 
                class="px-4 text-[10px] font-black uppercase tracking-wider"
                style="color: #034C3C; letter-spacing: 1px;"
            >
                Access Child Account
            </span>
            
            {{-- Child Selector Button --}}
            <div class="relative">
                <button 
                    id="child-selector-button"
                    onclick="toggleChildSelector()"
                    class="flex w-full items-center justify-between rounded-xl border p-3 transition-all hover:shadow-md"
                    style="background-color: #FFFFFF; border-color: #E2E8F0; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);"
                >
                    <div class="flex items-center gap-3">
                        {{-- Child Avatar --}}
                        <div 
                            class="flex h-10 w-10 items-center justify-center rounded-lg"
                            style="background-color: var(--lumina-accent-green-bg);"
                        >
                            @if($currentChild && isset($currentChild['avatar']) && $currentChild['avatar'])
                                <img src="{{ $currentChild['avatar'] }}" alt="{{ $currentChild['name'] }}" class="h-full w-full rounded-lg object-cover">
                            @else
                                <span class="text-sm font-black" style="color: #1E293B;">
                                    {{ $currentChild['initials'] ?? 'E' }}
                                </span>
                            @endif
                        </div>
                        {{-- Child Info --}}
                        <div class="flex flex-col items-start">
                            <span class="text-sm font-bold" style="color: #1E293B; letter-spacing: -0.4px;">
                                {{ $currentChild['name'] ?? 'Ethan H.' }}
                            </span>
                            <span class="text-[10px] font-medium uppercase" style="color: #64748B; letter-spacing: -0.4px;">
                                {{ $currentChild['grade'] ?? 'Grade 8' }}
                            </span>
                        </div>
                    </div>
                    {{-- Dropdown Icon --}}
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
                
                {{-- Dropdown Menu --}}
                <div 
                    id="child-selector-dropdown"
                    class="absolute left-0 right-0 top-full z-10 mt-2 hidden flex-col gap-1 rounded-xl border p-2 shadow-lg"
                    style="background-color: #FFFFFF; border-color: #E2E8F0;"
                >
                    @foreach($childrenList as $child)
                        <a 
                            href="{{ route('role.dashboard', ['role' => 'parent', 'child' => $child['id']]) }}"
                            class="flex items-center gap-3 rounded-lg p-2 transition-colors hover:bg-gray-50"
                        >
                            <div 
                                class="flex h-8 w-8 items-center justify-center rounded-lg"
                                style="background-color: {{ $loop->index === 0 ? 'var(--lumina-accent-green-bg)' : ($loop->index === 1 ? 'var(--lumina-child-1)' : 'var(--lumina-child-2)') }};"
                            >
                                <span class="text-xs font-black" style="color: #1E293B;">
                                    {{ $child['initials'] ?? substr($child['name'], 0, 1) }}
                                </span>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-semibold" style="color: #1E293B;">
                                    {{ $child['name'] }}
                                </span>
                                <span class="text-[10px]" style="color: #64748B;">
                                    {{ $child['grade'] }}
                                </span>
                            </div>
                            @if(($currentChild['id'] ?? null) == $child['id'])
                                <svg class="ml-auto h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-primary);">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            @endif
                        </a>
                    @endforeach
                    
                    {{-- View All Children Link --}}
                    <div class="mt-1 border-t pt-2" style="border-color: #E2E8F0;">
                        <a 
                            href="{{ route('role.dashboard', ['role' => 'parent']) }}"
                            class="flex items-center justify-center gap-2 rounded-lg p-2 text-sm font-semibold transition-colors hover:bg-gray-50"
                            style="color: var(--lumina-primary);"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            View All Children
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bottom Section --}}
    <div class="flex flex-col gap-1 border-t pt-6" style="border-color: rgba(226, 232, 240, 0.5);">
        {{-- Support Link --}}
        <a 
            href="#"
            class="flex items-center gap-3 px-4 py-2 text-base font-semibold transition-colors hover:opacity-80"
            style="color: #64748B;"
        >
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
            <span>Support</span>
        </a>
        
        {{-- Logout Link --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button 
                type="submit"
                class="flex w-full items-center gap-3 px-4 py-2 text-base font-semibold transition-colors hover:opacity-80"
                style="color: #BA1A1A;"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16,17 21,12 16,7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>

{{-- Mobile Menu Button (shown only on mobile) --}}
<button 
    onclick="toggleSidebar()"
    class="fixed left-4 top-4 z-40 flex h-10 w-10 items-center justify-center rounded-lg lg:hidden"
    style="background-color: var(--lumina-primary); color: white;"
>
    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
</button>
