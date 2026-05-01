<x-dynamic-component :component="$layoutComponent ?? 'layouts.student'" :title="__('Dashboard')" :currentRoute="$currentRoute ?? 'dashboard'" :pageTitle="'Child Dashboard'" :user="$user ?? null" :portalParent="$portalParent ?? null" :portalChildren="$portalChildren ?? []" :portalSelectedChild="$portalSelectedChild ?? null">
    @php
        $dashboardMessageCenterRouteName = $dashboardMessageCenterRouteName ?? 'role.messages.index';
        $dashboardMessageCenterRouteParams = $dashboardMessageCenterRouteParams ?? ['role' => 'student'];
    @endphp
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
                    class="flex items-center gap-2 rounded-full px-3 py-1 {{ ($studentStatus ?? 'active') === 'active' ? 'bg-emerald-50' : 'bg-red-50' }}"
                >
                    <span 
                        class="h-2 w-2 rounded-full {{ ($studentStatus ?? 'active') === 'active' ? 'bg-emerald-700' : 'bg-red-700' }}"
                    ></span>
                    <span 
                        class="text-xs font-bold uppercase {{ ($studentStatus ?? 'active') === 'active' ? 'text-emerald-700' : 'text-red-700' }}"
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
                <div class="flex flex-1 flex-col gap-4">
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
                    <div class="grid gap-4 sm:grid-cols-2">
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
                                class="inline-flex w-fit items-center gap-1 rounded-full px-2 py-0.5 text-xs font-bold {{ $isValid ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}"
                            >
                                <span class="h-1.5 w-1.5 rounded-full" style="background-color: currentColor;"></span>
                                {{ $isValid ? 'Valid' : 'Expired' }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Assigned Courses --}}
            <div class="mt-5 rounded-xl border p-4" style="border-color: rgba(190, 201, 191, 0.25); background-color: #F8FBF9;">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted); letter-spacing: 1.1px;">
                        Assigned Courses
                    </span>
                    <span class="text-xs font-bold" style="color: var(--lumina-primary);">
                        {{ count($assignedCourseNames ?? []) }} Course{{ count($assignedCourseNames ?? []) === 1 ? '' : 's' }}
                    </span>
                </div>

                @if(!empty($assignedCourseNames ?? []))
                    <div class="flex flex-wrap gap-2">
                        @foreach(($assignedCourseNames ?? []) as $courseName)
                            <span class="rounded-full px-3 py-1 text-xs font-semibold" style="background-color: #E5EFEA; color: #1E3A2F;">
                                {{ $courseName }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs" style="color: var(--lumina-text-secondary);">No course assignment found yet.</p>
                @endif
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
                        {{ $nextClassRoomName ?? 'Lecture Hall 4' }} • {{ $nextClass->teacher->name ?? 'Dr. Sterling' }}
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
                <span id="nextClassCountdownLive" data-target="{{ $nextClassStartsAt ?? '' }}" class="block w-full overflow-hidden text-center font-black text-white" style="font-size: clamp(1rem, 3.1vw, 1.65rem); letter-spacing: -0.2px; line-height: 1.15; white-space: nowrap;">
                    {{ $nextClassCountdown ?? '42Min' }}
                </span>
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
                    <div class="flex items-center gap-2">
                        <select id="proficiencyGroupFilter" class="rounded-lg border px-2 py-1 text-xs font-semibold" style="border-color: var(--lumina-border); background-color: #fff; color: var(--lumina-text-secondary);"></select>
                        <svg class="h-5 w-5" fill="currentColor" style="color: #5E70BB;" viewBox="0 0 24 24">
                            <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm6.93 6h-2.95c-.32-1.25-.78-2.45-1.38-3.56 1.84.63 3.37 1.91 4.33 3.56zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14C4.1 13.36 4 12.69 4 12s.1-1.36.26-2h3.38c-.08.66-.14 1.32-.14 2 0 .68.06 1.34.14 2H4.26zm.82 2h2.95c.32 1.25.78 2.45 1.38 3.56-1.84-.63-3.37-1.91-4.33-3.56zm2.95-8H5.08c.96-1.66 2.49-2.93 4.33-3.56C8.81 5.55 8.35 6.75 8.03 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82c-.43 1.43-1.08 2.76-1.91 3.96zM14.34 14H9.66c-.09-.66-.16-1.32-.16-2 0-.68.07-1.35.16-2h4.68c.09.65.16 1.32.16 2 0 .68-.07 1.34-.16 2zm.25 5.56c.6-1.11 1.06-2.31 1.38-3.56h2.95c-.96 1.65-2.49 2.93-4.33 3.56zM16.36 14c.08-.66.14-1.32.14-2 0-.68-.06-1.34-.14-2h3.38c.16.64.26 1.31.26 2s-.1 1.36-.26 2h-3.38z"/>
                        </svg>
                    </div>
                </div>
                
                {{-- Circular Progress --}}
                <div class="flex items-center justify-center py-4">
                    <div class="relative flex h-32 w-32 items-center justify-center">
                        {{-- Background Circle --}}
                        <svg class="absolute h-full w-full -rotate-90" viewBox="0 0 128 128">
                            <circle cx="64" cy="64" r="56" fill="none" stroke="#DFE4DD" stroke-width="10"/>
                            <circle 
                                id="proficiencyProgressCircle"
                                cx="64" cy="64" r="56" 
                                fill="none" 
                                stroke="var(--lumina-primary)" 
                                stroke-width="10" 
                                stroke-dasharray="352" 
                                stroke-dashoffset="{{ 352 - (352 * ($proficiencyPercent ?? 0) / 100) }}"
                                stroke-linecap="round"
                            />
                        </svg>
                        {{-- Center Text --}}
                        <div class="relative z-10 flex flex-col items-center">
                            <span id="proficiencyCenterGroupText" class="max-w-[90px] text-center text-[12px] font-black leading-tight" style="color: var(--lumina-text-primary);">
                                All Groups
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Progress Info --}}
            <div class="flex flex-col gap-2 border-t pt-4" style="border-color: rgba(190, 201, 191, 0.3);">
                <div class="flex items-center justify-between">
                    <span id="proficiencyPercentText" class="text-sm font-bold" style="color: var(--lumina-text-secondary);">
                        {{ $proficiencyPercent ?? 0 }}%
                    </span>
                    <span id="proficiencyStatusText" class="text-sm font-bold" style="color: var(--lumina-primary);">
                        {{ $proficiencyStatus ?? 'Advanced' }}
                    </span>
                </div>
                <p id="proficiencyInsightText" class="text-xs" style="color: var(--lumina-text-secondary);">
                    {{ $proficiencyInsight ?? 'Complete evaluations to track your language progression.' }}
                </p>
            </div>
        </div>
        
        {{-- Mentors Card --}}
        <div 
            class="flex flex-col justify-between rounded-3xl border p-8"
            style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;"
        >
            <div class="flex flex-col gap-6">
                <h3 class="text-sm font-bold uppercase tracking-wider" style="color: var(--lumina-text-heading); letter-spacing: 1.4px;">
                    Mentors
                </h3>

                <div class="flex flex-col gap-3">
                    @forelse($mentors ?? [] as $mentor)
                        <button
                            type="button"
                            class="mentor-trigger group flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left transition-all duration-200 hover:-translate-y-0.5 hover:bg-[rgba(0,106,65,0.08)] hover:shadow-sm"
                            data-mentor-index="{{ $loop->index }}"
                        >
                            <div class="relative">
                                <div class="h-10 w-10 overflow-hidden rounded-full transition-transform duration-200 group-hover:scale-105">
                                    @if($mentor['avatar'] ?? false)
                                        <img src="{{ $mentor['avatar'] }}" alt="{{ $mentor['name'] }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-sm font-semibold" style="background-color: var(--lumina-bg-card); color: var(--lumina-primary);">
                                            {{ substr($mentor['name'] ?? 'M', 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white" style="background-color: #BEC9BF;"></div>
                            </div>
                            <div class="flex min-w-0 flex-1 flex-col">
                                <span class="mentor-name truncate text-sm font-bold" style="color: var(--lumina-text-primary);">
                                    {{ $mentor['name'] ?? 'Mentor Name' }}
                                </span>
                                <span class="mentor-specialty truncate text-[10px]" style="color: var(--lumina-text-secondary);">
                                    {{ $mentor['specialty'] ?? 'Specialist' }}
                                </span>
                            </div>
                        </button>
                    @empty
                        <p class="text-xs" style="color: var(--lumina-text-secondary);">No mentor linked yet. Enroll in a class to see your teachers.</p>
                    @endforelse
                </div>

                <p class="text-xs" style="color: var(--lumina-text-secondary);">
                    Click a mentor to open their information form in the center of the dashboard.
                </p>
            </div>

            <div class="pt-4">
                <a
                    href="{{ route($dashboardMessageCenterRouteName, $dashboardMessageCenterRouteParams) }}"
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
                        <span class="flex min-w-0 flex-col">
                            <span class="truncate text-sm font-medium" style="color: var(--lumina-text-primary);">
                                {{ $course->name }}
                            </span>
                            <span class="text-[10px] font-semibold uppercase tracking-wide" style="color: var(--lumina-text-secondary);">
                                {{ (int) ($course->assigned_students_count ?? 0) }} student{{ (int) ($course->assigned_students_count ?? 0) === 1 ? '' : 's' }}
                            </span>
                        </span>
                        <svg class="h-4 w-4" fill="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                            <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
                        </svg>
                    </a>
                @empty
                    <p class="rounded-lg p-3 text-xs" style="background-color: var(--lumina-bg-card); color: var(--lumina-text-secondary);">
                        No course assignments found yet.
                    </p>
                @endforelse
            </div>
        </div>
    </div>

    <style>
        .mentor-trigger {
            border: 1px solid transparent;
            cursor: pointer;
        }

        .mentor-trigger .mentor-name,
        .mentor-trigger .mentor-specialty {
            transition: color .2s ease;
        }

        .mentor-trigger:hover {
            border-color: rgba(0, 106, 65, 0.22);
            background-color: rgba(0, 106, 65, 0.1);
        }

        .mentor-trigger:hover .mentor-name {
            color: var(--lumina-primary);
        }

        .mentor-trigger:hover .mentor-specialty {
            color: #36556d;
        }
    </style>

    {{-- Mentor Information Modal --}}
    <div id="mentorModal" class="hidden items-center justify-center bg-black/45 backdrop-blur-[1px] px-4" style="position: fixed; inset: 0; width: 100vw; height: 100vh; z-index: 2147483647;">
        <div class="w-full max-w-md rounded-3xl border bg-white p-6 shadow-2xl" style="position: relative; z-index: 2147483647; border-color: rgba(190, 201, 191, 0.25);">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-bold uppercase tracking-wider" style="color: var(--lumina-text-heading); letter-spacing: 1.2px;">Mentor Information</h3>
                <button id="mentorModalClose" type="button" class="rounded-lg px-2 py-1 text-sm font-bold" style="color: var(--lumina-text-muted);">X</button>
            </div>

            <div class="grid grid-cols-1 gap-2">
                <div class="rounded-lg px-3 py-2" style="background-color: var(--lumina-bg-card);">
                    <span class="text-[10px] font-bold uppercase" style="color: var(--lumina-text-muted);">Full Name</span>
                    <p id="mentorFieldName" class="text-sm font-semibold" style="color: var(--lumina-text-primary);"></p>
                </div>
                <div class="rounded-lg px-3 py-2" style="background-color: var(--lumina-bg-card);">
                    <span class="text-[10px] font-bold uppercase" style="color: var(--lumina-text-muted);">Email</span>
                    <p id="mentorFieldEmail" class="text-sm font-semibold" style="color: var(--lumina-text-primary);"></p>
                </div>
                <div class="rounded-lg px-3 py-2" style="background-color: var(--lumina-bg-card);">
                    <span class="text-[10px] font-bold uppercase" style="color: var(--lumina-text-muted);">Phone</span>
                    <p id="mentorFieldPhone" class="text-sm font-semibold" style="color: var(--lumina-text-primary);"></p>
                </div>
                <div class="rounded-lg px-3 py-2" style="background-color: var(--lumina-bg-card);">
                    <span class="text-[10px] font-bold uppercase" style="color: var(--lumina-text-muted);">Preferred Language</span>
                    <p id="mentorFieldLanguage" class="text-sm font-semibold" style="color: var(--lumina-text-primary);"></p>
                </div>
                <div class="rounded-lg px-3 py-2" style="background-color: var(--lumina-bg-card);">
                    <span class="text-[10px] font-bold uppercase" style="color: var(--lumina-text-muted);">Assigned Courses</span>
                    <p id="mentorFieldCourses" class="text-sm font-semibold" style="color: var(--lumina-text-primary);"></p>
                </div>
                <div class="rounded-lg px-3 py-2" style="background-color: var(--lumina-bg-card);">
                    <span class="text-[10px] font-bold uppercase" style="color: var(--lumina-text-muted);">Bio</span>
                    <p id="mentorFieldBio" class="text-sm" style="color: var(--lumina-text-secondary);"></p>
                </div>
            </div>

            <div class="mt-4 flex items-center justify-end gap-2">
                <a id="mentorMessageBtn" href="#" class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2 text-sm font-bold text-white transition-opacity hover:opacity-90" style="background-color: var(--lumina-primary);">
                    <span>Message Teacher</span>
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <script id="studentProficiencyGroups" type="application/json">@json($proficiencyGroups ?? [])</script>
    <script id="studentMentorsData" type="application/json">@json(collect($mentors ?? [])->values())</script>
    <script>
        const PROFICIENCY_GROUPS = JSON.parse(document.getElementById('studentProficiencyGroups')?.textContent || '[]');
        const DEFAULT_PROFICIENCY_GROUP = "{{ $selectedProficiencyGroup ?? 'all' }}";
        const MENTORS_DATA = JSON.parse(document.getElementById('studentMentorsData')?.textContent || '[]');
        const MENTOR_FALLBACK_MESSAGE_URL = "{{ route($dashboardMessageCenterRouteName, $dashboardMessageCenterRouteParams) }}";
        const mentorModal = document.getElementById('mentorModal');
        const mentorModalClose = document.getElementById('mentorModalClose');

        (function initializeProficiencyCard() {
            const selectEl = document.getElementById('proficiencyGroupFilter');
            const centerGroupEl = document.getElementById('proficiencyCenterGroupText');
            const percentEl = document.getElementById('proficiencyPercentText');
            const statusEl = document.getElementById('proficiencyStatusText');
            const insightEl = document.getElementById('proficiencyInsightText');
            const circleEl = document.getElementById('proficiencyProgressCircle');

            if (!selectEl || !centerGroupEl || !percentEl || !statusEl || !insightEl || !circleEl) {
                return;
            }

            const groups = Array.isArray(PROFICIENCY_GROUPS) ? PROFICIENCY_GROUPS : [];

            if (!groups.length) {
                return;
            }

            const chooseColor = (percent) => {
                const value = Number(percent || 0);

                if (value < 25) return '#dc2626';
                if (value < 50) return '#eab308';
                if (value < 75) return '#f97316';
                return '#047857';
            };

            const render = (key) => {
                const group = groups.find(item => String(item.key) === String(key)) || groups[0];
                const percent = Math.max(0, Math.min(100, Number(group.percent || 0)));
                const circumference = 352;
                const strokeColor = chooseColor(percent);

                centerGroupEl.textContent = String(group.displayName || group.label || 'Group');
                percentEl.textContent = `${Math.round(percent)}%`;
                statusEl.textContent = String(group.status || 'No Data');
                statusEl.style.color = strokeColor;
                insightEl.textContent = String(group.insight || 'No graded evaluations yet.');

                circleEl.setAttribute('stroke-dashoffset', String(circumference - ((circumference * percent) / 100)));
                circleEl.setAttribute('stroke', strokeColor);
            };

            selectEl.innerHTML = groups.map(group => {
                const selected = String(group.key) === String(DEFAULT_PROFICIENCY_GROUP) ? 'selected' : '';
                const count = Number(group.count || 0);
                return `<option value="${String(group.key)}" ${selected}>${String(group.label)} (${count})</option>`;
            }).join('');

            selectEl.addEventListener('change', () => {
                render(selectEl.value);
            });

            render(selectEl.value || DEFAULT_PROFICIENCY_GROUP);
        })();

        function mentorValue(value, fallback = 'Not provided') {
            return (value && String(value).trim() !== '') ? String(value) : fallback;
        }

        function openMentorModal(index) {
            const mentor = MENTORS_DATA[index];

            if (!mentor) {
                return;
            }

            document.getElementById('mentorFieldName').textContent = mentorValue(mentor.name, 'Mentor');
            document.getElementById('mentorFieldEmail').textContent = mentorValue(mentor.email);
            document.getElementById('mentorFieldPhone').textContent = mentorValue(mentor.phone);
            document.getElementById('mentorFieldLanguage').textContent = mentorValue(mentor.preferredLanguage, 'Not set');
            document.getElementById('mentorFieldCourses').textContent = (mentor.courses && mentor.courses.length)
                ? mentor.courses.join(', ')
                : 'No assigned courses';
            document.getElementById('mentorFieldBio').textContent = mentorValue(mentor.bio, 'No bio provided yet.');

            const messageBtn = document.getElementById('mentorMessageBtn');
            messageBtn.href = mentor.messageUrl || MENTOR_FALLBACK_MESSAGE_URL;

            mentorModal.classList.remove('hidden');
            mentorModal.classList.add('flex');
        }

        function closeMentorModal() {
            mentorModal.classList.add('hidden');
            mentorModal.classList.remove('flex');
        }

        document.querySelectorAll('.mentor-trigger').forEach((button) => {
            button.addEventListener('click', () => {
                openMentorModal(Number(button.dataset.mentorIndex));
            });
        });

        mentorModalClose?.addEventListener('click', closeMentorModal);

        mentorModal?.addEventListener('click', (event) => {
            if (event.target === mentorModal) {
                closeMentorModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && mentorModal && !mentorModal.classList.contains('hidden')) {
                closeMentorModal();
            }
        });

        (function initializeNextClassLiveCountdown() {
            const countdownEl = document.getElementById('nextClassCountdownLive');

            if (!countdownEl) {
                return;
            }

            const targetRaw = countdownEl.dataset.target || '';
            const targetTime = Date.parse(targetRaw);

            if (Number.isNaN(targetTime)) {
                return;
            }

            const formatPart = (value, suffix) => `${String(Math.max(0, value)).padStart(2, '0')}${suffix}`;

            const tick = () => {
                const totalSeconds = Math.max(0, Math.floor((targetTime - Date.now()) / 1000));

                const days = Math.floor(totalSeconds / 86400);
                const hours = Math.floor((totalSeconds % 86400) / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;

                const parts = [];

                if (days > 0) {
                    parts.push(`${days}d`);
                }

                if (hours > 0) {
                    parts.push(formatPart(hours, 'h'));
                }

                parts.push(formatPart(minutes, 'min'));
                parts.push(formatPart(seconds, 's'));

                countdownEl.textContent = parts.join(':');
            };

            tick();
            setInterval(tick, 1000);
        })();
    </script>
</x-dynamic-component>
