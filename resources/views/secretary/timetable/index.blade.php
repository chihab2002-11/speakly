<x-layouts.secretary :title="__('Secretary Timetable Explorer')" :current-route="'secretary.timetable.index'">
    <div class="mx-auto max-w-7xl space-y-6">
        <div>
            <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
                Secretary Timetable Explorer
            </h1>
            <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
                View and explore all schedules with administrative filters.
            </p>
        </div>

        <!-- Filter Form -->
        <div class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light);">
            <h2 class="mb-4 text-lg font-bold" style="color: var(--lumina-text-primary);">{{ __('Filter Options') }}</h2>

            <form method="GET" action="{{ route('secretary.timetable.index') }}" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Teacher Filter -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold" style="color: var(--lumina-text-secondary);">
                            {{ __('Teacher') }}
                        </label>
                        <select name="teacher_id" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background-color: white; color: var(--lumina-text-primary);">
                            <option value="">{{ __('-- Select Teacher --') }}</option>
                            @foreach ($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected(request('teacher_id') == $teacher->id)>
                                    {{ $teacher->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Student Filter -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold" style="color: var(--lumina-text-secondary);">
                            {{ __('Student') }}
                        </label>
                        <select name="student_id" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background-color: white; color: var(--lumina-text-primary);">
                            <option value="">{{ __('-- Select Student --') }}</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}" @selected(request('student_id') == $student->id)>
                                    {{ $student->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Course Filter -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold" style="color: var(--lumina-text-secondary);">
                            {{ __('Course') }}
                        </label>
                        <select name="course_id" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background-color: white; color: var(--lumina-text-primary);">
                            <option value="">{{ __('-- Select Course --') }}</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" @selected(request('course_id') == $course->id)>
                                    {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Class Filter -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold" style="color: var(--lumina-text-secondary);">
                            {{ __('Class') }}
                        </label>
                        <select name="class_id" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background-color: white; color: var(--lumina-text-primary);">
                            <option value="">{{ __('-- Select Class --') }}</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected(request('class_id') == $class->id)>
                                    {{ $class->course->name }} - {{ $class->teacher?->name ?? 'No Teacher' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Room Filter -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold" style="color: var(--lumina-text-secondary);">
                            {{ __('Room') }}
                        </label>
                        <select name="room_id" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background-color: white; color: var(--lumina-text-primary);">
                            <option value="">{{ __('-- Select Room --') }}</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" @selected(request('room_id') == $room->id)>
                                    {{ $room->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Day of Week Filter -->
                    <div>
                        <label class="mb-2 block text-sm font-semibold" style="color: var(--lumina-text-secondary);">
                            {{ __('Day of Week') }}
                        </label>
                        <select name="day_of_week" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background-color: white; color: var(--lumina-text-primary);">
                            <option value="">{{ __('-- Select Day --') }}</option>
                            @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                <option value="{{ $day }}" @selected(request('day_of_week') == $day)>
                                    {{ __(ucfirst($day)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Filter Buttons -->
                <div class="flex gap-2 pt-4">
                    <button type="submit" class="rounded-xl px-4 py-2 text-sm font-semibold text-white" style="background-color: var(--lumina-primary);">
                        {{ __('Apply Filters') }}
                    </button>
                    <a href="{{ route('secretary.timetable.index') }}" class="rounded-xl border px-4 py-2 text-sm font-semibold" style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);">
                        {{ __('Clear Filters') }}
                    </a>
                </div>
            </form>
        </div>

        <!-- Results Summary -->
        @if (request()->anyFilled(['teacher_id', 'student_id', 'class_id', 'course_id', 'room_id', 'day_of_week']))
            <div class="rounded-xl border px-4 py-3 text-sm font-semibold" style="background-color: #ECFDF5; border-color: #A7F3D0; color: #065F46;">
                {{ __('Showing filtered results') }}
            </div>
        @endif

        <!-- Schedules Table -->
        @if ($groupedSchedules->isEmpty())
            <div class="rounded-xl border px-4 py-3 text-sm font-semibold" style="background-color: #FEFCE8; border-color: #FDE68A; color: #92400E;">
                {{ __('No schedules found') }}
            </div>
        @else
            @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                @if ($groupedSchedules->has($day) && $groupedSchedules[$day]->isNotEmpty())
                    <div class="space-y-3">
                        <!-- Day Header -->
                        <h3 class="text-lg font-bold capitalize" style="color: var(--lumina-text-primary);">
                            {{ __(ucfirst($day)) }}
                        </h3>

                        <!-- Day Schedule Table -->
                        <div class="overflow-x-auto rounded-2xl border" style="background: white; border-color: var(--lumina-border-light);">
                            <table class="w-full text-sm">
                                <thead class="border-b" style="border-color: var(--lumina-border); background-color: #F8FAFC;">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-primary);">
                                            {{ __('Time') }}
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-primary);">
                                            {{ __('Course') }}
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-primary);">
                                            {{ __('Class') }}
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-primary);">
                                            {{ __('Teacher') }}
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-primary);">
                                            {{ __('Room') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y" style="border-color: var(--lumina-border);">
                                    @foreach ($groupedSchedules[$day] as $schedule)
                                        <tr class="hover:bg-gray-50">
                                            <!-- Time -->
                                            <td class="px-4 py-3 font-medium" style="color: var(--lumina-text-primary);">
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} -
                                                {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                            </td>

                                            <!-- Course -->
                                            <td class="px-4 py-3" style="color: var(--lumina-text-secondary);">
                                                <div>
                                                    <p class="font-medium">{{ $schedule->class->course->name }}</p>
                                                    <p class="text-xs" style="color: var(--lumina-text-muted);">{{ $schedule->class->course->code }}</p>
                                                </div>
                                            </td>

                                            <!-- Class ID -->
                                            <td class="px-4 py-3" style="color: var(--lumina-text-secondary);">
                                                Class #{{ $schedule->class->id }}
                                            </td>

                                            <!-- Teacher -->
                                            <td class="px-4 py-3" style="color: var(--lumina-text-secondary);">
                                                {{ $schedule->class->teacher?->name ?? 'No Teacher' }}
                                            </td>

                                            <!-- Room -->
                                            <td class="px-4 py-3" style="color: var(--lumina-text-secondary);">
                                                {{ $schedule->room?->name ?? 'TBA' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Day Spacing -->
                    <div class="mt-8"></div>
                @endif
            @endforeach
        @endif
    </div>
</x-layouts.secretary>
