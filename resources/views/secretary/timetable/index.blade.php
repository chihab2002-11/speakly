<x-layouts::app :title="__('Secretary Timetable Explorer')">
    <div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6">
        <!-- Header -->
        <div>
            <flux:heading size="xl">{{ __('Secretary Timetable Explorer') }}</flux:heading>
            <flux:text class="mt-1">{{ __('View and explore all schedules in the system.') }}</flux:text>
        </div>

        <!-- Filter Form -->
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-4 text-lg font-semibold text-zinc-900 dark:text-white">{{ __('Filter Options') }}</h2>

            <form method="GET" action="{{ route('secretary.timetable.index') }}" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Teacher Filter -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('Teacher') }}
                        </label>
                        <select name="teacher_id" class="w-full rounded border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
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
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('Student') }}
                        </label>
                        <select name="student_id" class="w-full rounded border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
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
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('Course') }}
                        </label>
                        <select name="course_id" class="w-full rounded border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
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
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('Class') }}
                        </label>
                        <select name="class_id" class="w-full rounded border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
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
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('Room') }}
                        </label>
                        <select name="room_id" class="w-full rounded border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
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
                        <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('Day of Week') }}
                        </label>
                        <select name="day_of_week" class="w-full rounded border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
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
                    <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800">
                        {{ __('Apply Filters') }}
                    </button>
                    <a href="{{ route('secretary.timetable.index') }}" class="rounded border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800">
                        {{ __('Clear Filters') }}
                    </a>
                </div>
            </form>
        </div>

        <!-- Results Summary -->
        @if (request()->anyFilled(['teacher_id', 'student_id', 'class_id', 'course_id', 'room_id', 'day_of_week']))
            <flux:callout variant="info" icon="information-circle">
                {{ __('Showing filtered results') }}
            </flux:callout>
        @endif

        <!-- Schedules Table -->
        @if ($groupedSchedules->isEmpty())
            <flux:callout variant="warning" icon="exclamation-circle" :heading="__('No schedules found')" />
        @else
            @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                @if ($groupedSchedules->has($day) && $groupedSchedules[$day]->isNotEmpty())
                    <div class="space-y-3">
                        <!-- Day Header -->
                        <h3 class="text-lg font-semibold capitalize text-zinc-900 dark:text-white">
                            {{ __(ucfirst($day)) }}
                        </h3>

                        <!-- Day Schedule Table -->
                        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <table class="w-full text-sm">
                                <thead class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-zinc-900 dark:text-white">
                                            {{ __('Time') }}
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-zinc-900 dark:text-white">
                                            {{ __('Course') }}
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-zinc-900 dark:text-white">
                                            {{ __('Class') }}
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-zinc-900 dark:text-white">
                                            {{ __('Teacher') }}
                                        </th>
                                        <th class="px-4 py-3 text-left font-semibold text-zinc-900 dark:text-white">
                                            {{ __('Room') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                    @foreach ($groupedSchedules[$day] as $schedule)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                                            <!-- Time -->
                                            <td class="px-4 py-3 font-medium text-zinc-900 dark:text-white">
                                                {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} -
                                                {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                            </td>

                                            <!-- Course -->
                                            <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">
                                                <div>
                                                    <p class="font-medium">{{ $schedule->class->course->name }}</p>
                                                    <p class="text-xs text-zinc-500">{{ $schedule->class->course->code }}</p>
                                                </div>
                                            </td>

                                            <!-- Class ID -->
                                            <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">
                                                Class #{{ $schedule->class->id }}
                                            </td>

                                            <!-- Teacher -->
                                            <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">
                                                {{ $schedule->class->teacher?->name ?? 'No Teacher' }}
                                            </td>

                                            <!-- Room -->
                                            <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">
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
</x-layouts::app>
