<x-layouts::app :title="__('Timetable')">
    <div class="mx-auto max-w-6xl space-y-6 p-4 sm:p-6">
        <div>
            <flux:heading size="xl">{{ __('My Timetable') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Your enrolled classes and schedule.') }}</flux:text>
        </div>

        @if ($enrolledClasses->isEmpty())
            <flux:callout variant="warning" icon="exclamation-circle" :heading="__('No classes enrolled')" />
        @else
            <div class="grid gap-6">
                @foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                    @if (!empty($timetable[$day]))
                        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                            <h3 class="mb-4 text-lg font-semibold capitalize text-zinc-900 dark:text-white">
                                {{ __($day) }}
                            </h3>
                            <div class="space-y-3">
                                @foreach ($timetable[$day] as $schedule)
                                    <div class="flex items-start justify-between gap-4 border-l-4 border-blue-500 bg-blue-50 p-3 dark:bg-blue-900/20">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <p class="font-semibold text-zinc-900 dark:text-white">
                                                    {{ $schedule['course_name'] }}
                                                </p>
                                                <span class="inline-block rounded bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900 dark:text-blue-300">
                                                    {{ $schedule['course_code'] }}
                                                </span>
                                            </div>
                                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                                                📍 {{ $schedule['room_name'] }}
                                            </p>
                                            @if ($schedule['teacher_name'])
                                                <p class="mt-0.5 text-sm text-zinc-600 dark:text-zinc-400">
                                                    👨‍🏫 {{ $schedule['teacher_name'] }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <p class="font-semibold text-zinc-900 dark:text-white">
                                                {{ \Carbon\Carbon::parse($schedule['start_time'])->format('H:i') }} -
                                                {{ \Carbon\Carbon::parse($schedule['end_time'])->format('H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-900/50">
                <flux:heading size="md" class="mb-3">{{ __('Enrolled Courses') }}</flux:heading>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($enrolledClasses as $class)
                        <div class="rounded border border-zinc-200 p-3 dark:border-zinc-700">
                            <p class="font-medium text-zinc-900 dark:text-white">
                                {{ $class->course->name }}
                            </p>
                            <p class="text-xs text-zinc-600 dark:text-zinc-400">
                                {{ $class->course->code }} • Room {{ $class->room }}
                            </p>
                            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                👨‍🏫 {{ $class->teacher?->name ?? 'No teacher assigned' }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
