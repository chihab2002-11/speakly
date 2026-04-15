<x-layouts.admin :title="__('Manage Schedule')" :user="auth()->user()" :current-route="'admin.schedule.index'">
    <div class="mx-auto w-full max-w-7xl space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight md:text-5xl" style="color: #181D19; letter-spacing: -0.9px;">Manage Schedule</h1>
                <p class="mt-3 text-lg leading-8" style="color: #3F4941;">
                    Configure timetable slots by group and prevent teacher, group, and room conflicts.
                </p>
            </div>

            <a
                href="{{ route('admin.schedule.timetable-hub') }}"
                class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition hover:bg-gray-50"
                style="border-color: #D1D5DB; color: #1A1B22;"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Timetable Hub
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-xl border px-4 py-3 text-sm font-semibold" style="background-color: #D1FAE5; border-color: #A7F3D0; color: #065F46;">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border px-4 py-3" style="background-color: #FEF2F2; border-color: #FECACA; color: #991B1B;">
                <ul class="list-disc space-y-1 pl-5 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="rounded-2xl border p-5" style="background-color: #FFFFFF; border-color: #F1F5F9; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);">
            <h2 class="mb-4 text-xl font-bold" style="color: #181D19;">Add Schedule Slot</h2>

            <form method="POST" action="{{ route('admin.schedule.store') }}" class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
                @csrf

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Group</label>
                    <select name="class_id" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;">
                        <option value="">Select group</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" @selected((int) old('class_id') === (int) $group->id)>
                                #{{ $group->id }} - {{ $group->course?->name ?? 'Course' }}
                                @if($group->teacher)
                                    ({{ $group->teacher->name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Room</label>
                    <select name="room_id" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;">
                        <option value="">No room</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" @selected((int) old('room_id') === (int) $room->id)>{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Day</label>
                    <select name="day_of_week" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;">
                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                            <option value="{{ $day }}" @selected(old('day_of_week') === $day)>{{ ucfirst($day) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Start</label>
                    <input name="start_time" type="time" value="{{ old('start_time') }}" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">End</label>
                    <input name="end_time" type="time" value="{{ old('end_time') }}" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full rounded-xl px-4 py-2.5 text-sm font-semibold text-white" style="background-color: #2D8C5E;">
                        Add Slot
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-2xl border p-5" style="background-color: #FFFFFF; border-color: #F1F5F9; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);">
            <h2 class="mb-4 text-xl font-bold" style="color: #181D19;">Filter Schedule</h2>

            <form method="GET" action="{{ route('admin.schedule.index') }}" class="grid gap-4 md:grid-cols-3 xl:grid-cols-6">
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Teacher</label>
                    <select name="teacher_id" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;">
                        <option value="">All</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected($selectedTeacherId === $teacher->id)>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Group</label>
                    <select name="class_id" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;">
                        <option value="">All</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" @selected($selectedClassId === $group->id)>
                                #{{ $group->id }} - {{ $group->course?->name ?? 'Course' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Room</label>
                    <select name="room_id" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;">
                        <option value="">All</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" @selected($selectedRoomId === $room->id)>{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Day</label>
                    <select name="day" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;">
                        <option value="">All</option>
                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                            <option value="{{ $day }}" @selected($selectedDay === $day)>{{ ucfirst($day) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white" style="background-color: #2D8C5E;">Apply</button>
                    <a href="{{ route('admin.schedule.index') }}" class="rounded-xl border px-4 py-2.5 text-sm font-semibold" style="border-color: #E2E8F0; color: #3F4941;">Reset</a>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border" style="background-color: #FFFFFF; border-color: #F1F5F9; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);">
            <div class="flex items-center justify-between border-b px-6 py-5" style="border-color: #F1F5F9;">
                <h2 class="text-[20px] font-bold" style="color: #181D19;">Current Schedule</h2>
                <span class="text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">{{ $schedules->total() }} Slot(s)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr style="background-color: #F0F5EE;">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">Day</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">Time</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">Group</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">Teacher</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">Room</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                            <tr class="border-t" style="border-color: #F1F5F9;">
                                <td class="px-6 py-5 text-sm font-medium" style="color: #3F4941;">{{ ucfirst((string) $schedule->day_of_week) }}</td>
                                <td class="px-6 py-5 text-sm" style="color: #3F4941;">
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                </td>
                                <td class="px-6 py-5 text-sm" style="color: #3F4941;">#{{ $schedule->class_id }} - {{ $schedule->class?->course?->name ?? 'Course' }} ({{ $schedule->class?->course?->code ?? 'N/A' }})</td>
                                <td class="px-6 py-5 text-sm" style="color: #3F4941;">{{ $schedule->class?->teacher?->name ?? 'Unassigned' }}</td>
                                <td class="px-6 py-5 text-sm" style="color: #3F4941;">{{ $schedule->room?->name ?? 'No room' }}</td>
                                <td class="px-6 py-5">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            onclick="openEditScheduleModal(this)"
                                            data-id="{{ $schedule->id }}"
                                            data-class-id="{{ $schedule->class_id }}"
                                            data-room-id="{{ $schedule->room_id }}"
                                            data-day="{{ $schedule->day_of_week }}"
                                            data-start="{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}"
                                            data-end="{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-gray-100"
                                            title="Edit"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #94A3B8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <form method="POST" action="{{ route('admin.schedule.destroy', $schedule) }}" onsubmit="return confirm('Delete this schedule slot?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-red-50" title="Delete">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #94A3B8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V4h6v3m-7 4v6m4-6v6m5 4H7a2 2 0 01-2-2V7h14v12a2 2 0 01-2 2z"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-sm" style="color: #64748B;">No schedule slots found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t px-6 py-4" style="border-color: #F1F5F9;">
                {{ $schedules->links() }}
            </div>
        </section>
    </div>

    <div id="editScheduleModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" onclick="if(event.target===this){closeEditScheduleModal()}">
        <div class="w-full max-w-2xl rounded-2xl p-6" style="background-color: #FFFFFF;" onclick="event.stopPropagation()">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-xl font-bold" style="color: #181D19;">Edit Schedule Slot</h3>
                <button type="button" onclick="closeEditScheduleModal()" class="rounded-lg p-2 hover:bg-gray-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748B;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="editScheduleForm" method="POST" action="" class="grid gap-4 md:grid-cols-2">
                @csrf
                @method('PATCH')

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Group</label>
                    <select id="edit_class_id" name="class_id" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;">
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}">
                                #{{ $group->id }} - {{ $group->course?->name ?? 'Course' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Teacher</label>
                    <div class="rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0; background-color: #F8FAFC; color: #3F4941;">
                        Teacher is managed from the selected group.
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Room</label>
                    <select id="edit_room_id" name="room_id" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;">
                        <option value="">No room</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Day</label>
                    <select id="edit_day_of_week" name="day_of_week" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;">
                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                            <option value="{{ $day }}">{{ ucfirst($day) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Start</label>
                    <input id="edit_start_time" name="start_time" type="time" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">End</label>
                    <input id="edit_end_time" name="end_time" type="time" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;">
                </div>

                <div class="md:col-span-2 mt-2 flex justify-end gap-3">
                    <button type="button" onclick="closeEditScheduleModal()" class="rounded-xl border px-4 py-2.5 text-sm font-semibold" style="border-color: #E2E8F0; color: #3F4941;">Cancel</button>
                    <button type="submit" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white" style="background-color: #2D8C5E;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditScheduleModal(button) {
            const modal = document.getElementById('editScheduleModal');
            const form = document.getElementById('editScheduleForm');

            if (!modal || !form || !button) {
                return;
            }

            const scheduleId = button.getAttribute('data-id');
            form.action = `{{ url('/admin/schedule') }}/${scheduleId}`;

            const classId = button.getAttribute('data-class-id') || '';
            const roomId = button.getAttribute('data-room-id') || '';
            const day = button.getAttribute('data-day') || '';
            const start = button.getAttribute('data-start') || '';
            const end = button.getAttribute('data-end') || '';

            document.getElementById('edit_class_id').value = classId;
            document.getElementById('edit_room_id').value = roomId;
            document.getElementById('edit_day_of_week').value = day;
            document.getElementById('edit_start_time').value = start;
            document.getElementById('edit_end_time').value = end;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEditScheduleModal() {
            const modal = document.getElementById('editScheduleModal');
            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
</x-layouts.admin>
