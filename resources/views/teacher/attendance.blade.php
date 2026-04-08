<x-layouts.teacher :title="__('Manage Attendance')" :currentRoute="'teacher.attendance'">
    @php
        $hasSelectedClass = ! empty($selectedClass['id'] ?? null);
        $hasStudents = count($students ?? []) > 0;
    @endphp

    {{-- Page Header --}}
    <div class="mb-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
                    Manage Attendance
                </h1>
                <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
                    Record attendance and evaluate student performance.
                </p>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border p-4" style="background-color: #D1FAE5; border-color: #A7F3D0; color: #065F46;">
            <p class="text-sm font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-xl border p-4" style="background-color: #FEF2F2; border-color: #FECACA; color: #991B1B;">
            <ul class="list-disc space-y-1 pl-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Controls Bar --}}
    <div class="mb-6 flex flex-col gap-4 rounded-2xl border p-4 md:flex-row md:items-center md:justify-between" style="background: white; border-color: var(--lumina-border-light);">
        {{-- Left Side: Class & Date Selection --}}
        <form id="attendanceFilterForm" method="GET" action="{{ route('teacher.attendance') }}" class="flex flex-col gap-4 sm:flex-row sm:items-end sm:gap-3">
            {{-- Class Selector --}}
            <div class="relative">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color: var(--lumina-text-muted);">Select Class</label>
                <select
                    name="class_id"
                    onchange="this.form.submit()"
                    class="input-focus w-full appearance-none rounded-xl border py-3 pl-4 pr-10 text-sm font-medium transition-all duration-200 sm:w-64"
                    style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border-light); color: var(--lumina-text-primary);"
                >
                    @forelse ($classes ?? [] as $class)
                        <option value="{{ $class['id'] }}" @selected(($selectedClass['id'] ?? null) === $class['id'])>
                            {{ $class['name'] }} ({{ $class['students_count'] }} students)
                        </option>
                    @empty
                        <option value="">No assigned classes</option>
                    @endforelse
                </select>
                <svg class="pointer-events-none absolute right-3 top-9 h-5 w-5" fill="none" stroke="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            {{-- Date Picker --}}
            <div class="relative">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color: var(--lumina-text-muted);">Date</label>
                <input 
                    type="date" 
                    name="date"
                    value="{{ $selectedDate ?? now()->format('Y-m-d') }}"
                    class="input-focus w-full rounded-xl border py-3 px-4 text-sm font-medium transition-all duration-200 sm:w-48"
                    style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border-light); color: var(--lumina-text-primary);"
                />
            </div>

            <button 
                type="submit"
                class="flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition-all duration-200 hover:bg-gray-50 active:scale-[0.98]"
                style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);"
            >
                Load
            </button>
        </form>

        {{-- Right Side: Action Buttons --}}
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('teacher.attendance.export') }}">
                <input type="hidden" name="class_id" value="{{ $selectedClass['id'] ?? '' }}">
                <input type="hidden" name="date" value="{{ $selectedDate ?? now()->format('Y-m-d') }}">

                <button
                    type="submit"
                    class="flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition-all duration-200 hover:bg-gray-50 active:scale-[0.98]"
                    style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);"
                    @disabled(! $hasSelectedClass)
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Export
                </button>
            </form>
            <button 
                type="submit"
                form="attendanceSaveForm"
                class="flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]"
                style="background: linear-gradient(135deg, #006A41 0%, #2D8C5E 100%);"
                @disabled(! ($hasSelectedClass && $hasStudents))
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save All
            </button>
        </div>
    </div>

    <form id="attendanceSaveForm" method="POST" action="{{ route('teacher.attendance.store') }}">
        @csrf
        <input type="hidden" name="class_id" value="{{ $selectedClass['id'] ?? '' }}">
        <input type="hidden" name="date" value="{{ $selectedDate ?? now()->format('Y-m-d') }}">

        {{-- Student Roster --}}
        <div class="rounded-3xl border" style="background: white; border-color: var(--lumina-border-light);">
        {{-- Table Header --}}
        <div class="border-b px-6 py-4" style="border-color: var(--lumina-border-light);">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold" style="color: var(--lumina-text-primary);">Student Roster</h2>
                <div class="flex items-center gap-2">
                    <span class="text-sm" style="color: var(--lumina-text-muted);">
                        {{ $selectedClass['name'] ?? 'No class selected' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Table Header (Fixed) --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr style="background-color: var(--lumina-bg-card);">
                        <th class="w-1/4 px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="color: var(--lumina-text-muted);">Student</th>
                        <th class="w-1/5 px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider" style="color: var(--lumina-text-muted);">Attendance</th>
                        <th class="w-1/6 px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider" style="color: var(--lumina-text-muted);">Grade</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider" style="color: var(--lumina-text-muted);">Feedback</th>
                    </tr>
                </thead>
            </table>
        </div>

        {{-- Scrollable Table Body --}}
        <div class="max-h-[420px] overflow-y-auto overflow-x-auto" style="scrollbar-width: thin; scrollbar-color: var(--lumina-border) transparent;">
            <table class="w-full">
                <tbody class="divide-y" style="--tw-divide-opacity: 1; border-color: var(--lumina-border-light);">
                    @forelse($students ?? [] as $student)
                        <tr class="group transition-colors duration-200 hover:bg-gray-50">
                            {{-- Student Info --}}
                            <td class="w-1/4 px-6 py-4">
                                <input type="hidden" name="records[{{ $student['id'] }}][student_id]" value="{{ $student['id'] }}">
                                <input
                                    type="hidden"
                                    name="records[{{ $student['id'] }}][status]"
                                    value="{{ $student['attendance'] }}"
                                    data-attendance-input
                                >

                                <div class="flex items-center gap-3">
                                    @if($student['avatar'])
                                        <img src="{{ $student['avatar'] }}" alt="{{ $student['name'] }}" class="h-10 w-10 rounded-full object-cover">
                                    @else
                                        {{-- Anonymous Profile SVG Placeholder --}}
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full" style="background-color: var(--lumina-accent-green-light);">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" style="color: var(--lumina-accent-green-dark);" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium" style="color: var(--lumina-text-primary);">{{ $student['name'] }}</p>
                                        <p class="text-xs" style="color: var(--lumina-text-muted);">ID: STU-{{ str_pad($student['id'], 4, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Attendance Toggle --}}
                            <td class="w-1/5 px-6 py-4">
                                <div class="flex items-center justify-center gap-1">
                                    {{-- Present --}}
                                    <button 
                                        type="button"
                                        data-attendance-btn
                                        data-value="present"
                                        class="flex h-10 w-10 items-center justify-center rounded-xl border-2 transition-all duration-200 hover:scale-110 {{ $student['attendance'] === 'present' ? 'bg-emerald-100 border-emerald-600 shadow-md' : 'bg-white border-zinc-300 opacity-40 hover:opacity-100' }}"
                                        title="Present"
                                    >
                                        <svg class="h-5 w-5 {{ $student['attendance'] === 'present' ? 'text-emerald-600' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                    {{-- Late --}}
                                    <button 
                                        type="button"
                                        data-attendance-btn
                                        data-value="late"
                                        class="flex h-10 w-10 items-center justify-center rounded-xl border-2 transition-all duration-200 hover:scale-110 {{ $student['attendance'] === 'late' ? 'bg-amber-100 border-amber-600 shadow-md' : 'bg-white border-zinc-300 opacity-40 hover:opacity-100' }}"
                                        title="Late"
                                    >
                                        <svg class="h-5 w-5 {{ $student['attendance'] === 'late' ? 'text-amber-600' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </button>
                                    {{-- Absent --}}
                                    <button 
                                        type="button"
                                        data-attendance-btn
                                        data-value="absent"
                                        class="flex h-10 w-10 items-center justify-center rounded-xl border-2 transition-all duration-200 hover:scale-110 {{ $student['attendance'] === 'absent' ? 'bg-red-100 border-red-600 shadow-md' : 'bg-white border-zinc-300 opacity-40 hover:opacity-100' }}"
                                        title="Absent"
                                    >
                                        <svg class="h-5 w-5 {{ $student['attendance'] === 'absent' ? 'text-red-600' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>

                            {{-- Grade Input --}}
                            <td class="w-1/6 px-6 py-4">
                                <div class="flex justify-center">
                                    <input 
                                        type="number" 
                                        min="0" 
                                        max="100" 
                                        name="records[{{ $student['id'] }}][grade]"
                                        value="{{ $student['grade'] }}"
                                        placeholder="—"
                                        data-grade-input
                                        class="input-focus w-20 rounded-xl border py-2 text-center text-sm font-medium transition-all duration-200 {{ $student['attendance'] === 'absent' ? 'opacity-50' : '' }}"
                                        style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border-light); color: var(--lumina-text-primary);"
                                        {{ $student['attendance'] === 'absent' ? 'disabled' : '' }}
                                    />
                                </div>
                            </td>

                            {{-- Feedback --}}
                            <td class="px-6 py-4">
                                <input 
                                    type="text" 
                                    name="records[{{ $student['id'] }}][feedback]"
                                    value="{{ $student['feedback'] }}"
                                    placeholder="Add feedback..."
                                    class="input-focus w-full rounded-xl border py-2 px-3 text-sm transition-all duration-200"
                                    style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border-light); color: var(--lumina-text-primary);"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm" style="color: var(--lumina-text-muted);">
                                No students found for the selected class.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Table Footer --}}
        <div class="border-t px-6 py-4" style="border-color: var(--lumina-border-light);">
            <div class="flex items-center justify-between">
                <p class="text-sm" style="color: var(--lumina-text-muted);">
                    Showing {{ count($students ?? []) }} students
                </p>
                <button 
                    type="submit"
                    class="flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]"
                    style="background: linear-gradient(135deg, #006A41 0%, #2D8C5E 100%);"
                    @disabled(! ($hasSelectedClass && $hasStudents))
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>
            </div>
        </div>
        </div>
    </form>

    {{-- Stats Cards (Summary) --}}
    <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4">
        {{-- Present --}}
        <div class="card-hover rounded-2xl border p-4 transition-all duration-200" style="background: white; border-color: var(--lumina-border-light);">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl" style="background-color: #D1FAE5;">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" style="color: #059669;" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold" style="color: #059669;">{{ $stats['present'] ?? 0 }}</p>
                    <p class="text-sm" style="color: var(--lumina-text-muted);">Present</p>
                </div>
            </div>
        </div>

        {{-- Late --}}
        <div class="card-hover rounded-2xl border p-4 transition-all duration-200" style="background: white; border-color: var(--lumina-border-light);">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl" style="background-color: #FEF3C7;">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" style="color: #D97706;" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold" style="color: #D97706;">{{ $stats['late'] ?? 0 }}</p>
                    <p class="text-sm" style="color: var(--lumina-text-muted);">Late</p>
                </div>
            </div>
        </div>

        {{-- Absent --}}
        <div class="card-hover rounded-2xl border p-4 transition-all duration-200" style="background: white; border-color: var(--lumina-border-light);">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl" style="background-color: #FEE2E2;">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" style="color: #DC2626;" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold" style="color: #DC2626;">{{ $stats['absent'] ?? 0 }}</p>
                    <p class="text-sm" style="color: var(--lumina-text-muted);">Absent</p>
                </div>
            </div>
        </div>

        {{-- Total --}}
        <div class="card-hover rounded-2xl border p-4 transition-all duration-200" style="background: white; border-color: var(--lumina-border-light);">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl" style="background-color: #E0E7FF;">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" style="color: #4F46E5;" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold" style="color: #4F46E5;">{{ $stats['total'] ?? 0 }}</p>
                    <p class="text-sm" style="color: var(--lumina-text-muted);">Total Students</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const statusStyles = {
                present: {
                    backgroundColor: '#D1FAE5',
                    borderColor: '#059669',
                    iconColor: '#059669',
                },
                late: {
                    backgroundColor: '#FEF3C7',
                    borderColor: '#D97706',
                    iconColor: '#D97706',
                },
                absent: {
                    backgroundColor: '#FEE2E2',
                    borderColor: '#DC2626',
                    iconColor: '#DC2626',
                },
            };

            const defaultButtonStyle = {
                backgroundColor: 'white',
                borderColor: 'var(--lumina-border)',
                iconColor: 'var(--lumina-text-muted)',
            };

            function applyRowStatus(row, selectedStatus) {
                const buttons = row.querySelectorAll('[data-attendance-btn]');

                buttons.forEach(function (button) {
                    const buttonStatus = button.dataset.value;
                    const icon = button.querySelector('svg');
                    const isActive = buttonStatus === selectedStatus;

                    if (isActive) {
                        button.classList.add('shadow-md');
                        button.classList.remove('opacity-40', 'hover:opacity-100');
                        button.style.backgroundColor = statusStyles[buttonStatus].backgroundColor;
                        button.style.borderColor = statusStyles[buttonStatus].borderColor;
                        if (icon) {
                            icon.style.color = statusStyles[buttonStatus].iconColor;
                        }
                    } else {
                        button.classList.remove('shadow-md');
                        button.classList.add('opacity-40', 'hover:opacity-100');
                        button.style.backgroundColor = defaultButtonStyle.backgroundColor;
                        button.style.borderColor = defaultButtonStyle.borderColor;
                        if (icon) {
                            icon.style.color = defaultButtonStyle.iconColor;
                        }
                    }
                });

                const gradeInput = row.querySelector('[data-grade-input]');
                if (gradeInput) {
                    const shouldDisable = selectedStatus === 'absent';
                    gradeInput.disabled = shouldDisable;
                    gradeInput.classList.toggle('opacity-50', shouldDisable);

                    if (shouldDisable) {
                        gradeInput.value = '';
                    }
                }
            }

            document.querySelectorAll('[data-attendance-btn]').forEach(function (button) {
                button.addEventListener('click', function () {
                    const row = button.closest('tr');
                    const statusInput = row?.querySelector('[data-attendance-input]');

                    if (!row || !statusInput) {
                        return;
                    }

                    const selectedStatus = button.dataset.value;
                    statusInput.value = selectedStatus;
                    applyRowStatus(row, selectedStatus);
                });
            });

            document.querySelectorAll('tr').forEach(function (row) {
                const statusInput = row.querySelector('[data-attendance-input]');

                if (!statusInput) {
                    return;
                }

                applyRowStatus(row, statusInput.value);
            });
        });
    </script>
</x-layouts.teacher>
