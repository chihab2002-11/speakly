<x-layouts.parent 
    :title="'Dashboard'"
    :pageTitle="'Dashboard'"
    :currentRoute="'dashboard'"
    :user="$user ?? null"
    :children="$children ?? []"
    :selectedChild="$selectedChild ?? null"
>
    {{-- Hero Header Section --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        {{-- Left: Welcome Message --}}
        <div class="flex flex-col gap-2">
            <span 
                class="text-xs font-bold uppercase tracking-wider"
                style="color: var(--lumina-primary); letter-spacing: 1.2px;"
            >
                Welcome Back, {{ $user->first_name ?? 'Sarah' }}
            </span>
            <h2 
                class="text-4xl font-black"
                style="color: var(--lumina-text-primary); letter-spacing: -0.9px;"
            >
                Overview
            </h2>
        </div>
        
        {{-- Right: Academic Term --}}
        <div class="flex flex-col items-end">
            <span class="text-sm font-medium" style="color: var(--lumina-text-secondary);">
                Academic Term
            </span>
            <span class="text-xl font-bold" style="color: #065F46;">
                {{ $academicTerm ?? 'Spring 2024' }}
            </span>
        </div>
    </div>

    {{-- Bento Grid Layout --}}
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Card 1: Overall Academic Status (Large Card - spans 2 columns) --}}
        <div 
            class="relative overflow-hidden rounded-3xl border p-8 lg:col-span-2"
            style="background-color: #FFFFFF; border-color: var(--lumina-border-light); box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);"
        >
            {{-- Background Decorative Icon --}}
            <div class="absolute right-1 top-1 opacity-10">
                <svg class="h-24 w-24" fill="currentColor" viewBox="0 0 24 24" style="color: var(--lumina-text-primary);">
                    <path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7zm-4 6h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/>
                </svg>
            </div>
            
            {{-- Card Content --}}
            <div class="relative z-10 flex flex-col gap-6">
                {{-- Header --}}
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" style="color: var(--lumina-primary);">
                        <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z"/>
                    </svg>
                    <span 
                        class="text-sm font-bold uppercase tracking-tight"
                        style="color: var(--lumina-primary); letter-spacing: -0.7px;"
                    >
                        Overall Academic Status
                    </span>
                </div>

                {{-- Children Grid --}}
                <div class="grid gap-6 sm:grid-cols-2">
                    @php
                        $childrenData = $children ?? [
                            [
                                'id' => 1,
                                'name' => 'Alex Johnson',
                                'initials' => 'A',
                                'grade' => 'Grade 10',
                                'stream' => 'Science Stream',
                                'gpa' => '3.8',
                                'status' => 'On Track',
                                'color' => 'var(--lumina-child-1)',
                                'textColor' => 'var(--lumina-child-1-text)',
                            ],
                            [
                                'id' => 2,
                                'name' => 'Sophie Johnson',
                                'initials' => 'S',
                                'grade' => 'Grade 8',
                                'stream' => 'Arts Stream',
                                'gpa' => '3.6',
                                'status' => 'On Track',
                                'color' => 'var(--lumina-child-2)',
                                'textColor' => 'var(--lumina-child-2-text)',
                            ],
                        ];
                    @endphp
                    
                    @foreach($childrenData as $index => $child)
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center gap-4">
                                {{-- Child Avatar --}}
                                <div 
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl"
                                    style="background-color: {{ $child['color'] ?? ($index === 0 ? 'var(--lumina-child-1)' : 'var(--lumina-child-2)') }};"
                                >
                                    <span 
                                        class="text-base font-black"
                                        style="color: {{ $child['textColor'] ?? ($index === 0 ? 'var(--lumina-child-1-text)' : 'var(--lumina-child-2-text)') }};"
                                    >
                                        {{ $child['initials'] ?? substr($child['name'], 0, 1) }}
                                    </span>
                                </div>
                                {{-- Child Info --}}
                                <div class="flex flex-col">
                                    <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                                        {{ $child['name'] }}
                                    </h3>
                                    <span class="text-xs" style="color: var(--lumina-text-secondary);">
                                        {{ $child['grade'] ?? 'Grade 10' }} &bull; {{ $child['stream'] ?? 'Science Stream' }}
                                    </span>
                                </div>
                            </div>
                            
                            {{-- Quick Stats --}}
                            <div class="flex items-baseline gap-2">
                                <span class="text-sm" style="color: var(--lumina-text-muted);">Current GPA:</span>
                                <span class="text-lg font-bold" style="color: var(--lumina-primary);">
                                    {{ $child['gpa'] ?? '3.8' }}
                                </span>
                                <span 
                                    class="ml-2 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase"
                                    style="background-color: var(--lumina-accent-green-bg); color: #065F46;"
                                >
                                    {{ $child['status'] ?? 'On Track' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Divider & Action --}}
                <div class="border-t pt-8" style="border-color: var(--lumina-border-light);">
                    <a 
                        href="{{ route('dashboard') }}"
                        class="inline-flex items-center gap-1 text-base font-bold transition-colors hover:opacity-80"
                        style="color: var(--lumina-primary);"
                    >
                        View Detailed Reports
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- Card 2: Pending Payments Widget (Dark Green) --}}
        <div 
            class="relative overflow-hidden rounded-3xl p-8"
            style="background-color: var(--lumina-dark-green); box-shadow: 0px 20px 25px -5px rgba(2, 44, 34, 0.2), 0px 8px 10px -6px rgba(2, 44, 34, 0.2);"
        >
            {{-- Background Blur Effect --}}
            <div 
                class="absolute -bottom-10 -right-10 h-40 w-40 rounded-full opacity-50"
                style="background-color: #065F46; filter: blur(32px);"
            ></div>
            
            {{-- Content --}}
            <div class="relative z-10 flex h-full flex-col justify-between">
                {{-- Header --}}
                <div class="flex items-start justify-between">
                    <div 
                        class="flex h-11 w-11 items-center justify-center rounded-2xl"
                        style="background-color: rgba(6, 95, 70, 0.5);"
                    >
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                            <line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                    </div>
                    <span 
                        class="rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-wider text-white"
                        style="background-color: rgba(4, 120, 87, 0.5); border-color: rgba(5, 150, 105, 0.5);"
                    >
                        Tuition Due
                    </span>
                </div>

                {{-- Amount Section --}}
                <div class="mt-10 flex flex-col gap-1">
                    <span class="text-sm font-medium" style="color: #6EE7B7;">
                        Total Outstanding
                    </span>
                    <span 
                        class="text-5xl font-black"
                        style="color: #FFFFFF; letter-spacing: -2.4px;"
                    >
                        @php
                            $totalAmount = $totalOutstanding ?? 245000;
                            $formattedAmount = number_format($totalAmount, 0, ',', ' ');
                        @endphp
                        {{ $formattedAmount }} <span class="text-2xl">DZD</span>
                    </span>
                </div>

                {{-- Breakdown --}}
                <div class="mt-7 flex flex-col gap-4">
                    @php
                        $paymentBreakdown = $payments ?? [
                            ['child' => 'Alex', 'term' => 'Term 3', 'amount' => 122500],
                            ['child' => 'Sophie', 'term' => 'Term 3', 'amount' => 122500],
                        ];
                    @endphp
                    
                    @foreach($paymentBreakdown as $payment)
                        <div 
                            class="flex items-center justify-between border-b py-2"
                            style="border-color: rgba(6, 95, 70, 0.5);"
                        >
                            <span class="text-xs" style="color: #A7F3D0;">
                                {{ $payment['child'] }} - {{ $payment['term'] }}
                            </span>
                            <span class="text-sm font-bold text-white">
                                {{ number_format($payment['amount'], 0, ',', ' ') }} DZD
                            </span>
                        </div>
                    @endforeach
                </div>

                {{-- Pay Now Button --}}
                <a 
                    href="{{ route('parent.financial') }}"
                    class="group mt-6 flex items-center justify-center gap-2 rounded-xl py-4 text-base font-bold transition-all duration-300 hover:scale-[1.02] hover:shadow-xl"
                    style="background-color: #FFFFFF; color: var(--lumina-dark-green); box-shadow: 0px 10px 15px -3px rgba(0, 0, 0, 0.1);"
                    onmouseenter="this.style.backgroundColor='var(--lumina-accent-green)'"
                    onmouseleave="this.style.backgroundColor='#FFFFFF'"
                >
                    Pay Now
                    <svg class="h-3 w-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Second Row: Info Cards --}}
    <div class="mt-6 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
        {{-- Uploaded Documents Card --}}
        <div 
            class="flex flex-col items-center justify-center rounded-3xl border p-8 text-center"
            style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border-light); min-height: 260px;"
        >
            <svg class="mb-4 h-12 w-12 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-secondary);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span 
                class="text-sm font-bold uppercase tracking-wider"
                style="color: var(--lumina-text-secondary); letter-spacing: 1.4px;"
            >
                Uploaded<br>Documents and<br>Homeworks
            </span>
            <span class="mt-4 text-3xl font-black" style="color: var(--lumina-primary);">
                {{ $documentsCount ?? 12 }}
            </span>
            <span class="text-xs" style="color: var(--lumina-text-muted);">This semester</span>
        </div>

        {{-- Teachers Card --}}
        <div 
            class="flex flex-col items-center justify-center rounded-3xl border p-8 text-center"
            style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border-light); min-height: 260px;"
        >
            <svg class="mb-4 h-12 w-12 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-secondary);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span 
                class="text-sm font-bold uppercase tracking-wider"
                style="color: var(--lumina-text-secondary); letter-spacing: 1.4px;"
            >
                Teachers of<br>Your Children
            </span>
            <span class="mt-4 text-3xl font-black" style="color: var(--lumina-primary);">
                {{ $teachersCount ?? 8 }}
            </span>
            <a href="#" class="mt-2 text-xs font-semibold hover:underline" style="color: var(--lumina-primary);">
                View All
            </a>
        </div>

        {{-- Upcoming Events Card --}}
        <div 
            class="flex flex-col items-center justify-center rounded-3xl border p-8 text-center"
            style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border-light); min-height: 260px;"
        >
            <svg class="mb-4 h-12 w-12 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-secondary);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span 
                class="text-sm font-bold uppercase tracking-wider"
                style="color: var(--lumina-text-secondary); letter-spacing: 1.4px;"
            >
                Upcoming<br>Events
            </span>
            <span class="mt-4 text-3xl font-black" style="color: var(--lumina-primary);">
                {{ $eventsCount ?? 3 }}
            </span>
            <span class="text-xs" style="color: var(--lumina-text-muted);">This month</span>
        </div>

        {{-- Unread Messages Card --}}
        <div 
            class="flex flex-col items-center justify-center rounded-3xl border p-8 text-center"
            style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border-light); min-height: 260px;"
        >
            <svg class="mb-4 h-12 w-12 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-secondary);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span 
                class="text-sm font-bold uppercase tracking-wider"
                style="color: var(--lumina-text-secondary); letter-spacing: 1.4px;"
            >
                Unread<br>Messages
            </span>
            <span class="mt-4 text-3xl font-black" style="color: var(--lumina-accent-red);">
                {{ $unreadMessagesCount ?? 5 }}
            </span>
            <a href="{{ route('role.messages.index', ['role' => 'parent']) }}" class="mt-2 text-xs font-semibold hover:underline" style="color: var(--lumina-primary);">
                View Inbox
            </a>
        </div>
    </div>

    {{-- Third Row: Quick Actions & Recent Activity --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Quick Actions --}}
        <div 
            class="rounded-3xl border p-6"
            style="background-color: #FFFFFF; border-color: var(--lumina-border-light);"
        >
            <h3 class="mb-4 text-lg font-bold" style="color: var(--lumina-text-primary);">
                Quick Actions
            </h3>
            <div class="grid grid-cols-2 gap-3">
                <a 
                    href="{{ route('role.messages.index', ['role' => 'parent']) }}"
                    class="flex items-center gap-3 rounded-xl p-4 transition-all hover:shadow-md"
                    style="background-color: var(--lumina-bg-card);"
                >
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg" style="background-color: var(--lumina-accent-green-bg);">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-primary);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold" style="color: var(--lumina-text-primary);">
                        Contact Teacher
                    </span>
                </a>
                
                <a 
                    href="{{ route('parent.financial') }}"
                    class="flex items-center gap-3 rounded-xl p-4 transition-all hover:shadow-md"
                    style="background-color: var(--lumina-bg-card);"
                >
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg" style="background-color: var(--lumina-accent-green-bg);">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-primary);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold" style="color: var(--lumina-text-primary);">
                        Pay Tuition
                    </span>
                </a>
                
                <a 
                    href="#"
                    class="flex items-center gap-3 rounded-xl p-4 transition-all hover:shadow-md"
                    style="background-color: var(--lumina-bg-card);"
                >
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg" style="background-color: var(--lumina-accent-green-bg);">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-primary);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold" style="color: var(--lumina-text-primary);">
                        View Reports
                    </span>
                </a>
                
                <a 
                    href="#"
                    class="flex items-center gap-3 rounded-xl p-4 transition-all hover:shadow-md"
                    style="background-color: var(--lumina-bg-card);"
                >
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg" style="background-color: var(--lumina-accent-green-bg);">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-primary);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <span class="text-sm font-semibold" style="color: var(--lumina-text-primary);">
                        School Calendar
                    </span>
                </a>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div 
            class="rounded-3xl border p-6"
            style="background-color: #FFFFFF; border-color: var(--lumina-border-light);"
        >
            <h3 class="mb-4 text-lg font-bold" style="color: var(--lumina-text-primary);">
                Recent Activity
            </h3>
            <div class="flex flex-col gap-4">
                @php
                    $activities = [
                        [
                            'icon' => 'document',
                            'title' => 'New grade posted',
                            'description' => 'Alex received 18/20 in Mathematics',
                            'time' => '2 hours ago',
                            'color' => 'var(--lumina-primary)',
                        ],
                        [
                            'icon' => 'message',
                            'title' => 'Message from teacher',
                            'description' => 'Ms. Benali sent a message about Sophie',
                            'time' => '5 hours ago',
                            'color' => '#3B82F6',
                        ],
                        [
                            'icon' => 'calendar',
                            'title' => 'Upcoming event',
                            'description' => 'Parent-Teacher meeting on April 10',
                            'time' => '1 day ago',
                            'color' => '#F59E0B',
                        ],
                        [
                            'icon' => 'payment',
                            'title' => 'Payment reminder',
                            'description' => 'Term 3 tuition due in 7 days',
                            'time' => '2 days ago',
                            'color' => 'var(--lumina-accent-red)',
                        ],
                    ];
                @endphp
                
                @foreach($activities as $activity)
                    <div class="flex items-start gap-3 rounded-xl p-3 transition-colors hover:bg-gray-50">
                        <div 
                            class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg"
                            style="background-color: {{ $activity['color'] }}20;"
                        >
                            @if($activity['icon'] === 'document')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: {{ $activity['color'] }};">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            @elseif($activity['icon'] === 'message')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: {{ $activity['color'] }};">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            @elseif($activity['icon'] === 'calendar')
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: {{ $activity['color'] }};">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            @else
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: {{ $activity['color'] }};">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold" style="color: var(--lumina-text-primary);">
                                {{ $activity['title'] }}
                            </h4>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">
                                {{ $activity['description'] }}
                            </p>
                        </div>
                        <span class="text-[10px]" style="color: var(--lumina-text-muted);">
                            {{ $activity['time'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.parent>
