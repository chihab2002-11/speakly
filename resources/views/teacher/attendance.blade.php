<x-layouts.teacher :title="__('Manage Attendance')" :currentRoute="'teacher.attendance'">
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

    {{-- Controls Bar --}}
    <div class="mb-6 flex flex-col gap-4 rounded-2xl border p-4 md:flex-row md:items-center md:justify-between" style="background: white; border-color: var(--lumina-border-light);">
        {{-- Left Side: Class & Date Selection --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            {{-- Class Selector --}}
            <div class="relative">
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wider" style="color: var(--lumina-text-muted);">Select Class</label>
                <select 
                    class="input-focus w-full appearance-none rounded-xl border py-3 pl-4 pr-10 text-sm font-medium transition-all duration-200 sm:w-64"
                    style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border-light); color: var(--lumina-text-primary);"
                >
                    @foreach($classes ?? [] as $class)
                        <option value="{{ $class['id'] }}" {{ ($selectedClass['id'] ?? 1) === $class['id'] ? 'selected' : '' }}>
                            {{ $class['name'] }} ({{ $class['students_count'] }} students)
                        </option>
                    @endforeach
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
                    value="{{ $selectedDate ?? now()->format('Y-m-d') }}"
                    class="input-focus w-full rounded-xl border py-3 px-4 text-sm font-medium transition-all duration-200 sm:w-48"
                    style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border-light); color: var(--lumina-text-primary);"
                />
            </div>
        </div>

        {{-- Right Side: Action Buttons --}}
        <div class="flex items-center gap-3">
            <button 
                class="flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition-all duration-200 hover:bg-gray-50 active:scale-[0.98]"
                style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Export
            </button>
            <button 
                class="flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]"
                style="background: linear-gradient(135deg, #006A41 0%, #2D8C5E 100%);"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save All
            </button>
        </div>
    </div>

    {{-- Student Roster --}}
    <div class="rounded-3xl border" style="background: white; border-color: var(--lumina-border-light);">
        {{-- Table Header --}}
        <div class="border-b px-6 py-4" style="border-color: var(--lumina-border-light);">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold" style="color: var(--lumina-text-primary);">Student Roster</h2>
                <div class="flex items-center gap-2">
                    <span class="text-sm" style="color: var(--lumina-text-muted);">
                        {{ $selectedClass['name'] ?? 'French B2 - Grammar' }}
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
                    @foreach($students ?? [
                        ['id' => 1, 'name' => 'Julian Alvarez', 'avatar' => null, 'attendance' => 'present', 'grade' => 85, 'feedback' => ''],
                        ['id' => 2, 'name' => 'Elena Vance', 'avatar' => null, 'attendance' => 'present', 'grade' => 92, 'feedback' => ''],
                        ['id' => 3, 'name' => 'Marcus Chen', 'avatar' => null, 'attendance' => 'late', 'grade' => 78, 'feedback' => 'Arrived 15 minutes late'],
                        ['id' => 4, 'name' => 'Sophie Martin', 'avatar' => null, 'attendance' => 'present', 'grade' => 88, 'feedback' => ''],
                        ['id' => 5, 'name' => 'Alex Thompson', 'avatar' => null, 'attendance' => 'absent', 'grade' => null, 'feedback' => 'Sick leave'],
                        ['id' => 6, 'name' => 'Olivia Brown', 'avatar' => null, 'attendance' => 'present', 'grade' => 91, 'feedback' => ''],
                        ['id' => 7, 'name' => 'James Wilson', 'avatar' => null, 'attendance' => 'present', 'grade' => 76, 'feedback' => ''],
                        ['id' => 8, 'name' => 'Emma Davis', 'avatar' => null, 'attendance' => 'late', 'grade' => 82, 'feedback' => ''],
                        ['id' => 9, 'name' => 'Liam Johnson', 'avatar' => null, 'attendance' => 'present', 'grade' => 95, 'feedback' => 'Excellent participation'],
                        ['id' => 10, 'name' => 'Ava Miller', 'avatar' => null, 'attendance' => 'absent', 'grade' => null, 'feedback' => 'Family emergency'],
                    ] as $student)
                        <tr class="group transition-colors duration-200 hover:bg-gray-50">
                            {{-- Student Info --}}
                            <td class="w-1/4 px-6 py-4">
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
                                        class="flex h-10 w-10 items-center justify-center rounded-xl border-2 transition-all duration-200 hover:scale-110 {{ $student['attendance'] === 'present' ? 'shadow-md' : 'opacity-40 hover:opacity-100' }}"
                                        style="{{ $student['attendance'] === 'present' ? 'background-color: #D1FAE5; border-color: #059669;' : 'background-color: white; border-color: var(--lumina-border);' }}"
                                        title="Present"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" style="color: {{ $student['attendance'] === 'present' ? '#059669' : 'var(--lumina-text-muted)' }};" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                    {{-- Late --}}
                                    <button 
                                        class="flex h-10 w-10 items-center justify-center rounded-xl border-2 transition-all duration-200 hover:scale-110 {{ $student['attendance'] === 'late' ? 'shadow-md' : 'opacity-40 hover:opacity-100' }}"
                                        style="{{ $student['attendance'] === 'late' ? 'background-color: #FEF3C7; border-color: #D97706;' : 'background-color: white; border-color: var(--lumina-border);' }}"
                                        title="Late"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" style="color: {{ $student['attendance'] === 'late' ? '#D97706' : 'var(--lumina-text-muted)' }};" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </button>
                                    {{-- Absent --}}
                                    <button 
                                        class="flex h-10 w-10 items-center justify-center rounded-xl border-2 transition-all duration-200 hover:scale-110 {{ $student['attendance'] === 'absent' ? 'shadow-md' : 'opacity-40 hover:opacity-100' }}"
                                        style="{{ $student['attendance'] === 'absent' ? 'background-color: #FEE2E2; border-color: #DC2626;' : 'background-color: white; border-color: var(--lumina-border);' }}"
                                        title="Absent"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" style="color: {{ $student['attendance'] === 'absent' ? '#DC2626' : 'var(--lumina-text-muted)' }};" viewBox="0 0 24 24">
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
                                        value="{{ $student['grade'] }}"
                                        placeholder="—"
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
                                    value="{{ $student['feedback'] }}"
                                    placeholder="Add feedback..."
                                    class="input-focus w-full rounded-xl border py-2 px-3 text-sm transition-all duration-200"
                                    style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border-light); color: var(--lumina-text-primary);"
                                />
                            </td>
                        </tr>
                    @endforeach
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
                    class="flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]"
                    style="background: linear-gradient(135deg, #006A41 0%, #2D8C5E 100%);"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Changes
                </button>
            </div>
        </div>
    </div>

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
                    <p class="text-2xl font-bold" style="color: #059669;">{{ $stats['present'] ?? 18 }}</p>
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
                    <p class="text-2xl font-bold" style="color: #D97706;">{{ $stats['late'] ?? 3 }}</p>
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
                    <p class="text-2xl font-bold" style="color: #DC2626;">{{ $stats['absent'] ?? 3 }}</p>
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
                    <p class="text-2xl font-bold" style="color: #4F46E5;">{{ $stats['total'] ?? 24 }}</p>
                    <p class="text-sm" style="color: var(--lumina-text-muted);">Total Students</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Backend Summary Comment --}}
    {{-- 
    ============================================================
    BACKEND SUMMARY - Manage Attendance
    ============================================================
    
    Endpoints Needed:
    -----------------
    GET  /api/teacher/classes
         → Returns list of classes assigned to teacher
         → Response: { classes: [{ id, name, students_count }] }
    
    GET  /api/teacher/classes/{classId}/attendance?date=YYYY-MM-DD
         → Returns students with attendance for specific date
         → Response: { 
             class: { id, name },
             date: "2024-01-15",
             stats: { present, late, absent, total },
             students: [{ id, name, avatar, attendance, grade, feedback }]
           }
    
    POST /api/teacher/attendance
         → Save attendance records
         → Body: { 
             class_id: int,
             date: "YYYY-MM-DD",
             records: [{ student_id, attendance: 'present'|'late'|'absent', grade, feedback }]
           }
         → Response: { success: true, message: "Attendance saved" }
    
    GET  /api/teacher/attendance/export?class_id=X&date=YYYY-MM-DD&format=csv|pdf
         → Export attendance report
         → Response: File download
    
    Data Types:
    -----------
    attendance: enum('present', 'late', 'absent')
    grade: nullable integer (0-100)
    feedback: nullable string (max 500 chars)
    
    ============================================================
    --}}
</x-layouts.teacher>
