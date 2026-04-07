<x-layouts.teacher :title="__('Teacher Dashboard')" :currentRoute="'role.dashboard'">
    {{-- Page Header --}}
    <div class="mb-8">
        {{-- Welcome Section --}}
        <div class="mb-6">
            <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
                Welcome back, {{ explode(' ', $user->name ?? 'Teacher')[0] }}
            </h1>
            <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
                You have {{ $totalClassesPerWeek ?? 10 }} classes per week
            </p>
        </div>
    </div>

    {{-- Weekly Timetable Section --}}
    <div class="mb-8 rounded-3xl border p-6" style="background: white; border-color: var(--lumina-border-light);">
        {{-- Timetable Header --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold tracking-tight" style="color: var(--lumina-text-primary);">TIME TABLE</h2>
                <p class="text-sm" style="color: var(--lumina-text-muted);">Academic Year {{ now()->year }}-{{ now()->year + 1 }}</p>
            </div>
            {{-- Print Button --}}
            <button 
                class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]"
                style="background-color: var(--lumina-primary);"
                onclick="window.print()"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
        </div>

        {{-- Timetable Grid --}}
        <div class="overflow-x-auto">
            <table class="w-full border-collapse min-w-[800px]">
                <thead>
                    <tr>
                        <th class="p-3 text-left text-sm font-semibold border-b-2" style="color: var(--lumina-text-muted); border-color: var(--lumina-border); min-width: 100px;">TIME</th>
                        @foreach(['08:00 - 09:30', '09:30 - 11:00', '11:00 - 12:30', '12:30 - 14:00', '14:00 - 15:30', '15:30 - 17:00'] as $timeSlot)
                            <th class="p-3 text-center text-xs font-semibold border-b-2" style="color: var(--lumina-text-muted); border-color: var(--lumina-border); min-width: 110px;">{{ $timeSlot }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $days = ['SATURDAY', 'SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY'];
                        
                        // Mock timetable data - structured as [day][timeSlot] => class info
                        $timetableData = $weeklySchedule ?? [
                            'SATURDAY' => [],
                            'SUNDAY' => [
                                2 => ['name' => 'IELTS Group', 'room' => 'Room 101', 'color' => '#D1FAE5', 'border' => '#10B981'],
                                3 => ['name' => 'IELTS Group', 'room' => 'Room 101', 'color' => '#E0E7FF', 'border' => '#6366F1'],
                                4 => ['name' => 'IELTS Group', 'room' => 'Room 101', 'color' => '#E0E7FF', 'border' => '#6366F1'],
                                5 => ['name' => 'IELTS Group', 'room' => 'Room 101', 'color' => '#E0E7FF', 'border' => '#6366F1'],
                            ],
                            'MONDAY' => [],
                            'TUESDAY' => [
                                2 => ['name' => 'CEFR B2', 'room' => 'Room 203', 'color' => '#D1FAE5', 'border' => '#10B981'],
                                3 => ['name' => 'CEFR B2', 'room' => 'Room 203', 'color' => '#D1FAE5', 'border' => '#10B981'],
                            ],
                            'WEDNESDAY' => [],
                            'THURSDAY' => [
                                2 => ['name' => 'VIP', 'room' => 'Room 203', 'color' => '#F3F4F6', 'border' => '#6B7280'],
                            ],
                            'FRIDAY' => [],
                        ];
                    @endphp

                    @foreach($days as $day)
                        <tr class="border-b" style="border-color: var(--lumina-border-light);">
                            <td class="p-3 text-sm font-semibold h-16" style="color: var(--lumina-text-primary);">{{ $day }}</td>
                            @for($slot = 0; $slot < 6; $slot++)
                                <td class="p-2 text-center h-16">
                                    @if(isset($timetableData[$day][$slot]))
                                        @php $class = $timetableData[$day][$slot]; @endphp
                                        <div 
                                            class="rounded-lg p-3 border-l-4 h-full flex flex-col justify-center transition-all duration-200 hover:shadow-md cursor-pointer"
                                            style="background-color: {{ $class['color'] }}; border-left-color: {{ $class['border'] }};"
                                        >
                                            <p class="text-xs font-bold truncate" style="color: var(--lumina-text-primary);">{{ $class['name'] }}</p>
                                            <p class="text-xs mt-1" style="color: var(--lumina-text-muted);">{{ $class['room'] }}</p>
                                        </div>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Bottom Grid: Stats + Quick Resources --}}
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Stats Cards Column --}}
        <div class="space-y-4">
            {{-- Total Students Card --}}
            <div class="card-hover rounded-2xl border p-5 transition-all duration-200" style="background: white; border-color: var(--lumina-border-light);">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm" style="color: var(--lumina-text-muted);">Total Students</p>
                        <p class="text-3xl font-bold mt-1" style="color: var(--lumina-text-primary);">{{ $totalStudents ?? 142 }}</p>
                        <p class="text-xs mt-1 flex items-center gap-1" style="color: var(--lumina-accent-green);">
                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            +4 this week
                        </p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full" style="background-color: #DBEAFE;">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" style="color: #3B82F6;" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- Active Classes Card --}}
            <div class="card-hover rounded-2xl border p-5 transition-all duration-200" style="background: white; border-color: var(--lumina-border-light);">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm" style="color: var(--lumina-text-muted);">Active Classes</p>
                        <p class="text-3xl font-bold mt-1" style="color: var(--lumina-text-primary);">{{ $activeClasses ?? 8 }}</p>
                        <p class="text-xs mt-1" style="color: var(--lumina-text-muted);">
                            Advanced Level (C1)
                        </p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full" style="background-color: var(--lumina-accent-green-light);">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" style="color: var(--lumina-accent-green-dark);" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Resources - Takes 2 columns --}}
        <div class="lg:col-span-2">
            <div class="rounded-3xl border p-6 h-full" style="background: linear-gradient(135deg, rgba(189, 217, 206, 0.3) 0%, rgba(209, 250, 229, 0.2) 100%); border-color: var(--lumina-border-light);">
                <div class="mb-5 flex items-center justify-between">
                    <h2 class="text-lg font-bold" style="color: var(--lumina-text-primary);">Quick Resources</h2>
                    <a 
                        href="{{ Route::has('teacher.resources') ? route('teacher.resources') : '#' }}"
                        class="text-sm font-semibold transition-colors duration-200 hover:opacity-80"
                        style="color: var(--lumina-primary);"
                    >
                        View All
                    </a>
                </div>

                {{-- Resource Cards Grid --}}
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    @foreach([
                        ['name' => 'Templates', 'icon' => 'template', 'color' => '#EFF6FF', 'iconColor' => '#3B82F6'],
                        ['name' => 'Flash Quizzes', 'icon' => 'lightning', 'color' => '#FEF3C7', 'iconColor' => '#D97706'],
                        ['name' => 'AI Assistant', 'icon' => 'sparkles', 'color' => '#F3E8FF', 'iconColor' => '#9333EA'],
                        ['name' => 'Idiom Bank', 'icon' => 'book', 'color' => '#ECFDF5', 'iconColor' => '#059669'],
                    ] as $resource)
                        <div 
                            class="group flex flex-col items-center justify-center gap-3 rounded-2xl border bg-white p-5 transition-all duration-200 hover:shadow-lg hover:scale-[1.02] cursor-pointer"
                            style="border-color: var(--lumina-border-light);"
                        >
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl transition-transform duration-200 group-hover:scale-110" style="background-color: {{ $resource['color'] }};">
                                @switch($resource['icon'])
                                    @case('template')
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" style="color: {{ $resource['iconColor'] }};" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                                        </svg>
                                        @break
                                    @case('lightning')
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" style="color: {{ $resource['iconColor'] }};" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        @break
                                    @case('sparkles')
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" style="color: {{ $resource['iconColor'] }};" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                        </svg>
                                        @break
                                    @case('book')
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" style="color: {{ $resource['iconColor'] }};" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                        @break
                                @endswitch
                            </div>
                            <p class="text-sm font-medium text-center" style="color: var(--lumina-text-primary);">{{ $resource['name'] }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- Decorative book illustration in corner --}}
                <div class="hidden lg:block absolute bottom-4 right-4 opacity-10">
                    <svg class="h-24 w-24" fill="currentColor" style="color: var(--lumina-primary);" viewBox="0 0 24 24">
                        <path d="M21 5c-1.11-.35-2.33-.5-3.5-.5-1.95 0-4.05.4-5.5 1.5-1.45-1.1-3.55-1.5-5.5-1.5S2.45 4.9 1 6v14.65c0 .25.25.5.5.5.1 0 .15-.05.25-.05C3.1 20.45 5.05 20 6.5 20c1.95 0 4.05.4 5.5 1.5 1.35-.85 3.8-1.5 5.5-1.5 1.65 0 3.35.3 4.75 1.05.1.05.15.05.25.05.25 0 .5-.25.5-.5V6c-.6-.45-1.25-.75-2-1zm0 13.5c-1.1-.35-2.3-.5-3.5-.5-1.7 0-4.15.65-5.5 1.5V8c1.35-.85 3.8-1.5 5.5-1.5 1.2 0 2.4.15 3.5.5v11.5z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</x-layouts.teacher>
