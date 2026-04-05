<x-layouts.student :title="__('Dashboard')" :currentRoute="'dashboard'">
    {{-- Welcome Hero Section --}}
    <div class="mb-8 flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
        {{-- Welcome Text --}}
        <div class="flex flex-col gap-2">
            <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
                Welcome back, {{ $user->name ?? 'Alex' }}.
            </h1>
            <p class="text-lg" style="color: var(--lumina-text-secondary);">
                Your academic access system is optimized for today's session.
            </p>
        </div>
        
        {{-- Current Status Badge --}}
        <div class="flex items-center gap-4 rounded-xl px-4 py-2" style="background-color: var(--lumina-bg-card);">
            <div class="flex flex-col items-end">
                <span class="text-xs font-bold uppercase tracking-wider" style="color: var(--lumina-text-heading); letter-spacing: 1.2px;">
                    Current Status
                </span>
                <span class="text-sm font-semibold" style="color: var(--lumina-primary);">
                    {{ $academicStatus ?? 'Academic Excellence' }}
                </span>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-full" style="background-color: var(--lumina-accent-green);">
                <svg class="h-5 w-5" fill="currentColor" style="color: #002111;" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Bento Grid Layout --}}
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Row 1: Academic Vitals Card (2 columns) + Next Class Card (1 column) --}}
        
        {{-- Academic Vitals / Student Card (Large Highlight) --}}
        <div 
            class="col-span-1 flex flex-col rounded-3xl border p-6 lg:col-span-2 lg:p-8"
            style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;"
        >
            {{-- Header --}}
            <div class="mb-6 flex items-center justify-between">
                <div class="flex flex-col gap-1">
                    <h3 class="text-sm font-bold uppercase tracking-wider" style="color: var(--lumina-text-heading); letter-spacing: 1.4px;">
                        Student Card
                    </h3>
                    <p class="text-xs" style="color: var(--lumina-text-secondary);">
                        Academic Identity
                    </p>
                </div>
                {{-- Status Badge --}}
                <div 
                    class="flex items-center gap-2 rounded-full px-3 py-1"
                    style="background-color: {{ ($studentStatus ?? 'active') === 'active' ? 'rgba(0, 106, 65, 0.1)' : 'rgba(186, 26, 26, 0.1)' }};"
                >
                    <span 
                        class="h-2 w-2 rounded-full"
                        style="background-color: {{ ($studentStatus ?? 'active') === 'active' ? 'var(--lumina-primary)' : 'var(--lumina-accent-red)' }};"
                    ></span>
                    <span 
                        class="text-xs font-bold uppercase"
                        style="color: {{ ($studentStatus ?? 'active') === 'active' ? 'var(--lumina-primary)' : 'var(--lumina-accent-red)' }};"
                    >
                        {{ ucfirst($studentStatus ?? 'Active') }}
                    </span>
                </div>
            </div>

            {{-- Main Content: Profile + Info --}}
            <div class="flex flex-col gap-6 lg:flex-row lg:gap-8">
                {{-- Profile Picture Section --}}
                <div class="flex flex-col items-center gap-3">
                    {{-- Hexagonal/Rounded Frame --}}
                    <div 
                        class="relative flex h-28 w-28 items-center justify-center overflow-hidden rounded-2xl lg:h-32 lg:w-32"
                        style="background: linear-gradient(135deg, var(--lumina-primary) 0%, var(--lumina-primary-light) 100%);"
                    >
                        @if($user->avatar ?? false)
                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-4xl font-bold text-white lg:text-5xl">
                                {{ $user ? $user->initials() : 'AT' }}
                            </span>
                        @endif
                    </div>
                    {{-- Student ID Badge --}}
                    <div 
                        class="rounded-lg px-3 py-1 text-center"
                        style="background-color: var(--lumina-bg-card);"
                    >
                        <span class="text-[10px] font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted);">
                            ID: {{ $studentId ?? 'LUM-2026-042' }}
                        </span>
                    </div>
                </div>

                {{-- Student Information --}}
                <div class="flex flex-1 flex-col gap-5">
                    {{-- Essential Identity --}}
                    <div class="flex flex-col gap-1">
                        <h4 class="font-serif text-2xl font-bold" style="color: var(--lumina-text-primary); font-family: 'Young Serif', Georgia, serif;">
                            {{ $user->name ?? 'Alex Thompson' }}
                        </h4>
                        <div class="flex flex-wrap items-center gap-3 text-sm" style="color: var(--lumina-text-secondary);">
                            <span class="flex items-center gap-1">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm-8 4H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/>
                                </svg>
                                {{ $studentBirthday ?? 'March 15, 2001' }} ({{ $studentAge ?? '24' }} years)
                            </span>
                            <span class="hidden text-gray-300 sm:inline">•</span>
                            <span class="flex items-center gap-1">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                                Member since {{ $enrollmentDate ?? 'September 2025' }}
                            </span>
                        </div>
                    </div>

                    {{-- Academic & Card Info --}}
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {{-- Academic Year --}}
                        <div class="flex flex-col gap-2 rounded-xl p-4" style="background-color: var(--lumina-bg-card);">
                            <span class="text-[10px] font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted);">
                                Academic Year
                            </span>
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" style="color: var(--lumina-primary);">
                                    <path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z"/>
                                </svg>
                                <span class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                                    {{ $academicYear ?? '2025/2026' }}
                                </span>
                            </div>
                            <span class="text-xs" style="color: var(--lumina-text-secondary);">
                                Registration Year: {{ $registrationYear ?? '2025' }}
                            </span>
                        </div>

                        {{-- Blood Type --}}
                        <div class="flex flex-col gap-2 rounded-xl p-4" style="background-color: var(--lumina-bg-card);">
                            <span class="text-[10px] font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted);">
                                Blood Type
                            </span>
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" style="color: var(--lumina-accent-red);">
                                    <path d="M12 2c-5.33 4.55-8 8.48-8 11.8 0 4.98 3.8 8.2 8 8.2s8-3.22 8-8.2c0-3.32-2.67-7.25-8-11.8zm0 18c-3.35 0-6-2.57-6-6.2 0-2.34 1.95-5.44 6-9.14 4.05 3.7 6 6.79 6 9.14 0 3.63-2.65 6.2-6 6.2z"/>
                                </svg>
                                <span class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                                    {{ $bloodType ?? 'O+' }}
                                </span>
                            </div>
                            <span class="text-xs" style="color: var(--lumina-text-secondary);">
                                Emergency info
                            </span>
                        </div>

                        {{-- Card Validity --}}
                        <div class="flex flex-col gap-2 rounded-xl p-4" style="background-color: var(--lumina-bg-card);">
                            <span class="text-[10px] font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted);">
                                Card Validity
                            </span>
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" style="color: var(--lumina-primary);">
                                    <path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
                                </svg>
                                <span class="text-sm font-bold" style="color: var(--lumina-text-primary);">
                                    {{ $cardValidFrom ?? 'Sep 2025' }} - {{ $cardValidUntil ?? 'Aug 2026' }}
                                </span>
                            </div>
                            @php
                                $isValid = ($cardStatus ?? 'valid') === 'valid';
                            @endphp
                            <span 
                                class="inline-flex w-fit items-center gap-1 rounded-full px-2 py-0.5 text-xs font-bold"
                                style="background-color: {{ $isValid ? 'var(--lumina-accent-green-bg)' : 'var(--lumina-accent-red-bg)' }}; color: {{ $isValid ? 'var(--lumina-accent-green)' : 'var(--lumina-accent-red)' }};"
                            >
                                <span class="h-1.5 w-1.5 rounded-full" style="background-color: currentColor;"></span>
                                {{ $isValid ? 'Valid' : 'Expired' }}
                            </span>
                        </div>
                    </div>

                    {{-- Emergency Contact --}}
                    <div class="flex flex-col gap-2 rounded-xl p-4" style="background-color: var(--lumina-bg-card);">
                        <span class="text-[10px] font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted);">
                            Emergency Contact
                        </span>
                        <div class="flex flex-wrap items-center gap-4">
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" style="color: var(--lumina-primary);">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                                <span class="text-sm font-semibold" style="color: var(--lumina-text-primary);">
                                    {{ $emergencyContactName ?? 'Maria Thompson' }}
                                </span>
                                <span class="text-xs" style="color: var(--lumina-text-muted);">
                                    ({{ $emergencyContactRelation ?? 'Mother' }})
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" style="color: var(--lumina-primary);">
                                    <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                                </svg>
                                <span class="text-sm" style="color: var(--lumina-text-secondary);">
                                    {{ $emergencyContactPhone ?? '+1 (555) 123-4567' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

</div>
        </div>
        
        {{-- Next Class Countdown Card --}}
        <div 
            class="relative flex flex-col justify-between overflow-hidden rounded-3xl p-8"
            style="background-color: var(--lumina-primary); min-height: 377px; box-shadow: 0px 20px 25px -5px rgba(0, 0, 0, 0.1), 0px 8px 10px -6px rgba(0, 0, 0, 0.1); border-radius: 24px;"
        >
            {{-- Gradient Overlay --}}
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-1/2" style="background: linear-gradient(0deg, rgba(0, 0, 0, 0.2) 0%, rgba(0, 0, 0, 0) 100%);"></div>
            
            {{-- Content --}}
            <div class="relative z-10 flex flex-col gap-4">
                {{-- Header --}}
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-white/80" style="letter-spacing: 1.2px;">
                        Next Class
                    </span>
                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                    </svg>
                </div>
                
                {{-- Course Name --}}
                <h4 class="text-3xl font-extrabold leading-tight text-white">
                    {{ $nextClass->course->name ?? 'French B2' }}
                </h4>
                
                {{-- Location & Teacher --}}
                <div class="flex items-center gap-2 text-white/90">
                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                    <span class="text-sm font-medium">
                        {{ $nextClass->room ?? 'Lecture Hall 4' }} • {{ $nextClass->teacher->name ?? 'Dr. Sterling' }}
                    </span>
                </div>
            </div>
            
            {{-- Countdown Timer Box --}}
            <div 
                class="relative z-10 flex flex-col gap-2 rounded-2xl p-6"
                style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.1); backdrop-filter: blur(6px);"
            >
                <span class="text-xs font-bold uppercase tracking-wider text-white/80" style="letter-spacing: 1.2px;">
                    Starts in
                </span>
                <div class="flex items-baseline gap-1">
                    <span class="text-4xl font-black text-white">
                        {{ $nextClassMinutes ?? 42 }}
                    </span>
                    <span class="text-xl font-bold text-white/80" style="letter-spacing: -1px;">
                        Minutes
                    </span>
                </div>
            </div>
        </div>
        
        {{-- Row 2: Language Proficiency + Mentors + Popular Courses --}}
        
        {{-- Language Proficiency Card --}}
        <div 
            class="flex flex-col justify-between rounded-3xl border p-8"
            style="background-color: var(--lumina-bg-card); border-color: rgba(190, 201, 191, 0.15); border-radius: 24px;"
        >
            {{-- Header --}}
            <div class="flex flex-col gap-6">
                <div class="flex items-start justify-between">
                    <h3 class="text-sm font-bold uppercase tracking-wider" style="color: var(--lumina-text-heading); letter-spacing: 1.4px;">
                        Language Proficiency
                    </h3>
                    <svg class="h-5 w-5" fill="currentColor" style="color: #5E70BB;" viewBox="0 0 24 24">
                        <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm6.93 6h-2.95c-.32-1.25-.78-2.45-1.38-3.56 1.84.63 3.37 1.91 4.33 3.56zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14C4.1 13.36 4 12.69 4 12s.1-1.36.26-2h3.38c-.08.66-.14 1.32-.14 2 0 .68.06 1.34.14 2H4.26zm.82 2h2.95c.32 1.25.78 2.45 1.38 3.56-1.84-.63-3.37-1.91-4.33-3.56zm2.95-8H5.08c.96-1.66 2.49-2.93 4.33-3.56C8.81 5.55 8.35 6.75 8.03 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82c-.43 1.43-1.08 2.76-1.91 3.96zM14.34 14H9.66c-.09-.66-.16-1.32-.16-2 0-.68.07-1.35.16-2h4.68c.09.65.16 1.32.16 2 0 .68-.07 1.34-.16 2zm.25 5.56c.6-1.11 1.06-2.31 1.38-3.56h2.95c-.96 1.65-2.49 2.93-4.33 3.56zM16.36 14c.08-.66.14-1.32.14-2 0-.68-.06-1.34-.14-2h3.38c.16.64.26 1.31.26 2s-.1 1.36-.26 2h-3.38z"/>
                    </svg>
                </div>
                
                {{-- Circular Progress --}}
                <div class="flex items-center justify-center py-4">
                    <div class="relative flex h-32 w-32 items-center justify-center">
                        {{-- Background Circle --}}
                        <svg class="absolute h-full w-full -rotate-90" viewBox="0 0 128 128">
                            <circle cx="64" cy="64" r="56" fill="none" stroke="#DFE4DD" stroke-width="10"/>
                            <circle 
                                cx="64" cy="64" r="56" 
                                fill="none" 
                                stroke="var(--lumina-primary)" 
                                stroke-width="10" 
                                stroke-dasharray="352" 
                                stroke-dashoffset="{{ 352 - (352 * ($proficiencyPercent ?? 75) / 100) }}"
                                stroke-linecap="round"
                            />
                        </svg>
                        {{-- Center Text --}}
                        <div class="relative z-10 flex flex-col items-center">
                            <span class="text-2xl font-black" style="color: var(--lumina-text-primary);">
                                {{ $proficiencyLevel ?? 'C1' }}
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-tight" style="color: var(--lumina-text-secondary);">
                                Current Goal
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Progress Info --}}
            <div class="flex flex-col gap-2 border-t pt-4" style="border-color: rgba(190, 201, 191, 0.3);">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold" style="color: var(--lumina-text-secondary);">
                        {{ $proficiencyPercent ?? 75 }}% to {{ $proficiencyLevel ?? 'C1' }}
                    </span>
                    <span class="text-sm font-bold" style="color: var(--lumina-primary);">
                        {{ $proficiencyStatus ?? 'Advanced' }}
                    </span>
                </div>
                <p class="text-xs" style="color: var(--lumina-text-secondary);">
                    Focus on specialized terminology to unlock the next level.
                </p>
            </div>
        </div>
        
        {{-- Mentors Card --}}
        <div 
            class="flex flex-col justify-between rounded-3xl border p-8"
            style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;"
        >
            {{-- Header --}}
            <div class="flex flex-col gap-6">
                <h3 class="text-sm font-bold uppercase tracking-wider" style="color: var(--lumina-text-heading); letter-spacing: 1.4px;">
                    Mentors
                </h3>
                
                {{-- Mentors List --}}
                <div class="flex flex-col gap-4">
                    @forelse($mentors ?? [] as $mentor)
                        <div class="flex items-center gap-3">
                            {{-- Avatar with Status --}}
                            <div class="relative">
                                <div class="h-10 w-10 overflow-hidden rounded-full">
                                    @if($mentor->avatar ?? false)
                                        <img src="{{ $mentor->avatar }}" alt="{{ $mentor->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-sm font-semibold" style="background-color: var(--lumina-bg-card); color: var(--lumina-primary);">
                                            {{ substr($mentor->name ?? 'M', 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                {{-- Online Status --}}
                                <div 
                                    class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white"
                                    style="background-color: {{ ($mentor->is_online ?? false) ? 'var(--lumina-primary)' : '#BEC9BF' }};"
                                ></div>
                            </div>
                            {{-- Info --}}
                            <div class="flex flex-col">
                                <span class="text-sm font-bold" style="color: var(--lumina-text-primary);">
                                    {{ $mentor->name ?? 'Mentor Name' }}
                                </span>
                                <span class="text-[10px]" style="color: var(--lumina-text-secondary);">
                                    {{ $mentor->specialty ?? 'Specialist' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        {{-- Default mentors if none provided --}}
                        @foreach([
                            ['name' => 'Prof. Janice S.', 'specialty' => 'Phonetics Expert', 'online' => true],
                            ['name' => 'Marcus L.', 'specialty' => 'Grammar Specialist', 'online' => false],
                            ['name' => 'Hamdane C.', 'specialty' => 'Math Specialist', 'online' => false],
                        ] as $defaultMentor)
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <div class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full" style="background-color: var(--lumina-bg-card);">
                                        <span class="text-sm font-semibold" style="color: var(--lumina-primary);">
                                            {{ substr($defaultMentor['name'], 0, 1) }}
                                        </span>
                                    </div>
                                    <div 
                                        class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white"
                                        style="background-color: {{ $defaultMentor['online'] ? 'var(--lumina-primary)' : '#BEC9BF' }};"
                                    ></div>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold" style="color: var(--lumina-text-primary);">
                                        {{ $defaultMentor['name'] }}
                                    </span>
                                    <span class="text-[10px]" style="color: var(--lumina-text-secondary);">
                                        {{ $defaultMentor['specialty'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @endforelse
                </div>
            </div>
            
            {{-- Message Center Link --}}
            <div class="pt-4">
                <a 
                    href="{{ route('messages.inbox') }}" 
                    class="inline-flex items-center gap-2 text-sm font-bold transition-colors hover:opacity-80"
                    style="color: var(--lumina-primary);"
                    wire:navigate
                >
                    <span>Message Center</span>
                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
                    </svg>
                </a>
            </div>
        </div>
        
        {{-- Popular Courses Card --}}
        <div 
            class="flex flex-col rounded-3xl border p-8"
            style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;"
        >
            {{-- Header --}}
            <h3 class="text-sm font-bold uppercase leading-tight tracking-wider" style="color: var(--lumina-text-heading); letter-spacing: 1.4px;">
                Popular Courses<br>
                <span class="text-xs font-normal normal-case">(other courses available)</span>
            </h3>
            
            {{-- Course List --}}
            <div class="mt-6 flex flex-col gap-3">
                @forelse($popularCourses ?? [] as $course)
                    <a 
                        href="#" 
                        class="flex items-center justify-between rounded-lg p-3 transition-colors hover:bg-gray-50"
                    >
                        <span class="text-sm font-medium" style="color: var(--lumina-text-primary);">
                            {{ $course->name }}
                        </span>
                        <svg class="h-4 w-4" fill="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                            <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
                        </svg>
                    </a>
                @empty
                    {{-- Default courses --}}
                    @foreach(['Spanish A1', 'German B1', 'Italian A2', 'Japanese N5'] as $defaultCourse)
                        <a 
                            href="#" 
                            class="flex items-center justify-between rounded-lg p-3 transition-colors hover:bg-gray-50"
                        >
                            <span class="text-sm font-medium" style="color: var(--lumina-text-primary);">
                                {{ $defaultCourse }}
                            </span>
                            <svg class="h-4 w-4" fill="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                                <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
                            </svg>
                        </a>
                    @endforeach
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.student>
