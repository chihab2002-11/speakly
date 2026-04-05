<x-layouts.student :title="__('Academic Information')" :currentRoute="'academic'">
    {{-- Page Header --}}
    <div class="mb-8 flex flex-col gap-2">
        <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
            Student Academic Record
        </h1>
        <p class="text-lg" style="color: var(--lumina-text-secondary);">
            Track your attendance, evaluations, and weekly schedule.
        </p>
    </div>

    {{-- Main Grid Layout --}}
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Left Column: Presence Tracker + Evaluations (2 columns) --}}
        <div class="flex flex-col gap-6 lg:col-span-2">
            {{-- Presence Tracker Card --}}
            <div 
                class="flex flex-col rounded-3xl border p-6 lg:p-8"
                style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;"
            >
                {{-- Header --}}
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <h3 class="text-sm font-bold uppercase tracking-wider" style="color: var(--lumina-text-heading); letter-spacing: 1.4px;">
                            Presence Tracker
                        </h3>
                        <p class="text-xs" style="color: var(--lumina-text-secondary);">
                            Last 4 weeks attendance
                        </p>
                    </div>
                    {{-- Current Streak Badge --}}
                    <div 
                        class="flex items-center gap-2 rounded-full px-4 py-2"
                        style="background-color: rgba(0, 106, 65, 0.1);"
                    >
                        <svg class="h-4 w-4" fill="currentColor" style="color: var(--lumina-primary);" viewBox="0 0 24 24">
                            <path d="M13.5.67s.74 2.65.74 4.8c0 2.06-1.35 3.73-3.41 3.73-2.07 0-3.63-1.67-3.63-3.73l.03-.36C5.21 7.51 4 10.62 4 14c0 4.42 3.58 8 8 8s8-3.58 8-8C20 8.61 17.41 3.8 13.5.67zM11.71 19c-1.78 0-3.22-1.4-3.22-3.14 0-1.62 1.05-2.76 2.81-3.12 1.77-.36 3.6-1.21 4.62-2.58.39 1.29.59 2.65.59 4.04 0 2.65-2.15 4.8-4.8 4.8z"/>
                        </svg>
                        <span class="text-sm font-bold" style="color: var(--lumina-primary);">
                            {{ $currentStreak ?? 12 }} Days
                        </span>
                    </div>
                </div>

                {{-- Heatmap Grid --}}
                <div class="flex flex-col gap-3">
                    {{-- Day Labels Row --}}
                    <div class="grid grid-cols-8 gap-2">
                        <div class="text-xs font-medium" style="color: var(--lumina-text-muted);"></div>
                        @foreach(['M', 'T', 'W', 'T', 'F', 'S', 'S'] as $day)
                            <div class="flex items-center justify-center text-xs font-bold" style="color: var(--lumina-text-secondary);">
                                {{ $day }}
                            </div>
                        @endforeach
                    </div>

                    {{-- Week Rows --}}
                    @php
                        // Attendance data: 1 = present, 0 = absent, null = no class
                        $attendanceData = $attendance ?? [
                            ['week' => 'W1', 'days' => [1, 1, 1, 1, 1, null, null]],
                            ['week' => 'W2', 'days' => [1, 1, 0, 1, 1, null, null]],
                            ['week' => 'W3', 'days' => [1, 1, 1, 1, 1, null, null]],
                            ['week' => 'W4', 'days' => [1, 1, 1, 1, null, null, null]],
                        ];
                    @endphp

                    @foreach($attendanceData as $week)
                        <div class="grid grid-cols-8 gap-2">
                            {{-- Week Label --}}
                            <div class="flex items-center text-xs font-medium" style="color: var(--lumina-text-muted);">
                                {{ $week['week'] }}
                            </div>
                            {{-- Day Cells --}}
                            @foreach($week['days'] as $status)
                                <div 
                                    class="flex h-10 items-center justify-center rounded-lg transition-all hover:scale-105"
                                    style="background-color: {{ $status === 1 ? 'var(--lumina-primary)' : ($status === 0 ? 'var(--lumina-accent-red)' : 'var(--lumina-border)') }};"
                                >
                                    @if($status === 1)
                                        <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                        </svg>
                                    @elseif($status === 0)
                                        <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                                        </svg>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                {{-- Legend --}}
                <div class="mt-6 flex items-center gap-6 border-t pt-4" style="border-color: var(--lumina-border);">
                    <div class="flex items-center gap-2">
                        <div class="h-4 w-4 rounded" style="background-color: var(--lumina-primary);"></div>
                        <span class="text-xs font-medium" style="color: var(--lumina-text-secondary);">Present</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-4 w-4 rounded" style="background-color: var(--lumina-accent-red);"></div>
                        <span class="text-xs font-medium" style="color: var(--lumina-text-secondary);">Absent</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-4 w-4 rounded" style="background-color: var(--lumina-border);"></div>
                        <span class="text-xs font-medium" style="color: var(--lumina-text-secondary);">No Class</span>
                    </div>
                </div>
            </div>

            {{-- Detailed Evaluations Card --}}
            <div 
                class="flex flex-col rounded-3xl border p-6 lg:p-8"
                style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;"
            >
                {{-- Header --}}
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <h3 class="text-sm font-bold uppercase tracking-wider" style="color: var(--lumina-text-heading); letter-spacing: 1.4px;">
                            Detailed Evaluations
                        </h3>
                        <p class="text-xs" style="color: var(--lumina-text-secondary);">
                            Recent assessments and feedback
                        </p>
                    </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[600px]">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--lumina-border);">
                                <th class="pb-4 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted); letter-spacing: 1px;">
                                    Subject
                                </th>
                                <th class="pb-4 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted); letter-spacing: 1px;">
                                    Assessment
                                </th>
                                <th class="pb-4 text-center text-xs font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted); letter-spacing: 1px;">
                                    Score
                                </th>
                                <th class="pb-4 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted); letter-spacing: 1px;">
                                    Faculty Feedback
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $evaluations = $evaluations ?? [
                                    [
                                        'subject' => 'French B2',
                                        'assessment' => 'Oral Presentation',
                                        'score' => 92,
                                        'feedback' => 'Excellent pronunciation and fluency. Continue practicing complex grammar structures.',
                                    ],
                                    [
                                        'subject' => 'French B2',
                                        'assessment' => 'Written Essay',
                                        'score' => 88,
                                        'feedback' => 'Strong vocabulary usage. Work on paragraph transitions.',
                                    ],
                                    [
                                        'subject' => 'Spanish A2',
                                        'assessment' => 'Listening Comprehension',
                                        'score' => 85,
                                        'feedback' => 'Good understanding of native speakers. Focus on regional accents.',
                                    ],
                                    [
                                        'subject' => 'Spanish A2',
                                        'assessment' => 'Grammar Quiz',
                                        'score' => 78,
                                        'feedback' => 'Review subjunctive mood conjugations.',
                                    ],
                                ];
                            @endphp

                            @foreach($evaluations as $eval)
                                <tr style="border-bottom: 1px solid var(--lumina-border);">
                                    <td class="py-4">
                                        <span class="text-sm font-semibold" style="color: var(--lumina-text-primary);">
                                            {{ $eval['subject'] }}
                                        </span>
                                    </td>
                                    <td class="py-4">
                                        <span class="text-sm" style="color: var(--lumina-text-secondary);">
                                            {{ $eval['assessment'] }}
                                        </span>
                                    </td>
                                    <td class="py-4 text-center">
                                        @php
                                            $scoreColor = $eval['score'] >= 90 ? 'var(--lumina-primary)' : 
                                                         ($eval['score'] >= 80 ? '#5E70BB' : 
                                                         ($eval['score'] >= 70 ? '#D97706' : 'var(--lumina-accent-red)'));
                                        @endphp
                                        <span 
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold text-white"
                                            style="background-color: {{ $scoreColor }};"
                                        >
                                            {{ $eval['score'] }}
                                        </span>
                                    </td>
                                    <td class="py-4">
                                        <p class="max-w-xs text-xs leading-relaxed" style="color: var(--lumina-text-secondary);">
                                            {{ $eval['feedback'] }}
                                        </p>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- View All Link --}}
                <div class="mt-4 flex justify-end">
                    <a 
                        href="#" 
                        class="inline-flex items-center gap-2 text-sm font-bold transition-colors hover:opacity-80"
                        style="color: var(--lumina-primary);"
                    >
                        <span>View All Evaluations</span>
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- Right Column: Weekly Schedule (1 column) --}}
        <div class="lg:col-span-1">
            {{-- Weekly Schedule Card --}}
            <div 
                class="flex flex-col rounded-3xl border p-6 lg:p-8"
                style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;"
            >
                {{-- Header --}}
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <h3 class="text-sm font-bold uppercase tracking-wider" style="color: var(--lumina-text-heading); letter-spacing: 1.4px;">
                            Weekly Schedule
                        </h3>
                        <p class="text-xs" style="color: var(--lumina-text-secondary);">
                            Your class timetable
                        </p>
                    </div>
                    {{-- Week Selector --}}
                    <div class="flex items-center gap-2">
                        <button class="flex h-8 w-8 items-center justify-center rounded-lg transition-colors hover:bg-gray-100" style="color: var(--lumina-text-muted);">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
                            </svg>
                        </button>
                        <span class="text-xs font-bold" style="color: var(--lumina-text-secondary);">
                            This Week
                        </span>
                        <button class="flex h-8 w-8 items-center justify-center rounded-lg transition-colors hover:bg-gray-100" style="color: var(--lumina-text-muted);">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Schedule Grid --}}
                @php
                    $timeSlots = ['09:00', '11:30', '14:30'];
                    $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
                    
                    // Schedule data with colors
                    $schedule = $schedule ?? [
                        'Mon' => [
                            '09:00' => ['course' => 'French B2', 'color' => '#006A41', 'room' => 'Room 101'],
                            '14:30' => ['course' => 'Spanish A2', 'color' => '#5E70BB', 'room' => 'Room 203'],
                        ],
                        'Tue' => [
                            '11:30' => ['course' => 'French B2', 'color' => '#006A41', 'room' => 'Room 101'],
                        ],
                        'Wed' => [
                            '09:00' => ['course' => 'Spanish A2', 'color' => '#5E70BB', 'room' => 'Room 203'],
                            '14:30' => ['course' => 'French B2', 'color' => '#006A41', 'room' => 'Room 101'],
                        ],
                        'Thu' => [
                            '09:00' => ['course' => 'Tutorial', 'color' => '#64748B', 'room' => 'Lab 2'],
                            '11:30' => ['course' => 'French B2', 'color' => '#006A41', 'room' => 'Room 101'],
                        ],
                        'Fri' => [
                            '09:00' => ['course' => 'Spanish A2', 'color' => '#5E70BB', 'room' => 'Room 203'],
                        ],
                    ];
                @endphp

                <div class="overflow-x-auto">
                    <table class="w-full">
                        {{-- Days Header --}}
                        <thead>
                            <tr>
                                <th class="w-16 pb-3 text-left text-[10px] font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted);">
                                    Time
                                </th>
                                @foreach($days as $day)
                                    <th class="pb-3 text-center text-[10px] font-bold uppercase tracking-wider" style="color: var(--lumina-text-secondary);">
                                        {{ $day }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timeSlots as $time)
                                <tr>
                                    {{-- Time Label --}}
                                    <td class="py-2 text-xs font-medium" style="color: var(--lumina-text-muted);">
                                        {{ $time }}
                                    </td>
                                    {{-- Day Cells --}}
                                    @foreach($days as $day)
                                        <td class="p-1">
                                            @if(isset($schedule[$day][$time]))
                                                @php $class = $schedule[$day][$time]; @endphp
                                                <div 
                                                    class="flex flex-col items-center justify-center rounded-lg p-2 text-center transition-all hover:scale-105"
                                                    style="background-color: {{ $class['color'] }}; min-height: 60px;"
                                                    title="{{ $class['course'] }} - {{ $class['room'] }}"
                                                >
                                                    <span class="text-[10px] font-bold leading-tight text-white">
                                                        {{ Str::limit($class['course'], 10) }}
                                                    </span>
                                                    <span class="mt-1 text-[8px] text-white/80">
                                                        {{ $class['room'] }}
                                                    </span>
                                                </div>
                                            @else
                                                <div 
                                                    class="flex items-center justify-center rounded-lg"
                                                    style="background-color: var(--lumina-bg-card); min-height: 60px;"
                                                >
                                                    <span class="text-[10px]" style="color: var(--lumina-text-muted);">—</span>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Legend --}}
                <div class="mt-6 flex flex-wrap items-center gap-3 border-t pt-4" style="border-color: var(--lumina-border);">
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded" style="background-color: #006A41;"></div>
                        <span class="text-[10px] font-medium" style="color: var(--lumina-text-secondary);">French</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded" style="background-color: #5E70BB;"></div>
                        <span class="text-[10px] font-medium" style="color: var(--lumina-text-secondary);">Spanish</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded" style="background-color: #64748B;"></div>
                        <span class="text-[10px] font-medium" style="color: var(--lumina-text-secondary);">Tutorial</span>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div class="mt-6 grid grid-cols-2 gap-4 border-t pt-4" style="border-color: var(--lumina-border);">
                    <div class="flex flex-col gap-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted);">
                            Classes/Week
                        </span>
                        <span class="text-2xl font-black" style="color: var(--lumina-text-primary);">
                            {{ $classesPerWeek ?? 8 }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted);">
                            Hours/Week
                        </span>
                        <span class="text-2xl font-black" style="color: var(--lumina-text-primary);">
                            {{ $hoursPerWeek ?? 12 }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.student>
