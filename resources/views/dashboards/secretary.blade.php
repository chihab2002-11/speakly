<x-layouts.secretary :title="__('Secretary Dashboard')" :current-route="'role.dashboard'">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
                Timetable Hub
            </h1>
            <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
                Switch between teacher, student, and rooms timetables from one workspace.
            </p>
        </div>

        <a
            href="{{ route('secretary.timetable.index') }}"
            class="inline-flex items-center rounded-xl border px-4 py-2 text-sm font-semibold transition hover:bg-gray-50"
            style="border-color: var(--lumina-border); color: var(--lumina-text-primary);"
        >
            Open Full Explorer
        </a>
    </div>

    <section class="rounded-3xl border p-5" style="background: white; border-color: var(--lumina-border-light);">
        @php
            $modes = [
                'rooms' => 'Rooms Timetable',
                'classes' => 'Classes Timetable',
                'teacher' => 'Teacher Timetable',
                'student' => 'Student Timetable',
            ];
        @endphp

        <div class="mb-5 flex flex-wrap gap-2">
            @foreach($modes as $modeKey => $modeLabel)
                @if($mode === $modeKey)
                    <a
                        href="{{ route('role.dashboard', ['role' => 'secretary', 'mode' => $modeKey]) }}"
                        class="rounded-xl border px-3.5 py-2 text-sm font-semibold transition"
                        style="background: var(--lumina-primary); color: white; border-color: var(--lumina-primary);"
                    >
                        {{ $modeLabel }}
                    </a>
                @else
                    <a
                        href="{{ route('role.dashboard', ['role' => 'secretary', 'mode' => $modeKey]) }}"
                        class="rounded-xl border px-3.5 py-2 text-sm font-semibold transition"
                        style="background: white; color: var(--lumina-text-secondary); border-color: var(--lumina-border);"
                    >
                        {{ $modeLabel }}
                    </a>
                @endif
            @endforeach
        </div>

        <form method="GET" action="{{ route('role.dashboard', ['role' => 'secretary']) }}" class="space-y-5">
            <input type="hidden" name="mode" value="{{ $mode }}">

            <div class="grid gap-4 md:grid-cols-4">
                @if($mode === 'teacher')
                    <div>
                        <label for="teacher_id" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Teacher</label>
                        <select id="teacher_id" name="teacher_id" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color: var(--lumina-border); color: var(--lumina-text-primary); background: #fff;">
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected((int) $selectedTeacherId === (int) $teacher->id)>{{ $teacher->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif($mode === 'student')
                    <div>
                        <label for="student_id" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Student</label>
                        <select id="student_id" name="student_id" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color: var(--lumina-border); color: var(--lumina-text-primary); background: #fff;">
                            <option value="">Select Student</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" @selected((int) $selectedStudentId === (int) $student->id)>{{ $student->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif($mode === 'classes')
                    <div>
                        <label for="class_id" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Class / Course Group</label>
                        <select id="class_id" name="class_id" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color: var(--lumina-border); color: var(--lumina-text-primary); background: #fff;">
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" @selected((int) $selectedClassId === (int) $class->id)>
                                    {{ $class->course?->name ?? 'Course' }} - Group #{{ $class->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div>
                        <label for="room_id" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Room</label>
                        <select id="room_id" name="room_id" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color: var(--lumina-border); color: var(--lumina-text-primary); background: #fff;">
                            <option value="">All Rooms</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" @selected((int) $selectedRoomId === (int) $room->id)>{{ $room->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-xl px-4 py-2 text-sm font-semibold text-white" style="background: var(--lumina-primary);">
                        Apply
                    </button>
                    <a href="{{ route('role.dashboard', ['role' => 'secretary', 'mode' => $mode]) }}" class="rounded-xl border px-4 py-2 text-sm font-semibold" style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </section>

    <section class="mt-6 rounded-3xl border p-5" style="background: white; border-color: var(--lumina-border-light);">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-xl font-bold" style="color: var(--lumina-text-primary);">Timetable</h2>
            <p class="text-sm font-semibold" style="color: var(--lumina-text-muted);">{{ $totalSchedules }} schedule item(s)</p>
        </div>

        @if($mode === 'student' && $selectedStudentId === null)
            <div class="mb-4 rounded-xl border px-4 py-3 text-sm font-semibold" style="background-color: #FEFCE8; border-color: #FDE68A; color: #92400E;">
                Select a student to display timetable.
            </div>
        @endif

        @if($mode === 'teacher' && $selectedTeacherId === null)
            <div class="mb-4 rounded-xl border px-4 py-3 text-sm font-semibold" style="background-color: #FEFCE8; border-color: #FDE68A; color: #92400E;">
                Select a teacher to display timetable.
            </div>
        @endif

        @if($mode === 'classes' && $selectedClassId === null)
            <div class="mb-4 rounded-xl border px-4 py-3 text-sm font-semibold" style="background-color: #FEFCE8; border-color: #FDE68A; color: #92400E;">
                Select a class to display timetable.
            </div>
        @endif

        @if(collect($timeSlots)->isEmpty())
            <div class="rounded-xl border px-4 py-3 text-sm font-semibold" style="background-color: #FEFCE8; border-color: #FDE68A; color: #92400E;">
                No timetable records for the selected filters.
            </div>
        @else
            <div class="overflow-x-auto rounded-2xl border" style="border-color: var(--lumina-border);">
                <table class="w-full min-w-[940px] text-sm">
                    <thead style="background-color: #F8FAFC;">
                        <tr>
                            <th class="border-b px-3 py-3 text-left font-semibold" style="border-color: var(--lumina-border); color: var(--lumina-text-muted); width: 140px;">Time</th>
                            @foreach($visibleDays as $day)
                                <th class="border-b px-3 py-3 text-left font-semibold" style="border-color: var(--lumina-border); color: var(--lumina-text-muted); min-width: 220px;">
                                    {{ ucfirst($day) }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timeSlots as $slot)
                            <tr>
                                <td class="border-b px-3 py-4 align-top font-semibold" style="border-color: var(--lumina-border); color: var(--lumina-text-primary);">
                                    {{ \Carbon\Carbon::parse($slot['start_time'])->format('H:i') }} - {{ \Carbon\Carbon::parse($slot['end_time'])->format('H:i') }}
                                </td>
                                @foreach($visibleDays as $day)
                                    @php
                                        $items = $scheduleGrid[$day][$slot['key']] ?? collect();
                                    @endphp
                                    <td class="border-b px-3 py-3 align-top" style="border-color: var(--lumina-border);">
                                        @if($items->isEmpty())
                                            <span class="text-xs" style="color: var(--lumina-text-muted);">-</span>
                                        @else
                                            <div class="space-y-2">
                                                @foreach($items as $schedule)
                                                    <div class="rounded-xl border px-3 py-2" style="border-color: var(--lumina-border-light); background: #F8FAFC;">
                                                        <p class="text-sm font-semibold" style="color: var(--lumina-text-primary);">
                                                            {{ $schedule->class?->course?->name ?? 'Course' }}
                                                        </p>
                                                        <p class="mt-1 text-xs" style="color: var(--lumina-text-secondary);">
                                                            Group #{{ $schedule->class_id }}
                                                            @if($schedule->class?->teacher?->name)
                                                                - {{ $schedule->class->teacher->name }}
                                                            @endif
                                                        </p>
                                                        <p class="mt-1 text-xs" style="color: var(--lumina-text-muted);">
                                                            Room: {{ $schedule->room?->name ?? 'TBA' }}
                                                        </p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layouts.secretary>
