<x-layouts.parent 
    :title="'Calendar'"
    :pageTitle="'Calendar'"
    :currentRoute="'calendar'"
    :user="$user ?? null"
    :children="$children ?? []"
    :selectedChild="$selectedChild ?? null"
>
    {{-- Page Header --}}
    <div class="mb-8 flex flex-col gap-2">
        <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
            Children's Timetable
        </h1>
        <p class="text-lg" style="color: var(--lumina-text-secondary);">
            View your children's weekly schedules and upcoming classes.
        </p>
    </div>

    {{-- Child Selector Tabs --}}
    <div class="mb-6 flex flex-wrap gap-3">
        @php
            $childrenData = $children ?? [
                ['id' => 1, 'name' => 'Alex Johnson', 'initials' => 'A', 'color' => 'var(--lumina-child-1)', 'textColor' => 'var(--lumina-child-1-text)'],
                ['id' => 2, 'name' => 'Sophie Johnson', 'initials' => 'S', 'color' => 'var(--lumina-child-2)', 'textColor' => 'var(--lumina-child-2-text)'],
            ];
            $selectedChildId = $selectedChild['id'] ?? 1;
        @endphp
        
        @foreach($childrenData as $child)
            <button 
                class="flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold transition-all hover:shadow-md"
                style="background-color: {{ ($child['id'] ?? 1) === $selectedChildId ? $child['color'] : '#FFFFFF' }}; 
                       color: {{ ($child['id'] ?? 1) === $selectedChildId ? $child['textColor'] : 'var(--lumina-text-primary)' }};
                       border: 1px solid {{ ($child['id'] ?? 1) === $selectedChildId ? 'transparent' : 'var(--lumina-border-light)' }};"
            >
                <span 
                    class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold"
                    style="background-color: {{ ($child['id'] ?? 1) === $selectedChildId ? 'rgba(255,255,255,0.3)' : $child['color'] }}; 
                           color: {{ ($child['id'] ?? 1) === $selectedChildId ? 'inherit' : $child['textColor'] }};"
                >
                    {{ $child['initials'] ?? substr($child['name'], 0, 1) }}
                </span>
                {{ $child['name'] }}
            </button>
        @endforeach
    </div>

    {{-- Main Grid Layout --}}
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Weekly Timetable (2 columns) --}}
        <div class="flex flex-col gap-6 lg:col-span-2">
            {{-- Timetable Card --}}
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
                            {{ $selectedChild['name'] ?? 'Alex Johnson' }}'s classes this week
                        </p>
                    </div>
                    {{-- Week Navigator --}}
                    <div class="flex items-center gap-2">
                        <button class="flex h-8 w-8 items-center justify-center rounded-lg transition-colors hover:bg-gray-100">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <span class="text-sm font-semibold" style="color: var(--lumina-text-primary);">
                            {{ $currentWeek ?? 'April 1 - 7, 2024' }}
                        </span>
                        <button class="flex h-8 w-8 items-center justify-center rounded-lg transition-colors hover:bg-gray-100">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-muted);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Timetable Grid --}}
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px]">
                        <thead>
                            <tr>
                                <th class="w-20 pb-4 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted); letter-spacing: 1px;">
                                    Time
                                </th>
                                @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri'] as $day)
                                    <th class="pb-4 text-center text-xs font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted); letter-spacing: 1px;">
                                        {{ $day }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $scheduleData = $schedule ?? [
                                    ['time' => '08:00', 'slots' => [
                                        ['subject' => 'Italian', 'room' => 'A101', 'teacher' => 'Ms. Ferrara', 'color' => '#22C55E'],
                                        null,
                                        ['subject' => 'Italian', 'room' => 'A101', 'teacher' => 'Ms. Ferrara', 'color' => '#22C55E'],
                                        null,
                                        ['subject' => 'German', 'room' => 'B205', 'teacher' => 'Mr. Schmidt', 'color' => '#3B82F6'],
                                    ]],
                                    ['time' => '09:30', 'slots' => [
                                        ['subject' => 'German', 'room' => 'B205', 'teacher' => 'Mr. Schmidt', 'color' => '#3B82F6'],
                                        ['subject' => 'Italian', 'room' => 'A101', 'teacher' => 'Ms. Ferrara', 'color' => '#22C55E'],
                                        null,
                                        ['subject' => 'German', 'room' => 'B205', 'teacher' => 'Mr. Schmidt', 'color' => '#3B82F6'],
                                        ['subject' => 'Italian', 'room' => 'A101', 'teacher' => 'Ms. Ferrara', 'color' => '#22C55E'],
                                    ]],
                                    ['time' => '11:00', 'slots' => [
                                        null,
                                        ['subject' => 'German', 'room' => 'B205', 'teacher' => 'Mr. Schmidt', 'color' => '#3B82F6'],
                                        ['subject' => 'Italian Conv.', 'room' => 'C102', 'teacher' => 'Mrs. Rossi', 'color' => '#10B981'],
                                        ['subject' => 'Italian', 'room' => 'A101', 'teacher' => 'Ms. Ferrara', 'color' => '#22C55E'],
                                        null,
                                    ]],
                                    ['time' => '14:00', 'slots' => [
                                        ['subject' => 'German Conv.', 'room' => 'B210', 'teacher' => 'Mr. Weber', 'color' => '#6366F1'],
                                        null,
                                        ['subject' => 'German', 'room' => 'B205', 'teacher' => 'Mr. Schmidt', 'color' => '#3B82F6'],
                                        null,
                                        ['subject' => 'Italian Conv.', 'room' => 'C102', 'teacher' => 'Mrs. Rossi', 'color' => '#10B981'],
                                    ]],
                                    ['time' => '15:30', 'slots' => [
                                        null,
                                        ['subject' => 'Italian Conv.', 'room' => 'C102', 'teacher' => 'Mrs. Rossi', 'color' => '#10B981'],
                                        null,
                                        ['subject' => 'German Conv.', 'room' => 'B210', 'teacher' => 'Mr. Weber', 'color' => '#6366F1'],
                                        null,
                                    ]],
                                ];
                            @endphp

                            @foreach($scheduleData as $row)
                                <tr>
                                    <td class="py-2 pr-4 text-xs font-medium" style="color: var(--lumina-text-muted);">
                                        {{ $row['time'] }}
                                    </td>
                                    @foreach($row['slots'] as $slot)
                                        <td class="p-1">
                                            @if($slot)
                                                <div 
                                                    class="flex flex-col gap-0.5 rounded-xl p-3 transition-all hover:scale-[1.02] hover:shadow-md"
                                                    style="background-color: {{ $slot['color'] }}15; border-left: 3px solid {{ $slot['color'] }};"
                                                >
                                                    <span class="text-xs font-bold" style="color: {{ $slot['color'] }};">
                                                        {{ $slot['subject'] }}
                                                    </span>
                                                    <span class="text-[10px]" style="color: var(--lumina-text-muted);">
                                                        {{ $slot['room'] }}
                                                    </span>
                                                </div>
                                            @else
                                                <div class="h-16 rounded-xl" style="background-color: var(--lumina-bg-card);"></div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Legend --}}
                <div class="mt-6 flex flex-wrap items-center gap-4 border-t pt-4" style="border-color: var(--lumina-border);">
                    <span class="text-xs font-medium" style="color: var(--lumina-text-muted);">Subjects:</span>
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded" style="background-color: #22C55E;"></div>
                        <span class="text-xs" style="color: var(--lumina-text-secondary);">Italian</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded" style="background-color: #3B82F6;"></div>
                        <span class="text-xs" style="color: var(--lumina-text-secondary);">German</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded" style="background-color: #10B981;"></div>
                        <span class="text-xs" style="color: var(--lumina-text-secondary);">Italian Conv.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded" style="background-color: #6366F1;"></div>
                        <span class="text-xs" style="color: var(--lumina-text-secondary);">German Conv.</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Today's Classes & Upcoming Events --}}
        <div class="flex flex-col gap-6">
            {{-- Today's Classes Card --}}
            <div 
                class="flex flex-col rounded-3xl border p-6"
                style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;"
            >
                <div class="mb-4 flex items-center gap-2">
                    <svg class="h-5 w-5" fill="currentColor" style="color: var(--lumina-primary);" viewBox="0 0 24 24">
                        <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm-8 4H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z"/>
                    </svg>
                    <h3 class="text-base font-bold" style="color: var(--lumina-text-primary);">
                        Today's Classes
                    </h3>
                </div>

                <div class="flex flex-col gap-3">
                    @php
                        $todaysClasses = $todaysClasses ?? [
                            ['time' => '08:00 - 09:15', 'subject' => 'Italian', 'teacher' => 'Ms. Ferrara', 'room' => 'A101', 'status' => 'completed'],
                            ['time' => '09:30 - 10:45', 'subject' => 'German', 'teacher' => 'Mr. Schmidt', 'room' => 'B205', 'status' => 'in_progress'],
                            ['time' => '14:00 - 15:15', 'subject' => 'German Conv.', 'teacher' => 'Mr. Weber', 'room' => 'B210', 'status' => 'upcoming'],
                        ];
                    @endphp

                    @foreach($todaysClasses as $class)
                        <div 
                            class="flex items-center gap-3 rounded-xl p-3 transition-colors"
                            style="background-color: {{ $class['status'] === 'in_progress' ? 'var(--lumina-accent-green-bg)' : 'var(--lumina-bg-card)' }};"
                        >
                            {{-- Status Indicator --}}
                            <div 
                                class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full"
                                style="background-color: {{ $class['status'] === 'completed' ? 'var(--lumina-primary)' : ($class['status'] === 'in_progress' ? '#F59E0B' : 'var(--lumina-border)') }};"
                            >
                                @if($class['status'] === 'completed')
                                    <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                    </svg>
                                @elseif($class['status'] === 'in_progress')
                                    <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="5"/>
                                    </svg>
                                @else
                                    <svg class="h-4 w-4" fill="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                                        <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                                    </svg>
                                @endif
                            </div>
                            
                            {{-- Class Info --}}
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold" style="color: var(--lumina-text-primary);">
                                        {{ $class['subject'] }}
                                    </span>
                                    @if($class['status'] === 'in_progress')
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase" style="background-color: #F59E0B; color: white;">
                                            Now
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs" style="color: var(--lumina-text-muted);">
                                    {{ $class['time'] }} &bull; {{ $class['room'] }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Upcoming Events Card --}}
            <div 
                class="flex flex-col rounded-3xl border p-6"
                style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;"
            >
                <div class="mb-4 flex items-center gap-2">
                    <svg class="h-5 w-5" fill="currentColor" style="color: var(--lumina-primary);" viewBox="0 0 24 24">
                        <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/>
                    </svg>
                    <h3 class="text-base font-bold" style="color: var(--lumina-text-primary);">
                        Upcoming Events
                    </h3>
                </div>

                <div class="flex flex-col gap-3">
                    @php
                        $upcomingEvents = $upcomingEvents ?? [
                            ['date' => 'Apr 10', 'title' => 'Parent-Teacher Meeting', 'type' => 'meeting', 'color' => '#8B5CF6'],
                            ['date' => 'Apr 15', 'title' => 'Italian Oral Exam', 'type' => 'exam', 'color' => '#EF4444'],
                            ['date' => 'Apr 20', 'title' => 'German Culture Day', 'type' => 'event', 'color' => '#3B82F6'],
                        ];
                    @endphp

                    @foreach($upcomingEvents as $event)
                        <div class="flex items-center gap-3 rounded-xl p-3" style="background-color: var(--lumina-bg-card);">
                            <div 
                                class="flex h-10 w-10 flex-shrink-0 flex-col items-center justify-center rounded-xl"
                                style="background-color: {{ $event['color'] }}15;"
                            >
                                <span class="text-[10px] font-bold uppercase" style="color: {{ $event['color'] }};">
                                    {{ explode(' ', $event['date'])[0] }}
                                </span>
                                <span class="text-sm font-black" style="color: {{ $event['color'] }};">
                                    {{ explode(' ', $event['date'])[1] }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-semibold" style="color: var(--lumina-text-primary);">
                                    {{ $event['title'] }}
                                </p>
                                <span 
                                    class="text-[10px] font-medium uppercase"
                                    style="color: {{ $event['color'] }};"
                                >
                                    {{ $event['type'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Quick Stats Card --}}
            <div 
                class="rounded-3xl p-6"
                style="background-color: var(--lumina-dark-green);"
            >
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-white" style="letter-spacing: 1.4px;">
                    This Week
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col">
                        <span class="text-3xl font-black text-white">12</span>
                        <span class="text-xs" style="color: #A7F3D0;">Total Classes</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-3xl font-black text-white">6h</span>
                        <span class="text-xs" style="color: #A7F3D0;">Italian</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-3xl font-black text-white">6h</span>
                        <span class="text-xs" style="color: #A7F3D0;">German</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-3xl font-black text-white">100%</span>
                        <span class="text-xs" style="color: #A7F3D0;">Attendance</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.parent>
