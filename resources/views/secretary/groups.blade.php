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
        $oldStudentGroupId = old('class_id', old('remove_class_id'));
        $oldStudentCourseId = (string) old('enroll_course_id', old('remove_course_id', ''));
        $oldStudentProgramId = (string) old('enroll_program_id', old('remove_program_id', ''));
        $oldStudentAction = (string) old('group_student_action', old('remove_student_id') ? 'remove' : 'add');

        if ($oldStudentProgramId === '' && $oldStudentGroupId) {
            $oldStudentGroup = ($enrollGroups ?? collect())->firstWhere('id', (int) $oldStudentGroupId);
            $oldStudentProgramId = (string) ($oldStudentGroup?->course?->program_id ?? '');
            $oldStudentCourseId = (string) ($oldStudentGroup?->course_id ?? $oldStudentCourseId);
        }

        $secretaryGroupCoursesData = ($courses ?? collect())->map(fn ($course): array => [
            'id' => $course->id,
            'name' => $course->name,
            'code' => $course->code,
            'program_id' => $course->program_id,
        ])->values()->all();

        $secretaryGroupClassesData = ($enrollGroups ?? collect())->map(fn ($group): array => [
            'id' => $group->id,
            'course_id' => $group->course_id,
            'course_name' => $group->course?->name ?? 'Course',
            'course_code' => $group->course?->code,
            'program_id' => $group->course?->program_id,
            'teacher_name' => $group->teacher?->name,
            'students_count' => $group->students_count,
            'capacity' => $group->capacity,
        ])->values()->all();

        $studentSearchTerm = mb_strtolower($studentSearch ?? '');
        $secretaryGroupStudentsData = $groups->getCollection()->mapWithKeys(function ($group): array {
            $coursePrice = max(0, (int) ($group->course?->price ?? 0));

            $students = $group->students->map(function ($student) use ($coursePrice): array {
                $totalPaid = (int) $student->tuitionPaymentsAsStudent->sum('amount');
                $tuitionAmount = max(0, (int) ($student->studentTuition?->course_price ?? $coursePrice));
                $paymentStatus = $tuitionAmount <= 0
                    ? 'Not priced'
                    : ($totalPaid >= $tuitionAmount
                        ? 'Paid'
                        : ($totalPaid > 0 ? 'Partial' : 'Pending'));

                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'email' => $student->email,
                    'phone' => $student->phone,
                    'parent_name' => $student->parent?->name,
                    'enrolled_at' => $student->pivot?->enrolled_at
                        ? \Carbon\Carbon::parse($student->pivot->enrolled_at)->format('M d, Y')
                        : ($student->pivot?->created_at ? \Carbon\Carbon::parse($student->pivot->created_at)->format('M d, Y') : null),
                    'payment_status' => $paymentStatus,
                    'total_paid' => $totalPaid,
                    'tuition_amount' => $tuitionAmount,
                ];
            })->values()->all();

            return [(string) $group->id => $students];
        })->all();
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
            <h2 class="text-lg font-bold" style="color: var(--lumina-text-primary);">Students in Group</h2>
            <p class="mt-1 text-sm" style="color: var(--lumina-text-muted);">Select program -> course -> group, then search for a student. The form switches automatically between add and remove.</p>

            <form id="group_student_form" method="POST" action="{{ route('secretary.groups.enroll') }}" class="mt-4 grid gap-3 md:grid-cols-2">
                @csrf

                <div>
                    <label for="group_student_program_id" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Program</label>
                    <select id="group_student_program_id" name="enroll_program_id" required class="w-full rounded-lg border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background: #F8FAFC;">
                        <option value="">Select program</option>
                        @foreach($availablePrograms as $program)
                            <option value="{{ $program->id }}" @selected($oldStudentProgramId !== '' && (string) $program->id === $oldStudentProgramId)>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="group_student_course_id" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Course</label>
                    <select id="group_student_course_id" name="enroll_course_id" data-selected-course="{{ $oldStudentCourseId }}" required class="w-full rounded-lg border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background: #F8FAFC;">
                        <option value="">Select course</option>
                    </select>
                </div>

                <div>
                    <label for="group_student_class_id" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Group</label>
                    <select id="group_student_class_id" name="class_id" data-selected-class="{{ $oldStudentGroupId }}" required class="w-full rounded-lg border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background: #F8FAFC;">
                        <option value="">Select group</option>
                    </select>
                </div>

                <div class="relative">
                    <label for="group_student_search" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Student</label>
                    <input
                        id="group_student_search"
                        type="text"
                        placeholder="Select a group first"
                        class="w-full rounded-lg border px-3 py-2 text-sm"
                        style="border-color: var(--lumina-border); background: #F8FAFC;"
                        autocomplete="off"
                        disabled
                    >
                    <div id="group_student_results" class="absolute left-0 right-0 top-full mt-1 hidden max-h-48 overflow-y-auto rounded-lg border bg-white shadow-lg" style="border-color: var(--lumina-border);">
                    </div>
                </div>

                <input type="hidden" id="group_student_id" name="student_id" value="{{ old('student_id', old('remove_student_id', '')) }}">
                <input type="hidden" id="group_student_action" name="group_student_action" value="{{ $oldStudentAction }}">
                <input type="hidden" id="remove_program_id_sync" name="remove_program_id" value="{{ $oldStudentProgramId }}">
                <input type="hidden" id="remove_course_id_sync" name="remove_course_id" value="{{ $oldStudentCourseId }}">
                <input type="hidden" id="remove_class_id_sync" name="remove_class_id" value="{{ $oldStudentGroupId }}">
                <input type="hidden" id="remove_student_id_sync" name="remove_student_id" value="{{ old('remove_student_id', '') }}">

                <div id="group_student_selected" class="hidden rounded-lg border p-3 md:col-span-2" style="border-color: var(--lumina-border); background: #F8FAFC;">
                    <p class="text-xs font-semibold" style="color: var(--lumina-text-muted);">Selected Student</p>
                    <p id="group_student_display" class="mt-1 text-sm font-semibold" style="color: var(--lumina-text-primary);"></p>
                    <p id="group_student_status" class="mt-2 text-xs font-semibold" style="color: var(--lumina-text-muted);"></p>
                </div>

                <div class="md:col-span-2 flex gap-2">
                    <button id="group_student_submit" type="submit" disabled class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background-color: #94A3B8;">
                        Select a Student
                    </button>
                    <button type="reset" class="rounded-lg border px-4 py-2 text-sm font-semibold" style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);">
                        Clear
                    </button>
                </div>

                <p id="group_student_hint" class="md:col-span-2 text-xs" style="color: var(--lumina-text-muted);">Select a group first to search approved students by name or email.</p>
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
        <form method="GET" action="{{ route('secretary.groups') }}" class="grid gap-3 md:grid-cols-6">
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
                <label for="student_search" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Student Search</label>
                <input
                    id="student_search"
                    name="student_search"
                    value="{{ $studentSearch }}"
                    type="text"
                    placeholder="Student name or email"
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
            <p class="text-lg font-semibold" style="color: var(--lumina-text-primary);">
                {{ $studentSearch !== '' ? 'No matching student groups found' : 'No groups match this filter' }}
            </p>
            <p class="mt-2 text-sm" style="color: var(--lumina-text-muted);">
                {{ $studentSearch !== '' ? 'No enrolled student name or email matched this search.' : 'Try relaxing one or more filters.' }}
            </p>
        </section>
    @else
        @if($studentSearch !== '')
            <section class="mb-4 rounded-2xl border p-4" style="background: #F8FAFC; border-color: var(--lumina-border-light);">
                <p class="text-sm font-bold" style="color: var(--lumina-text-primary);">Student search results for "{{ $studentSearch }}"</p>
                <div class="mt-3 grid gap-2 md:grid-cols-2">
                    @foreach($groups as $group)
                        @php
                            $matchedStudents = $group->students->filter(function ($student) use ($studentSearchTerm): bool {
                                return str_contains(mb_strtolower((string) $student->name), $studentSearchTerm)
                                    || str_contains(mb_strtolower((string) $student->email), $studentSearchTerm);
                            });
                        @endphp

                        @foreach($matchedStudents as $matchedStudent)
                            <article class="rounded-xl border p-3" style="background: white; border-color: var(--lumina-border);">
                                <p class="text-sm font-semibold" style="color: var(--lumina-text-primary);">{{ $matchedStudent->name }}</p>
                                <p class="text-xs" style="color: var(--lumina-text-muted);">{{ $matchedStudent->email }}</p>
                                <p class="mt-2 text-xs" style="color: var(--lumina-text-secondary);">
                                    Group #{{ $group->id }} - {{ $group->course?->name ?? 'Course' }}
                                    @if($group->course?->program)
                                        / {{ $group->course->program->name }}
                                    @endif
                                </p>
                                <p class="mt-1 text-xs" style="color: var(--lumina-text-muted);">Teacher: {{ $group->teacher?->name ?? 'Unassigned' }}</p>
                            </article>
                        @endforeach
                    @endforeach
                </div>
            </section>
        @endif

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
                            @if($group->course?->program)
                                <p class="mt-1 text-xs font-semibold" style="color: var(--lumina-text-secondary);">
                                    Program: {{ $group->course->program->name }}
                                </p>
                            @endif
                        </div>
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold" style="background: #ECFDF5; color: #065F46;">
                            Enrolled: {{ $group->students_count }} / {{ $group->capacity }}
                        </span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <button
                            type="button"
                            onclick="openStudentsListModal({{ $group->id }}, this)"
                            data-group-title="{{ $group->course?->name ?? 'Course' }} - Group #{{ $group->id }}"
                            data-group-context="{{ $group->course?->program?->name ? $group->course?->program?->name.' / ' : '' }}Teacher: {{ $group->teacher?->name ?? 'Unassigned' }}"
                            class="rounded-lg border px-3 py-1.5 text-xs font-semibold"
                            style="border-color: #BFDBFE; color: #1D4ED8;"
                        >
                            Students List
                        </button>

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

    <div id="studentsListModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" onclick="if(event.target===this){closeStudentsListModal()}">
        <div class="flex max-h-[90vh] w-full max-w-4xl flex-col rounded-2xl" style="background-color: #FFFFFF;" onclick="event.stopPropagation()">
            <div class="border-b p-5" style="border-color: var(--lumina-border-light);">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 id="studentsListTitle" class="text-xl font-bold" style="color: var(--lumina-text-primary);">Students List</h3>
                        <p id="studentsListContext" class="mt-1 text-sm" style="color: var(--lumina-text-muted);"></p>
                    </div>
                    <button type="button" onclick="closeStudentsListModal()" class="rounded-lg p-2 hover:bg-gray-100" aria-label="Close students list">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--lumina-text-muted);"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="mt-4">
                    <label for="studentsListSearch" class="sr-only">Filter students in this group</label>
                    <input
                        id="studentsListSearch"
                        type="text"
                        placeholder="Filter this list by name, email, parent, or phone"
                        class="w-full rounded-lg border px-3 py-2 text-sm outline-none"
                        style="border-color: var(--lumina-border); background: #F8FAFC;"
                    >
                </div>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto p-5">
                <div id="studentsListEmpty" class="hidden rounded-xl border p-6 text-center text-sm" style="border-color: var(--lumina-border); color: var(--lumina-text-muted);">
                    No students enrolled in this group yet.
                </div>

                <div id="studentsListNoMatches" class="hidden rounded-xl border p-6 text-center text-sm" style="border-color: var(--lumina-border); color: var(--lumina-text-muted);">
                    No students match this modal filter.
                </div>

                <div id="studentsListRows" class="grid gap-3"></div>
            </div>
        </div>
    </div>

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

    <script id="secretaryGroupStudentsData" type="application/json">
        @json($secretaryGroupStudentsData)
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

        function setGroupStudentCourseOptions(programId, selectedCourseId) {
            const select = document.getElementById('group_student_course_id');

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

            syncGroupStudentHiddenFields();
            setGroupStudentGroupOptions('');
        }

        function setGroupStudentGroupOptions(selectedGroupId) {
            const select = document.getElementById('group_student_class_id');
            const courseSelect = document.getElementById('group_student_course_id');

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
                const label = `#${group.id} - ${group.course_name}${courseCode}${teacherName} (Enrolled: ${group.students_count} / ${group.capacity})`;

                select.appendChild(new Option(label, String(group.id)));
            });

            const groupExists = filteredGroups.some((group) => String(group.id) === String(selectedGroupId));
            select.value = groupExists ? String(selectedGroupId) : '';
            select.disabled = !courseId || filteredGroups.length === 0;
            select.dataset.selectedClass = select.value;

            syncGroupStudentHiddenFields();
            updateGroupStudentSearchState();
            clearGroupStudentSelection();
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
            const resultsPanel = document.getElementById('group_student_results');
            const groupSelect = document.getElementById('group_student_class_id');
            const trimmedQuery = (query || '').trim();
            const classId = groupSelect?.value || '';

            if (!resultsPanel || !classId) {
                return;
            }

            if (trimmedQuery.length < 2) {
                resultsPanel.classList.add('hidden');
                return;
            }

            fetch(`{{ route('secretary.groups.students.search') }}?q=${encodeURIComponent(trimmedQuery)}&class_id=${encodeURIComponent(classId)}`)
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
                        const enrollmentText = student.is_enrolled ? 'Already in group' : 'Not in group';
                        const enrollmentColor = student.is_enrolled ? '#B91C1C' : '#166534';
                        item.innerHTML = `<p class="text-sm font-semibold" style="color: var(--lumina-text-primary);">${escapeHtml(student.name)}</p><p class="text-xs" style="color: var(--lumina-text-muted);">${escapeHtml(student.email)}</p><p class="mt-1 text-[11px] font-semibold" style="color: ${enrollmentColor};">${enrollmentText}</p>`;
                        item.addEventListener('click', () => selectStudent(student.id, student.name, student.email, Boolean(student.is_enrolled)));
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

        function selectStudent(id, name, email, isEnrolled) {
            const studentInput = document.getElementById('group_student_id');
            const searchInput = document.getElementById('group_student_search');
            const resultsPanel = document.getElementById('group_student_results');
            const display = document.getElementById('group_student_display');
            const selected = document.getElementById('group_student_selected');
            const status = document.getElementById('group_student_status');
            const removeStudentInput = document.getElementById('remove_student_id_sync');
            const actionInput = document.getElementById('group_student_action');

            if (!studentInput || !searchInput || !resultsPanel || !display || !selected || !status || !removeStudentInput || !actionInput) {
                return;
            }

            studentInput.value = id;
            removeStudentInput.value = id;
            actionInput.value = isEnrolled ? 'remove' : 'add';
            searchInput.value = `${name} (${email})`;
            searchInput.blur();
            resultsPanel.classList.add('hidden');
            display.textContent = `${name} (${email})`;
            status.textContent = isEnrolled
                ? 'This student is already enrolled in the selected group.'
                : 'This student is not enrolled in the selected group.';
            selected.classList.remove('hidden');

            updateGroupStudentActionButton(isEnrolled);
        }

        function clearGroupStudentSelection() {
            const studentInput = document.getElementById('group_student_id');
            const removeStudentInput = document.getElementById('remove_student_id_sync');
            const searchInput = document.getElementById('group_student_search');
            const display = document.getElementById('group_student_display');
            const selected = document.getElementById('group_student_selected');
            const status = document.getElementById('group_student_status');
            const actionInput = document.getElementById('group_student_action');

            if (studentInput) {
                studentInput.value = '';
            }

            if (removeStudentInput) {
                removeStudentInput.value = '';
            }

            if (searchInput) {
                searchInput.value = '';
            }

            if (display) {
                display.textContent = '';
            }

            if (status) {
                status.textContent = '';
            }

            if (selected) {
                selected.classList.add('hidden');
            }

            if (actionInput) {
                actionInput.value = 'add';
            }

            updateGroupStudentActionButton(false);
        }

        function syncGroupStudentHiddenFields() {
            const programValue = document.getElementById('group_student_program_id')?.value || '';
            const courseValue = document.getElementById('group_student_course_id')?.value || '';
            const classValue = document.getElementById('group_student_class_id')?.value || '';

            const removeProgram = document.getElementById('remove_program_id_sync');
            const removeCourse = document.getElementById('remove_course_id_sync');
            const removeClass = document.getElementById('remove_class_id_sync');

            if (removeProgram) {
                removeProgram.value = programValue;
            }

            if (removeCourse) {
                removeCourse.value = courseValue;
            }

            if (removeClass) {
                removeClass.value = classValue;
            }
        }

        function updateGroupStudentSearchState() {
            const groupSelect = document.getElementById('group_student_class_id');
            const searchInput = document.getElementById('group_student_search');
            const hint = document.getElementById('group_student_hint');

            if (!groupSelect || !searchInput || !hint) {
                return;
            }

            const hasGroup = groupSelect.value !== '';

            searchInput.disabled = !hasGroup;
            searchInput.placeholder = hasGroup ? 'Search student by name or email' : 'Select a group first';
            hint.textContent = hasGroup
                ? 'Search approved students by name or email. The action will switch to add or remove based on current enrollment.'
                : 'Select a group first to search approved students by name or email.';
        }

        function updateGroupStudentActionButton(isEnrolled) {
            const button = document.getElementById('group_student_submit');
            const form = document.getElementById('group_student_form');
            const actionInput = document.getElementById('group_student_action');
            const hasStudent = (document.getElementById('group_student_id')?.value || '') !== '';

            if (!button || !form || !actionInput) {
                return;
            }

            if (!hasStudent) {
                button.disabled = true;
                button.textContent = 'Select a Student';
                button.style.backgroundColor = '#94A3B8';
                form.action = `{{ route('secretary.groups.enroll') }}`;
                actionInput.value = 'add';
                return;
            }

            if (isEnrolled) {
                button.disabled = false;
                button.textContent = 'Remove from Group';
                button.style.backgroundColor = '#B91C1C';
                form.action = `{{ route('secretary.groups.remove-student') }}`;
                actionInput.value = 'remove';
                return;
            }

            button.disabled = false;
            button.textContent = 'Add to Group';
            button.style.backgroundColor = '#15803D';
            form.action = `{{ route('secretary.groups.enroll') }}`;
            actionInput.value = 'add';
        }

        let activeStudentsList = [];

        function openStudentsListModal(groupId, button) {
            const modal = document.getElementById('studentsListModal');
            const title = document.getElementById('studentsListTitle');
            const context = document.getElementById('studentsListContext');
            const searchInput = document.getElementById('studentsListSearch');
            const allGroupStudents = secretaryGroupJson('secretaryGroupStudentsData');

            if (!modal || !title || !context || !searchInput) {
                return;
            }

            activeStudentsList = Array.isArray(allGroupStudents[String(groupId)])
                ? allGroupStudents[String(groupId)]
                : [];

            title.textContent = button?.dataset?.groupTitle || `Group #${groupId}`;
            context.textContent = button?.dataset?.groupContext || '';
            searchInput.value = '';
            renderStudentsList('');

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            searchInput.focus();
        }

        function closeStudentsListModal() {
            const modal = document.getElementById('studentsListModal');

            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');
            activeStudentsList = [];
        }

        function renderStudentsList(filterValue) {
            const rows = document.getElementById('studentsListRows');
            const empty = document.getElementById('studentsListEmpty');
            const noMatches = document.getElementById('studentsListNoMatches');
            const normalizedFilter = (filterValue || '').trim().toLowerCase();

            if (!rows || !empty || !noMatches) {
                return;
            }

            rows.innerHTML = '';
            empty.classList.toggle('hidden', activeStudentsList.length > 0);

            if (activeStudentsList.length === 0) {
                noMatches.classList.add('hidden');
                return;
            }

            const visibleStudents = activeStudentsList.filter((student) => {
                if (normalizedFilter === '') {
                    return true;
                }

                return [
                    student.name,
                    student.email,
                    student.parent_name,
                    student.phone,
                    student.payment_status,
                ].some((value) => String(value || '').toLowerCase().includes(normalizedFilter));
            });

            noMatches.classList.toggle('hidden', visibleStudents.length > 0);

            visibleStudents.forEach((student) => {
                const row = document.createElement('article');
                row.className = 'rounded-xl border p-4';
                row.style.borderColor = 'var(--lumina-border)';
                row.innerHTML = `
                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                        <div>
                            <p class="text-sm font-bold" style="color: var(--lumina-text-primary);">${escapeHtml(student.name)}</p>
                            <p class="mt-1 text-xs" style="color: var(--lumina-text-muted);">${escapeHtml(student.email)}</p>
                            <p class="mt-1 text-xs" style="color: var(--lumina-text-muted);">Parent: ${escapeHtml(student.parent_name || 'Not linked')}</p>
                            <p class="mt-1 text-xs" style="color: var(--lumina-text-muted);">Phone: ${escapeHtml(student.phone || 'Not provided')}</p>
                        </div>
                        <div class="grid gap-2 text-left md:min-w-44">
                            <span class="rounded-lg px-2.5 py-1 text-xs font-semibold" style="background: #F1F5F9; color: #0F172A;">Enrolled: ${escapeHtml(student.enrolled_at || 'Not recorded')}</span>
                            <span class="rounded-lg px-2.5 py-1 text-xs font-semibold" style="background: ${student.payment_status === 'Paid' ? '#DCFCE7' : (student.payment_status === 'Partial' ? '#FEF3C7' : '#FEE2E2')}; color: ${student.payment_status === 'Paid' ? '#166534' : (student.payment_status === 'Partial' ? '#92400E' : '#991B1B')};">Payment: ${escapeHtml(student.payment_status)}</span>
                        </div>
                    </div>
                `;
                rows.appendChild(row);
            });
        }

        function escapeHtml(text) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(text ?? '').replace(/[&<>"']/g, (m) => map[m]);
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

            // Setup Students in Group form
            const groupStudentForm = document.getElementById('group_student_form');
            const groupStudentProgramSelect = document.getElementById('group_student_program_id');
            const groupStudentCourseSelect = document.getElementById('group_student_course_id');
            const groupStudentClassSelect = document.getElementById('group_student_class_id');
            const groupStudentSearch = document.getElementById('group_student_search');

            if (groupStudentForm) {
                groupStudentForm.addEventListener('reset', function () {
                    requestAnimationFrame(() => {
                        syncGroupStudentHiddenFields();
                        updateGroupStudentSearchState();
                        clearGroupStudentSelection();
                    });
                });
            }

            if (groupStudentProgramSelect) {
                setGroupStudentCourseOptions(groupStudentProgramSelect.value, groupStudentCourseSelect?.dataset.selectedCourse || '');

                groupStudentProgramSelect.addEventListener('change', function () {
                    setGroupStudentCourseOptions(this.value, '');
                });
            }

            if (groupStudentCourseSelect) {
                setGroupStudentGroupOptions(groupStudentClassSelect?.dataset.selectedClass || '');

                groupStudentCourseSelect.addEventListener('change', function () {
                    syncGroupStudentHiddenFields();
                    setGroupStudentGroupOptions('');
                });
            }

            if (groupStudentClassSelect) {
                updateGroupStudentSearchState();

                groupStudentClassSelect.addEventListener('change', function () {
                    syncGroupStudentHiddenFields();
                    updateGroupStudentSearchState();
                    clearGroupStudentSelection();
                });
            }

            if (groupStudentSearch) {
                updateGroupStudentSearchState();
                updateGroupStudentActionButton(`{{ $oldStudentAction }}` === 'remove');

                groupStudentSearch.addEventListener('input', function () {
                    clearTimeout(searchDebounceTimer);
                    searchDebounceTimer = setTimeout(() => {
                        performStudentSearch(this.value);
                    }, 300);
                });

                groupStudentSearch.addEventListener('focus', function () {
                    if (!this.disabled && this.value.length >= 2) {
                        document.getElementById('group_student_results').classList.remove('hidden');
                    }
                });

                document.addEventListener('click', function (e) {
                    if (!e.target.closest('#group_student_search') && !e.target.closest('#group_student_results')) {
                        document.getElementById('group_student_results').classList.add('hidden');
                    }
                });
            }

            const studentsListSearch = document.getElementById('studentsListSearch');

            if (studentsListSearch) {
                studentsListSearch.addEventListener('input', function () {
                    renderStudentsList(this.value);
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') {
                    return;
                }

                closeStudentsListModal();
            });
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
