<x-layouts.secretary :title="__('Manage Groups')" :current-route="'secretary.groups'">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
            Manage Groups
        </h1>
        <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
            Create groups from courses, enroll students, and monitor assignment and schedule load.
        </p>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm font-semibold" style="background-color: #D1FAE5; border-color: #A7F3D0; color: #065F46;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-xl border px-4 py-3" style="background-color: #FEF2F2; border-color: #FECACA; color: #991B1B;">
            <ul class="list-disc space-y-1 pl-5 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $oldEnrollGroupId = old('class_id');
        $oldEnrollProgramId = (string) old('enroll_program_id', '');

        if ($oldEnrollProgramId === '' && $oldEnrollGroupId) {
            $oldEnrollGroup = ($enrollGroups ?? collect())->firstWhere('id', (int) $oldEnrollGroupId);
            $oldEnrollProgramId = (string) ($oldEnrollGroup?->course?->program_id ?? '');
        }

        $secretaryGroupCoursesData = ($courses ?? collect())->map(fn ($course): array => [
            'id' => $course->id,
            'name' => $course->name,
            'code' => $course->code,
            'program_id' => $course->program_id,
        ])->values()->all();

        $secretaryGroupClassesData = ($enrollGroups ?? collect())->map(fn ($group): array => [
            'id' => $group->id,
            'course_name' => $group->course?->name ?? 'Course',
            'course_code' => $group->course?->code,
            'program_id' => $group->course?->program_id,
            'teacher_name' => $group->teacher?->name,
            'students_count' => $group->students_count,
            'capacity' => $group->capacity,
        ])->values()->all();
    @endphp

    <div class="mb-6 grid gap-4 lg:grid-cols-2">
        <section class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light);">
            <h2 class="text-lg font-bold" style="color: var(--lumina-text-primary);">Create Group</h2>
            <p class="mt-1 text-sm" style="color: var(--lumina-text-muted);">Choose a program, course, optional teacher, and capacity.</p>

            <form method="POST" action="{{ route('secretary.groups.store') }}" class="mt-4 grid gap-3 md:grid-cols-2">
                @csrf

                <div>
                    <label for="create_group_program_id" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Program</label>
                    <select id="create_group_program_id" name="program_id" required class="w-full rounded-lg border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background: #F8FAFC;">
                        <option value="">Select program</option>
                        @foreach($availablePrograms as $program)
                            <option value="{{ $program->id }}" @selected((string) old('program_id') === (string) $program->id)>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="create_group_course_id" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Course</label>
                    <select id="create_group_course_id" name="course_id" data-selected-course="{{ old('course_id') }}" required class="w-full rounded-lg border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background: #F8FAFC;">
                        <option value="">Select a program first</option>
                    </select>
                </div>

                <div class="relative">
                    <label for="create_group_teacher_search" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Teacher</label>
                    <input
                        id="create_group_teacher_search"
                        type="text"
                        placeholder="Search teacher by name or email"
                        class="w-full rounded-lg border px-3 py-2 text-sm"
                        style="border-color: var(--lumina-border); background: #F8FAFC;"
                        autocomplete="off"
                    >
                    <div id="create_group_teacher_results" class="absolute left-0 right-0 top-full mt-1 hidden max-h-48 overflow-y-auto rounded-lg border bg-white shadow-lg" style="border-color: var(--lumina-border);"></div>
                </div>

                <input type="hidden" id="create_group_teacher_id" name="teacher_id" value="">

                <div>
                    <label for="create_group_capacity" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Capacity</label>
                    <input
                        id="create_group_capacity"
                        type="number"
                        name="capacity"
                        min="1"
                        max="1000"
                        value="{{ old('capacity', 30) }}"
                        required
                        class="w-full rounded-lg border px-3 py-2 text-sm"
                        style="border-color: var(--lumina-border); background: #F8FAFC;"
                    >
                </div>

                <div class="flex items-end">
                    <button type="submit" class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background-color: var(--lumina-primary);">
                        Create Group
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light);">
            <h2 class="text-lg font-bold" style="color: var(--lumina-text-primary);">Enroll Student</h2>
            <p class="mt-1 text-sm" style="color: var(--lumina-text-muted);">Select program → course → group, then search and select a student.</p>

            <form method="POST" action="{{ route('secretary.groups.enroll') }}" class="mt-4 grid gap-3 md:grid-cols-2">
                @csrf

                <div>
                    <label for="enroll_program_id" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Program</label>
                    <select id="enroll_program_id" name="enroll_program_id" required class="w-full rounded-lg border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background: #F8FAFC;">
                        <option value="">Select program</option>
                        @foreach($availablePrograms as $program)
                            <option value="{{ $program->id }}" @selected($oldEnrollProgramId !== '' && (string) $program->id === $oldEnrollProgramId)>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="enroll_course_id" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Course</label>
                    <select id="enroll_course_id" name="enroll_course_id" data-selected-course="{{ old('enroll_course_id') }}" required class="w-full rounded-lg border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background: #F8FAFC;">
                        <option value="">Select course</option>
                    </select>
                </div>

                <div>
                    <label for="enroll_class_id" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Group</label>
                    <select id="enroll_class_id" name="class_id" data-selected-class="{{ old('class_id') }}" required class="w-full rounded-lg border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background: #F8FAFC;">
                        <option value="">Select group</option>
                    </select>
                </div>

                <div class="relative">
                    <label for="enroll_student_search" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Student</label>
                    <input
                        id="enroll_student_search"
                        type="text"
                        placeholder="Search student by name or email"
                        class="w-full rounded-lg border px-3 py-2 text-sm"
                        style="border-color: var(--lumina-border); background: #F8FAFC;"
                        autocomplete="off"
                    >
                    <div id="enroll_student_results" class="absolute left-0 right-0 top-full mt-1 hidden max-h-48 overflow-y-auto rounded-lg border bg-white shadow-lg" style="border-color: var(--lumina-border);">
                    </div>
                </div>

                <input type="hidden" id="enroll_student_id" name="student_id" value="">

                <div id="enroll_student_selected" class="hidden rounded-lg border p-3" style="border-color: var(--lumina-border); background: #F8FAFC;">
                    <p class="text-xs font-semibold" style="color: var(--lumina-text-muted);">Selected Student</p>
                    <p id="enroll_student_display" class="mt-1 text-sm font-semibold" style="color: var(--lumina-text-primary);"></p>
                </div>

                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background-color: var(--lumina-primary);">
                        Enroll Student
                    </button>
                    <button type="reset" class="rounded-lg border px-4 py-2 text-sm font-semibold" style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);">
                        Clear
                    </button>
                </div>
            </form>
        </section>
    </div>

    <section class="mb-6 grid gap-4 lg:grid-cols-4">
        <article class="relative overflow-hidden rounded-2xl p-5 text-white" style="background: #2D8C5E; box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 150px;">
            <p class="text-xs font-bold uppercase tracking-[1.2px]">Total Groups</p>
            <p class="mt-3 text-4xl font-black leading-none">{{ $statsTotalGroups }}</p>
            <p class="mt-4 text-sm font-semibold">Active class structures</p>
            <div class="pointer-events-none absolute -bottom-5 right-3 h-14 w-14 rounded-full border border-white/30"></div>
        </article>

        <article class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 150px;">
            <p class="text-xs font-bold uppercase tracking-[1.2px]" style="color: #525C87;">Assigned Students</p>
            <p class="mt-3 text-4xl font-extrabold" style="color: var(--lumina-text-primary);">{{ $statsAssignedStudents }}</p>
            <p class="mt-2 text-sm" style="color: var(--lumina-text-muted);">Across all groups</p>
        </article>

        <article class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 150px;">
            <p class="text-xs font-bold uppercase tracking-[1.2px]" style="color: #611E00;">Groups Active Today</p>
            <p class="mt-3 text-4xl font-extrabold" style="color: var(--lumina-text-primary);">{{ $statsActiveToday }}</p>
            <p class="mt-2 text-sm" style="color: var(--lumina-text-muted);">Have schedule slot today</p>
        </article>

        <article class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 150px;">
            <p class="text-xs font-bold uppercase tracking-[1.2px]" style="color: #1E3A8A;">Open Slots</p>
            <p class="mt-3 text-4xl font-extrabold" style="color: var(--lumina-text-primary);">{{ $statsOpenSlots }}</p>
            <p class="mt-2 text-sm" style="color: var(--lumina-text-muted);">Remaining seats overall</p>
        </article>
    </section>

    <div class="mb-4 rounded-2xl border p-4" style="background: white; border-color: var(--lumina-border-light);">
        <form method="GET" action="{{ route('secretary.groups') }}" class="grid gap-3 md:grid-cols-5">
            <div>
                <label for="search" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Search</label>
                <input
                    id="search"
                    name="search"
                    value="{{ $search }}"
                    type="text"
                    placeholder="Course, code, teacher, ID"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none"
                    style="border-color: var(--lumina-border); background: #F8FAFC;"
                >
            </div>

            <div>
                <label for="teacher_id" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Teacher</label>
                <select
                    id="teacher_id"
                    name="teacher_id"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none"
                    style="border-color: var(--lumina-border); background: #F8FAFC;"
                >
                    <option value="">All</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected($teacherId !== '' && (int) $teacherId === $teacher->id)>{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="course_id" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Course</label>
                <select
                    id="course_id"
                    name="course_id"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none"
                    style="border-color: var(--lumina-border); background: #F8FAFC;"
                >
                    <option value="">All</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected($courseId !== '' && (int) $courseId === $course->id)>
                            {{ $course->name }} ({{ $course->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="day" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Day</label>
                <select
                    id="day"
                    name="day"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none"
                    style="border-color: var(--lumina-border); background: #F8FAFC;"
                >
                    <option value="">All</option>
                    @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $weekday)
                        <option value="{{ $weekday }}" @selected($day === $weekday)>{{ ucfirst($weekday) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button
                    type="submit"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-white"
                    style="background-color: var(--lumina-primary);"
                >
                    Apply
                </button>
                <a
                    href="{{ route('secretary.groups') }}"
                    class="rounded-lg border px-4 py-2 text-sm font-semibold"
                    style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);"
                >
                    Reset
                </a>
            </div>
        </form>
    </div>

    @if($groups->isEmpty())
        <section class="rounded-3xl border p-12 text-center" style="background: white; border-color: var(--lumina-border-light);">
            <p class="text-lg font-semibold" style="color: var(--lumina-text-primary);">No groups match this filter</p>
            <p class="mt-2 text-sm" style="color: var(--lumina-text-muted);">Try relaxing one or more filters.</p>
        </section>
    @else
        <div class="grid gap-4 xl:grid-cols-2">
            @foreach($groups as $group)
                <article class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light);">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                                {{ $group->course?->name ?? 'Course' }}
                            </h2>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">
                                {{ $group->course?->code ?? 'N/A' }} - Group #{{ $group->id }}
                            </p>
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold" style="background: #ECFDF5; color: #065F46;">
                            {{ $group->students_count }}/{{ $group->capacity }} students
                        </span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <button
                            type="button"
                            onclick="openEditGroupModal(this)"
                            data-id="{{ $group->id }}"
                            data-course-id="{{ $group->course_id }}"
                            data-teacher-id="{{ $group->teacher_id }}"
                            data-capacity="{{ $group->capacity }}"
                            data-students-count="{{ $group->students_count }}"
                            class="rounded-lg border px-3 py-1.5 text-xs font-semibold"
                            style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);"
                        >
                            Edit
                        </button>

                        <form method="POST" action="{{ route('secretary.groups.destroy', $group) }}" onsubmit="return confirm('Delete this group?');">
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="rounded-lg border px-3 py-1.5 text-xs font-semibold"
                                style="border-color: #FECACA; color: #991B1B;"
                            >
                                Delete
                            </button>
                        </form>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-xl border p-3" style="border-color: var(--lumina-border);">
                            <p class="text-xs font-semibold" style="color: var(--lumina-text-muted);">Teacher</p>
                            <p class="mt-1 text-sm font-semibold" style="color: var(--lumina-text-primary);">{{ $group->teacher?->name ?? 'Unassigned' }}</p>
                        </div>

                        <div class="rounded-xl border p-3" style="border-color: var(--lumina-border);">
                            <p class="text-xs font-semibold" style="color: var(--lumina-text-muted);">Schedule Slots</p>
                            <p class="mt-1 text-sm font-semibold" style="color: var(--lumina-text-primary);">{{ $group->schedules_count }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="mb-2 text-xs font-semibold" style="color: var(--lumina-text-muted);">Weekly Schedule</p>
                        @if($group->schedules->isEmpty())
                            <p class="text-sm" style="color: var(--lumina-text-muted);">No schedule assigned yet.</p>
                        @else
                            <div class="flex flex-wrap gap-2">
                                @foreach($group->schedules->sortBy(['day_of_week', 'start_time']) as $slot)
                                    <span class="rounded-lg px-2.5 py-1 text-xs font-semibold" style="background: #F1F5F9; color: #0F172A;">
                                        {{ ucfirst((string) $slot->day_of_week) }} {{ 
                                            \Carbon\Carbon::parse($slot->start_time)->format('H:i')
                                        }}-{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }} {{ $slot->room?->name ? '- '.$slot->room->name : '' }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $groups->links() }}
        </div>
    @endif

    <div id="editGroupModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" onclick="if(event.target===this){closeEditGroupModal()}">
        <div class="w-full max-w-xl rounded-2xl p-6" style="background-color: #FFFFFF;" onclick="event.stopPropagation()">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-xl font-bold" style="color: var(--lumina-text-primary);">Edit Group</h3>
                <button type="button" onclick="closeEditGroupModal()" class="rounded-lg p-2 hover:bg-gray-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--lumina-text-muted);"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="editGroupForm" method="POST" action="" class="grid gap-4 md:grid-cols-2">
                @csrf
                @method('PATCH')

                <div>
                    <label class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Course</label>
                    <select id="edit_group_course_id" name="course_id" required class="w-full rounded-lg border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background: #F8FAFC;">
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }} ({{ $course->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Teacher</label>
                    <select id="edit_group_teacher_id" name="teacher_id" class="w-full rounded-lg border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background: #F8FAFC;">
                        <option value="">Unassigned</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Capacity</label>
                    <input id="edit_group_capacity" type="number" name="capacity" min="1" max="1000" required class="w-full rounded-lg border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background: #F8FAFC;">
                    <p id="edit_group_capacity_hint" class="mt-1 text-xs" style="color: var(--lumina-text-muted);"></p>
                </div>

                <div class="md:col-span-2 mt-2 flex justify-end gap-3">
                    <button type="button" onclick="closeEditGroupModal()" class="rounded-lg border px-4 py-2 text-sm font-semibold" style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);">Cancel</button>
                    <button type="submit" class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background-color: var(--lumina-primary);">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script id="secretaryGroupCoursesData" type="application/json">
        @json($secretaryGroupCoursesData)
    </script>

    <script id="secretaryGroupClassesData" type="application/json">
        @json($secretaryGroupClassesData)
    </script>

    <script>
        function secretaryGroupJson(scriptId) {
            const element = document.getElementById(scriptId);

            if (!element) {
                return [];
            }

            try {
                return JSON.parse(element.textContent || '[]');
            } catch (error) {
                return [];
            }
        }

        function setSecretaryCreateCourseOptions(programId, selectedCourseId) {
            const select = document.getElementById('create_group_course_id');

            if (!select) {
                return;
            }

            const courses = secretaryGroupJson('secretaryGroupCoursesData');
            const filteredCourses = courses.filter((course) => String(course.program_id) === String(programId));
            const placeholder = !programId
                ? 'Select a program first'
                : filteredCourses.length === 0
                    ? 'No courses for this program'
                    : 'Select course';

            select.innerHTML = '';
            select.appendChild(new Option(placeholder, ''));

            filteredCourses.forEach((course) => {
                const label = course.code ? `${course.name} (${course.code})` : course.name;
                select.appendChild(new Option(label, String(course.id)));
            });

            const courseExists = filteredCourses.some((course) => String(course.id) === String(selectedCourseId));
            select.value = courseExists ? String(selectedCourseId) : '';
            select.disabled = !programId || filteredCourses.length === 0;
            select.dataset.selectedCourse = select.value;
        }

        function setEnrollCourseOptions(programId, selectedCourseId) {
            const select = document.getElementById('enroll_course_id');

            if (!select) {
                return;
            }

            const courses = secretaryGroupJson('secretaryGroupCoursesData');
            const filteredCourses = courses.filter((course) => String(course.program_id) === String(programId));
            const placeholder = !programId
                ? 'Select a program first'
                : filteredCourses.length === 0
                    ? 'No courses for this program'
                    : 'Select course';

            select.innerHTML = '';
            select.appendChild(new Option(placeholder, ''));

            filteredCourses.forEach((course) => {
                const label = course.code ? `${course.name} (${course.code})` : course.name;
                select.appendChild(new Option(label, String(course.id)));
            });

            const courseExists = filteredCourses.some((course) => String(course.id) === String(selectedCourseId));
            select.value = courseExists ? String(selectedCourseId) : '';
            select.disabled = !programId || filteredCourses.length === 0;
            select.dataset.selectedCourse = select.value;

            // Reset group select when course changes
            setEnrollGroupOptions('');
        }

        function setEnrollGroupOptions(selectedGroupId) {
            const select = document.getElementById('enroll_class_id');
            const courseSelect = document.getElementById('enroll_course_id');

            if (!select || !courseSelect) {
                return;
            }

            const courseId = courseSelect.value;
            const groups = secretaryGroupJson('secretaryGroupClassesData');
            const filteredGroups = groups.filter((group) => String(group.course_id) === String(courseId));
            const placeholder = !courseId
                ? 'Select a course first'
                : filteredGroups.length === 0
                    ? 'No groups for this course'
                    : 'Select group';

            select.innerHTML = '';
            select.appendChild(new Option(placeholder, ''));

            filteredGroups.forEach((group) => {
                const courseCode = group.course_code ? ` (${group.course_code})` : '';
                const teacherName = group.teacher_name ? ` - ${group.teacher_name}` : '';
                const label = `#${group.id} - ${group.course_name}${courseCode}${teacherName} (${group.students_count}/${group.capacity})`;

                select.appendChild(new Option(label, String(group.id)));
            });

            const groupExists = filteredGroups.some((group) => String(group.id) === String(selectedGroupId));
            select.value = groupExists ? String(selectedGroupId) : '';
            select.disabled = !courseId || filteredGroups.length === 0;
            select.dataset.selectedClass = select.value;
        }

        let searchDebounceTimer = null;
        let teacherSearchDebounceTimer = null;

        function performTeacherSearch(query) {
            const resultsPanel = document.getElementById('create_group_teacher_results');
            const trimmedQuery = (query || '').trim();

            if (trimmedQuery.length < 2) {
                resultsPanel.classList.add('hidden');
                return;
            }

            fetch(`{{ route('secretary.groups.teachers.search') }}?q=${encodeURIComponent(trimmedQuery)}`)
                .then(response => response.json())
                .then(data => {
                    if (!Array.isArray(data.teachers) || data.teachers.length === 0) {
                        resultsPanel.innerHTML = '<div class="px-3 py-2 text-sm" style="color: var(--lumina-text-muted);">No teachers found</div>';
                        resultsPanel.classList.remove('hidden');
                        return;
                    }

                    resultsPanel.innerHTML = '';
                    data.teachers.forEach((teacher) => {
                        const item = document.createElement('div');
                        item.className = 'cursor-pointer border-b px-3 py-2 last:border-b-0 hover:bg-gray-50';
                        item.innerHTML = `<p class="text-sm font-semibold" style="color: var(--lumina-text-primary);">${escapeHtml(teacher.name)}</p><p class="text-xs" style="color: var(--lumina-text-muted);">${escapeHtml(teacher.email)}</p>`;
                        item.addEventListener('click', () => selectTeacher(teacher.id, teacher.name, teacher.email));
                        resultsPanel.appendChild(item);
                    });

                    resultsPanel.classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Teacher search error:', error);
                    resultsPanel.innerHTML = '<div class="px-3 py-2 text-sm" style="color: #991B1B;">Search error</div>';
                    resultsPanel.classList.remove('hidden');
                });
        }

        function selectTeacher(id, name, email) {
            document.getElementById('create_group_teacher_id').value = id;
            document.getElementById('create_group_teacher_search').value = `${name} (${email})`;
            document.getElementById('create_group_teacher_search').blur();
            document.getElementById('create_group_teacher_results').classList.add('hidden');
        }

        function performStudentSearch(query) {
            const resultsPanel = document.getElementById('enroll_student_results');
            const trimmedQuery = (query || '').trim();

            if (trimmedQuery.length < 2) {
                resultsPanel.classList.add('hidden');
                return;
            }

            fetch(`{{ route('secretary.groups.students.search') }}?q=${encodeURIComponent(trimmedQuery)}`)
                .then(response => response.json())
                .then(data => {
                    if (!Array.isArray(data.students) || data.students.length === 0) {
                        resultsPanel.innerHTML = '<div class="px-3 py-2 text-sm" style="color: var(--lumina-text-muted);">No students found</div>';
                        resultsPanel.classList.remove('hidden');
                        return;
                    }

                    resultsPanel.innerHTML = '';
                    data.students.forEach((student) => {
                        const item = document.createElement('div');
                        item.className = 'cursor-pointer border-b px-3 py-2 last:border-b-0 hover:bg-gray-50';
                        item.innerHTML = `<p class="text-sm font-semibold" style="color: var(--lumina-text-primary);">${escapeHtml(student.name)}</p><p class="text-xs" style="color: var(--lumina-text-muted);">${escapeHtml(student.email)}</p>`;
                        item.addEventListener('click', () => selectStudent(student.id, student.name, student.email));
                        resultsPanel.appendChild(item);
                    });

                    resultsPanel.classList.remove('hidden');
                })
                .catch(error => {
                    console.error('Student search error:', error);
                    resultsPanel.innerHTML = '<div class="px-3 py-2 text-sm" style="color: #991B1B;">Search error</div>';
                    resultsPanel.classList.remove('hidden');
                });
        }

        function selectStudent(id, name, email) {
            document.getElementById('enroll_student_id').value = id;
            document.getElementById('enroll_student_search').value = `${name} (${email})`;
            document.getElementById('enroll_student_search').blur();
            document.getElementById('enroll_student_results').classList.add('hidden');
            document.getElementById('enroll_student_display').textContent = `${name} (${email})`;
            document.getElementById('enroll_student_selected').classList.remove('hidden');
        }

        function escapeHtml(text) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return text.replace(/[&<>"']/g, (m) => map[m]);
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Setup Create Group form
            const createProgramSelect = document.getElementById('create_group_program_id');
            const createCourseSelect = document.getElementById('create_group_course_id');
            const createTeacherSearch = document.getElementById('create_group_teacher_search');

            if (createProgramSelect && createCourseSelect) {
                setSecretaryCreateCourseOptions(createProgramSelect.value, createCourseSelect.dataset.selectedCourse || '');

                createProgramSelect.addEventListener('change', function () {
                    setSecretaryCreateCourseOptions(this.value, '');
                });
            }

            if (createTeacherSearch) {
                createTeacherSearch.addEventListener('input', function () {
                    clearTimeout(teacherSearchDebounceTimer);
                    teacherSearchDebounceTimer = setTimeout(() => {
                        performTeacherSearch(this.value);
                    }, 300);
                });

                createTeacherSearch.addEventListener('focus', function () {
                    if (this.value.length >= 2) {
                        document.getElementById('create_group_teacher_results').classList.remove('hidden');
                    }
                });

                document.addEventListener('click', function (e) {
                    if (!e.target.closest('#create_group_teacher_search') && !e.target.closest('#create_group_teacher_results')) {
                        document.getElementById('create_group_teacher_results').classList.add('hidden');
                    }
                });
            }

            // Setup Enroll Student form
            const enrollProgramSelect = document.getElementById('enroll_program_id');
            const enrollCourseSelect = document.getElementById('enroll_course_id');
            const enrollClassSelect = document.getElementById('enroll_class_id');
            const enrollStudentSearch = document.getElementById('enroll_student_search');

            if (enrollProgramSelect) {
                setEnrollCourseOptions(enrollProgramSelect.value, enrollCourseSelect?.dataset.selectedCourse || '');

                enrollProgramSelect.addEventListener('change', function () {
                    setEnrollCourseOptions(this.value, '');
                });
            }

            if (enrollCourseSelect) {
                setEnrollGroupOptions(enrollClassSelect?.dataset.selectedClass || '');

                enrollCourseSelect.addEventListener('change', function () {
                    setEnrollGroupOptions('');
                });
            }

            if (enrollStudentSearch) {
                enrollStudentSearch.addEventListener('input', function () {
                    clearTimeout(searchDebounceTimer);
                    searchDebounceTimer = setTimeout(() => {
                        performStudentSearch(this.value);
                    }, 300);
                });

                enrollStudentSearch.addEventListener('focus', function () {
                    if (this.value.length >= 2) {
                        document.getElementById('enroll_student_results').classList.remove('hidden');
                    }
                });

                document.addEventListener('click', function (e) {
                    if (!e.target.closest('#enroll_student_search') && !e.target.closest('#enroll_student_results')) {
                        document.getElementById('enroll_student_results').classList.add('hidden');
                    }
                });
            }
        });

        function openEditGroupModal(button) {
            const modal = document.getElementById('editGroupModal');
            const form = document.getElementById('editGroupForm');

            if (!modal || !form || !button) {
                return;
            }

            const id = button.getAttribute('data-id') || '';
            const courseId = button.getAttribute('data-course-id') || '';
            const teacherId = button.getAttribute('data-teacher-id') || '';
            const capacity = button.getAttribute('data-capacity') || '30';
            const studentsCount = button.getAttribute('data-students-count') || '0';

            form.action = `{{ url('/secretary/groups') }}/${id}`;

            const courseField = document.getElementById('edit_group_course_id');
            const teacherField = document.getElementById('edit_group_teacher_id');
            const capacityField = document.getElementById('edit_group_capacity');
            const hint = document.getElementById('edit_group_capacity_hint');

            if (courseField) courseField.value = courseId;
            if (teacherField) teacherField.value = teacherId;
            if (capacityField) {
                capacityField.value = capacity;
                capacityField.min = String(Math.max(1, parseInt(studentsCount, 10) || 0));
            }
            if (hint) {
                hint.textContent = `Enrolled students: ${studentsCount}. Capacity cannot go below this number.`;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEditGroupModal() {
            const modal = document.getElementById('editGroupModal');
            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
</x-layouts.secretary>
