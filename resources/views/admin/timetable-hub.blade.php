<x-layouts.admin :title="__('Admin Timetable Hub')" :user="auth()->user()" :current-route="'admin.schedule.index'">
    <div class="mx-auto w-full max-w-7xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl" style="color: #181D19; letter-spacing: -0.9px;">
                    Timetable Hub
                </h1>
                <p class="mt-2 text-base" style="color: #3F4941;">
                    Switch between teacher, student, classes, and room timetables from one workspace.
                </p>
            </div>

            <a
                href="{{ route('admin.schedule.index') }}"
                class="inline-flex items-center rounded-xl border px-4 py-2 text-sm font-semibold transition hover:bg-gray-50"
                style="border-color: #D1D5DB; color: #1A1B22;"
            >
                Back to Manage Schedule
            </a>
        </div>

        <section class="rounded-3xl border p-5" style="background: #FFFFFF; border-color: #F1F5F9;">
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
                            href="{{ route('admin.schedule.timetable-hub', ['mode' => $modeKey]) }}"
                            class="rounded-xl border px-3.5 py-2 text-sm font-semibold transition"
                            style="background: #2D8C5E; color: white; border-color: #2D8C5E;"
                        >
                            {{ $modeLabel }}
                        </a>
                    @else
                        <a
                            href="{{ route('admin.schedule.timetable-hub', ['mode' => $modeKey]) }}"
                            class="rounded-xl border px-3.5 py-2 text-sm font-semibold transition"
                            style="background: white; color: #3F4941; border-color: #D1D5DB;"
                        >
                            {{ $modeLabel }}
                        </a>
                    @endif
                @endforeach
            </div>

            <form method="GET" action="{{ route('admin.schedule.timetable-hub') }}" class="space-y-5">
                <input type="hidden" name="mode" value="{{ $mode }}">

                <div class="grid gap-4 md:grid-cols-4">
                    @if($mode === 'teacher')
                        <div>
                            <label for="teacher_id" class="mb-1 block text-xs font-semibold" style="color: #3F4941;">Teacher</label>
                            <select id="teacher_id" name="teacher_id" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color: #D1D5DB; color: #1A1B22; background: #fff;">
                                @foreach($teachers as $teacher)
                                    <option value="{{ $teacher->id }}" @selected((int) $selectedTeacherId === (int) $teacher->id)>{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif($mode === 'student')
                        <div>
                            <label for="student_id" class="mb-1 block text-xs font-semibold" style="color: #3F4941;">Student</label>
                            <select id="student_id" name="student_id" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color: #D1D5DB; color: #1A1B22; background: #fff;">
                                <option value="">Select Student</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" @selected((int) $selectedStudentId === (int) $student->id)>{{ $student->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif($mode === 'classes')
                        <div>
                            <label for="class_id" class="mb-1 block text-xs font-semibold" style="color: #3F4941;">Class / Course Group</label>
                            <select id="class_id" name="class_id" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color: #D1D5DB; color: #1A1B22; background: #fff;">
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
                            <label for="room_id" class="mb-1 block text-xs font-semibold" style="color: #3F4941;">Room</label>
                            <select id="room_id" name="room_id" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color: #D1D5DB; color: #1A1B22; background: #fff;">
                                <option value="">All Rooms</option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}" @selected((int) $selectedRoomId === (int) $room->id)>{{ $room->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="flex items-end gap-2">
                        <button type="submit" class="rounded-xl px-4 py-2 text-sm font-semibold text-white" style="background: #2D8C5E;">
                            Apply
                        </button>
                        <a href="{{ route('admin.schedule.timetable-hub', ['mode' => $mode]) }}" class="rounded-xl border px-4 py-2 text-sm font-semibold" style="border-color: #D1D5DB; color: #3F4941;">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </section>

        <section class="rounded-3xl border p-5" style="background: #FFFFFF; border-color: #F1F5F9;">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-xl font-bold" style="color: #181D19;">Timetable</h2>
                <p class="text-sm font-semibold" style="color: #64748B;">{{ $totalSchedules }} schedule item(s)</p>
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
                <div class="overflow-x-auto rounded-2xl border" style="border-color: #E2E8F0;">
                    <table class="w-full min-w-[940px] text-sm">
                        <thead style="background-color: #F8FAFC;">
                            <tr>
                                <th class="border-b px-3 py-3 text-left font-semibold" style="border-color: #E2E8F0; color: #64748B; width: 140px;">Time</th>
                                @foreach($visibleDays as $day)
                                    <th class="border-b px-3 py-3 text-left font-semibold" style="border-color: #E2E8F0; color: #64748B; min-width: 220px;">
                                        {{ ucfirst($day) }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($timeSlots as $slot)
                                <tr>
                                    <td class="border-b px-3 py-4 align-top font-semibold" style="border-color: #E2E8F0; color: #181D19;">
                                        {{ \Carbon\Carbon::parse($slot['start_time'])->format('H:i') }} - {{ \Carbon\Carbon::parse($slot['end_time'])->format('H:i') }}
                                    </td>
                                    @foreach($visibleDays as $day)
                                        @php
                                            $items = $scheduleGrid[$day][$slot['key']] ?? collect();
                                        @endphp
                                        <td class="border-b px-3 py-3 align-top" style="border-color: #E2E8F0;">
                                            @if($items->isEmpty())
                                                <span class="text-xs" style="color: #94A3B8;">-</span>
                                            @else
                                                <div class="space-y-2">
                                                    @foreach($items as $schedule)
                                                        <div class="rounded-xl border px-3 py-2" style="border-color: #E2E8F0; background: #F8FAFC;">
                                                            <p class="text-sm font-semibold" style="color: #181D19;">
                                                                {{ $schedule->class?->course?->name ?? 'Course' }}
                                                            </p>
                                                            <p class="mt-1 text-xs" style="color: #3F4941;">
                                                                Group #{{ $schedule->class_id }}
                                                                @if($schedule->class?->teacher?->name)
                                                                    - {{ $schedule->class->teacher->name }}
                                                                @endif
                                                            </p>
                                                            <p class="mt-1 text-xs" style="color: #64748B;">
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
    </div>
</x-layouts.admin>
