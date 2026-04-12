<x-layouts.student :title="__('Academic Information')" :currentRoute="'academic'">
    {{-- Page Header --}}
    <div class="mb-8 flex flex-col gap-2">
        <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
            Student Academic Record
        </h1>
        <p class="text-lg" style="color: var(--lumina-text-secondary);">
            Track your attendance, evaluations, and weekly schedule.
        </p>
    </div>

    {{-- WEEKLY TIMETABLE SECTION (Reused from Teacher Dashboard) --}}
    {{-- IDENTICAL UI/UX to: resources/views/teacher/dashboard.blade.php --}}
    {{-- Dimensions, layout, styling, and table structure are EXACT replicas --}}
    <div class="mb-8 rounded-3xl border p-6" style="background: white; border-color: var(--lumina-border-light);">
        {{-- Timetable Header --}}
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold tracking-tight" style="color: var(--lumina-text-primary);">TIME TABLE</h2>
                <p class="text-sm" style="color: var(--lumina-text-muted);">Academic Year {{ now()->year }}-{{ now()->year + 1 }}</p>
            </div>
            {{-- Print Button (Same styling as teacher dashboard) --}}
            <button 
                class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-md transition-all duration-200 hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]"
                style="background-color: var(--lumina-primary);"
                onclick="window.print()"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>
        </div>

        {{-- Timetable Grid (Same structure as teacher dashboard) --}}
        <div class="overflow-x-auto">
            <table class="w-full border-collapse min-w-[800px]">
                <thead>
                    <tr>
                        <th class="p-3 text-left text-sm font-semibold border-b-2" style="color: var(--lumina-text-muted); border-color: var(--lumina-border); min-width: 100px;">TIME</th>
                        @foreach(['08:00 - 09:30', '09:30 - 11:00', '11:00 - 12:30', '12:30 - 14:00', '14:00 - 15:30', '15:30 - 17:00'] as $timeSlot)
                            <th class="p-3 text-center text-xs font-semibold border-b-2" style="color: var(--lumina-text-muted); border-color: var(--lumina-border); min-width: 110px;">{{ $timeSlot }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $days = ['SATURDAY', 'SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY'];
                        
                        // Mock timetable data - structured as [day][timeSlot] => class info
                        // MODIFIED: Changed from teacher's IELTS/CEFR/VIP classes to student course schedule
                        $timetableData = $weeklySchedule ?? [
                            'SATURDAY' => [],
                            'SUNDAY' => [
                                2 => ['name' => 'English B2', 'room' => 'Room 101', 'color' => '#D1FAE5', 'border' => '#10B981'],
                                3 => ['name' => 'English B2', 'room' => 'Room 101', 'color' => '#E0E7FF', 'border' => '#6366F1'],
                            ],
                            'MONDAY' => [],
                            'TUESDAY' => [
                                2 => ['name' => 'Spanish A2', 'room' => 'Room 203', 'color' => '#D1FAE5', 'border' => '#10B981'],
                                3 => ['name' => 'Spanish A2', 'room' => 'Room 203', 'color' => '#D1FAE5', 'border' => '#10B981'],
                            ],
                            'WEDNESDAY' => [],
                            'THURSDAY' => [
                                2 => ['name' => 'French B1', 'room' => 'Room 305', 'color' => '#F3F4F6', 'border' => '#6B7280'],
                            ],
                            'FRIDAY' => [],
                        ];
                    @endphp

                    @foreach($days as $day)
                        <tr class="border-b" style="border-color: var(--lumina-border-light);">
                            <td class="p-3 text-sm font-semibold h-16" style="color: var(--lumina-text-primary);">{{ $day }}</td>
                            @for($slot = 0; $slot < 6; $slot++)
                                <td class="p-2 text-center h-16">
                                    @if(isset($timetableData[$day][$slot]))
                                        @php $class = $timetableData[$day][$slot]; @endphp
                                        <div 
                                            class="rounded-lg p-3 border-l-4 h-full flex flex-col justify-center transition-all duration-200 hover:shadow-md cursor-pointer"
                                            style="background-color: {{ $class['color'] }}; border-left-color: {{ $class['border'] }};"
                                        >
                                            <p class="text-xs font-bold truncate" style="color: var(--lumina-text-primary);">{{ $class['name'] }}</p>
                                            <p class="text-xs mt-1" style="color: var(--lumina-text-muted);">{{ $class['room'] }}</p>
                                        </div>
                                    @endif
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ======================================================================= --}}
    {{-- Attendance History START                                              --}}
    {{-- Scoped styles and component data - keeps attendance history isolated  --}}
    {{-- ======================================================================= --}}
    <style>
        .attendance-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
            border: 1px solid #e5e7eb;
            overflow: hidden;
            width: 100%;
            display: flex;
            flex-direction: column;
            height: 680px;
        }

        .attendance-top-bar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 20px 24px;
            gap: 16px;
            border-bottom: 1px solid #f3f4f6;
        }

        .attendance-week-selector {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
            text-align: left;
        }

        .attendance-week-selector label {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .attendance-week-select {
            appearance: none;
            background: #eaf5f0;
            border: 1.5px solid #d4ede4;
            border-radius: 8px;
            padding: 7px 32px 7px 12px;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            color: #1a6b4a;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%231a6b4a' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            transition: border-color .2s, box-shadow .2s;
        }
        .attendance-week-select:focus { 
            outline: none; 
            border-color: #2e8b6a; 
            box-shadow: 0 0 0 3px rgba(46,139,106,.15); 
        }

        .attendance-date-badge {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        .attendance-date-badge .day-name {
            font-size: 13px;
            font-weight: 700;
            color: #2e8b6a;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .attendance-date-badge .full-date {
            font-size: 12px;
            color: #9ca3af;
            font-weight: 500;
        }

        .attendance-header-section {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
        .attendance-header-section h2 {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }
        .attendance-header-section p {
            font-size: 12px;
            color: #6b7280;
            margin: 0;
        }

        .attendance-summary-row {
            display: flex;
            gap: 12px;
            padding: 14px 24px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            flex-wrap: wrap;
        }
        .attendance-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }
        .attendance-pill-icon { 
            width: 18px; height: 18px; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        .attendance-pill.present  { background: #eaf5f0; color: #1a6b4a; }
        .attendance-pill.late     { background: #fef3c7; color: #92400e; }
        .attendance-pill.absent   { background: #fee2e2; color: #991b1b; }
        .attendance-pill.present .attendance-pill-icon  { background: #2e8b6a; }
        .attendance-pill.late .attendance-pill-icon     { background: #f59e0b; }
        .attendance-pill.absent .attendance-pill-icon   { background: #ef4444; }
        .attendance-pill-icon svg { width: 10px; height: 10px; stroke: #fff; stroke-width: 2.5; fill: none; }

        .attendance-days-list { 
            padding: 16px 24px;
            overflow-y: auto;
            flex: 1;
        }
        .attendance-days-list::-webkit-scrollbar {
            width: 6px;
        }
        .attendance-days-list::-webkit-scrollbar-track {
            background: transparent;
        }
        .attendance-days-list::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }
        .attendance-days-list::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        .attendance-day-group { margin-top: 16px; }

        .attendance-day-group:first-child { margin-top: 0; }

        .attendance-day-label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .attendance-day-label span {
            font-size: 12px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .07em;
        }
        .attendance-day-label .today-badge {
            font-size: 11px;
            font-weight: 700;
            background: #2e8b6a;
            color: #fff;
            border-radius: 999px;
            padding: 2px 9px;
            letter-spacing: .04em;
        }
        .attendance-day-divider {
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .attendance-classes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 10px;
        }

        .attendance-class-card {
            border-radius: 12px;
            border: 1.5px solid #e5e7eb;
            background: white;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            transition: box-shadow .2s, transform .2s;
            position: relative;
            overflow: hidden;
        }
        .attendance-class-card::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
            border-radius: 4px 0 0 4px;
        }
        .attendance-class-card.present::before { background: #2e8b6a; }
        .attendance-class-card.late::before    { background: #f59e0b; }
        .attendance-class-card.absent::before  { background: #ef4444; }

        .attendance-class-card:hover { 
            box-shadow: 0 4px 16px rgba(0,0,0,.10); 
            transform: translateY(-2px); 
        }

        .attendance-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8px;
        }

        .attendance-lang-name {
            font-size: 14px;
            font-weight: 700;
            color: #1f2937;
        }
        .attendance-group-tag {
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            margin-top: 1px;
        }

        .attendance-status-badge {
            display: flex;
            align-items: center;
            gap: 5px;
            border-radius: 999px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .attendance-status-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
        }
        .attendance-status-badge.present  { background: #eaf5f0; color: #1a6b4a; }
        .attendance-status-badge.late     { background: #fef3c7; color: #92400e; }
        .attendance-status-badge.absent   { background: #fee2e2; color: #991b1b; }
        .attendance-status-badge.present .attendance-status-dot { background: #2e8b6a; }
        .attendance-status-badge.late .attendance-status-dot    { background: #f59e0b; }
        .attendance-status-badge.absent .attendance-status-dot  { background: #ef4444; }

        .attendance-card-time {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            color: #9ca3af;
            font-weight: 500;
        }
        .attendance-card-time svg { 
            width: 13px; height: 13px; 
            stroke: #9ca3af; 
            stroke-width: 2; 
            fill: none; 
            flex-shrink: 0; 
        }

        .attendance-no-class {
            font-size: 12px;
            color: #9ca3af;
            font-style: italic;
            padding: 4px 0;
        }

        .shared-week-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #f9fafb;
            margin-bottom: 14px;
        }

        .shared-week-left {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .shared-week-left .label {
            font-size: 12px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .main-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0,0,0,.08);
            overflow: hidden;
            width: 100%;
            height: 680px;
            display: flex;
            flex-direction: column;
        }

        .card-header {
            padding: 22px 24px 16px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .card-header-left h2 {
            font-size: 20px;
            font-weight: 800;
            color: #1f2937;
        }

        .card-header-left p {
            font-size: 13px;
            color: #6b7280;
            margin-top: 2px;
            font-weight: 500;
        }

        .summary-pills {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .pill {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
        }

        .pill-dot { width: 9px; height: 9px; border-radius: 50%; }
        .pill.good { background: #eaf5f0; color: #1a6b4a; }
        .pill.good .pill-dot { background: #2e8b6a; }
        .pill.poor { background: #fee2e2; color: #991b1b; }
        .pill.poor .pill-dot { background: #ef4444; }
        .pill.avg { background: #f3f4f6; color: #374151; }
        .pill.avg .pill-dot { background: #9ca3af; }

        .filter-bar {
            padding: 12px 24px;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-label {
            font-size: 12px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .06em;
        }

        .filter-select {
            appearance: none;
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            padding: 6px 28px 6px 11px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='13' height='13' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 9px center;
        }

        .filter-select:focus {
            outline: none;
            border-color: #2e8b6a;
            box-shadow: 0 0 0 3px rgba(46,139,106,.12);
        }

        .grades-list {
            padding: 16px 18px 18px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            overflow-y: auto;
            flex: 1;
        }

        .grades-list::-webkit-scrollbar {
            width: 6px;
        }

        .grades-list::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }

        .grade-card {
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            background: #fff;
            display: grid;
            grid-template-columns: 4px 150px 1px 1fr;
            overflow: hidden;
            transition: box-shadow .2s, transform .15s;
        }

        .grade-card:hover {
            box-shadow: 0 6px 20px rgba(0,0,0,.09);
            transform: translateY(-1px);
        }

        .grade-card.good .accent-bar { background: #2e8b6a; }
        .grade-card.poor .accent-bar { background: #ef4444; }

        .grade-left {
            padding: 12px 14px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 7px;
        }

        .grade-badge {
            display: inline-flex;
            align-items: baseline;
            gap: 3px;
            font-weight: 800;
            border-radius: 8px;
            padding: 5px 10px;
            width: fit-content;
        }

        .grade-card.good .grade-badge {
            background: #eaf5f0;
            color: #1a6b4a;
            border: 1.5px solid #d4ede4;
        }

        .grade-card.poor .grade-badge {
            background: #fee2e2;
            color: #991b1b;
            border: 1.5px solid #fca5a5;
        }

        .grade-num {
            font-size: 20px;
            line-height: 1;
            letter-spacing: -.5px;
        }

        .assessment-name {
            font-size: 12px;
            font-weight: 700;
            color: #374151;
            line-height: 1.3;
        }

        .group-tag {
            font-size: 11px;
            font-weight: 600;
            color: #9ca3af;
            background: #f3f4f6;
            border-radius: 999px;
            padding: 2px 8px;
            width: fit-content;
            max-width: 132px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .v-divider { background: #e5e7eb; }

        .grade-right {
            padding: 12px 16px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 5px;
        }

        .teacher-row {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .teacher-spacer {
            flex: 1;
        }

        .teacher-contact-btn {
            width: 26px;
            height: 26px;
            border-radius: 999px;
            border: 1.5px solid #d4ede4;
            background: #eaf5f0;
            color: #1a6b4a;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .18s, border-color .18s, transform .15s;
        }

        .teacher-contact-btn:hover {
            background: #d4ede4;
            border-color: #2e8b6a;
            transform: translateY(-1px);
        }

        .teacher-contact-btn svg {
            width: 13px;
            height: 13px;
            stroke: currentColor;
            stroke-width: 2.1;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .teacher-contact-btn:disabled {
            opacity: .45;
            cursor: not-allowed;
            transform: none;
        }

        .teacher-avatar {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #1a6b4a;
            color: #fff;
            font-size: 9px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .teacher-name {
            font-size: 12px;
            font-weight: 700;
            color: #6b7280;
        }

        .feedback-text {
            font-size: 12.5px;
            color: #6b7280;
            font-weight: 500;
            line-height: 1.45;
        }

        .grade-date {
            font-size: 11px;
            font-weight: 600;
            color: #9ca3af;
        }

        .empty-state {
            padding: 36px 20px;
            text-align: center;
            color: #9ca3af;
            font-size: 14px;
            font-weight: 500;
        }

        .section-divider {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 3px 0 2px;
        }

        .section-divider span {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .07em;
            color: #9ca3af;
            white-space: nowrap;
        }

        .section-divider .line {
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }
    </style>

    <div class="shared-week-toolbar">
        <div class="shared-week-left">
            <span class="label">Week</span>
            <select class="attendance-week-select" id="attendanceWeekSelect" onchange="handleWeekChange()"></select>
        </div>
        <div class="attendance-date-badge">
            <span class="day-name" id="attendanceTodayName"></span>
            <span class="full-date" id="attendanceTodayDate"></span>
        </div>
    </div>

    {{-- Bottom Layout --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Attendance History Card (Replaces Presence Tracker) --}}
        <div class="flex flex-col gap-6">
            <div class="attendance-card">
                {{-- Top Bar with Title --}}
                <div class="attendance-top-bar">
                    <div class="attendance-header-section">
                        <h2>Attendance History</h2>
                        <p>Your weekly class attendance overview.</p>
                    </div>
                </div>

                {{-- Summary Row --}}
                <div class="attendance-summary-row" id="attendanceSummaryRow"></div>

                {{-- Days List (Scrollable) --}}
                <div class="attendance-days-list" id="attendanceDaysList"></div>
            </div>
        </div>

        {{-- // Grades & Feedback START --}}
        <div class="flex flex-col gap-6">
            <div class="main-card">
                <div class="card-header">
                    <div class="card-header-left">
                        <h2>Grades &amp; Feedback</h2>
                        <p>Your recent assessments and teacher evaluations.</p>
                    </div>
                    <div class="summary-pills" id="summaryPills"></div>
                </div>

                <div class="filter-bar">
                    <span class="filter-label">Filter by</span>
                    <select class="filter-select" id="subjectFilter" onchange="gradesRenderWeek()">
                        <option value="all">All Subjects</option>
                    </select>
                    <select class="filter-select" id="resultFilter" onchange="gradesRenderWeek()">
                        <option value="all">All Results</option>
                        <option value="good">Pass</option>
                        <option value="poor">Fail</option>
                    </select>
                </div>

                <div class="grades-list" id="gradesList"></div>
            </div>
        </div>
        {{-- // Grades & Feedback END --}}
    </div>

    {{-- Attendance History END - JavaScript Logic --}}
    <script id="academicAttendanceWeeks" type="application/json">@json($attendanceWeeks ?? [])</script>
    <script id="academicRawEvaluations" type="application/json">@json($evaluations ?? [])</script>
    <script>
        // Attendance History Data
        const ATTENDANCE_WEEKS = JSON.parse(document.getElementById('academicAttendanceWeeks')?.textContent || '[]');

        // SVG Icons
        const ATTENDANCE_CHECK_SVG  = `<svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`;
        const ATTENDANCE_CLOCK_SVG  = `<svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>`;
        const ATTENDANCE_CROSS_SVG  = `<svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`;
        const ATTENDANCE_TIME_SVG   = `<svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>`;

        const ATTENDANCE_STATUS_LABEL = { present: "Present", late: "Late", absent: "Absent" };
        const ATTENDANCE_STATUS_ICON  = { present: ATTENDANCE_CHECK_SVG, late: ATTENDANCE_CLOCK_SVG, absent: ATTENDANCE_CROSS_SVG };
        const RAW_EVALUATIONS = JSON.parse(document.getElementById('academicRawEvaluations')?.textContent || '[]');

        function getInitials(name) {
            return (name || 'TBA')
                .split(' ')
                .filter(Boolean)
                .slice(0, 2)
                .map(part => part[0].toUpperCase())
                .join('');
        }

        function normalizeTo20(score) {
            const numeric = Number(score ?? 0);

            if (!Number.isFinite(numeric)) return 0;
            if (numeric <= 20) return Math.max(0, numeric);

            return Math.max(0, Math.min(20, Math.round((numeric / 5) * 10) / 10));
        }

        function buildGradesWeeks() {
            if (!ATTENDANCE_WEEKS.length) return [];

            const byWeek = new Map(
                ATTENDANCE_WEEKS.map(week => [week.weekStart || '', {
                    label: week.label,
                    startLabel: week.startLabel,
                    items: [],
                }])
            );

            RAW_EVALUATIONS.forEach(raw => {
                const weekStart = raw.weekStart || '';
                const bucket = byWeek.get(weekStart);

                if (!bucket) {
                    return;
                }

                const teacherName = raw.teacher || 'Teacher TBA';
                const rawGrade = raw.score ?? raw.grade;
                const hasGrade = rawGrade !== null && rawGrade !== undefined && rawGrade !== '';
                const normalizedGrade = hasGrade ? normalizeTo20(rawGrade) : null;

                bucket.items.push({
                    subject: raw.subject || 'Subject',
                    group: raw.group || 'Group',
                    teacher: teacherName,
                    initials: raw.initials || getInitials(teacherName),
                    assessment: raw.assessment || 'Class Evaluation',
                    grade: normalizedGrade,
                    hasGrade,
                    gradeLabel: hasGrade ? String(normalizedGrade) : 'Empty',
                    feedback: raw.feedback || 'No feedback yet.',
                    enteredDate: raw.date || 'Date N/A',
                    contactUrl: raw.contactUrl || '',
                });
            });

            return ATTENDANCE_WEEKS.map(week => byWeek.get(week.weekStart || '') || {
                label: week.label,
                startLabel: week.startLabel,
                items: [],
            });
        }

        const GRADE_WEEKS = buildGradesWeeks();

        function attendanceInitializeWeekSelector() {
            const select = document.getElementById("attendanceWeekSelect");

            if (!select) return;

            select.innerHTML = ATTENDANCE_WEEKS.map((week, idx) =>
                `<option value="${idx}">${week.label} - ${week.startLabel}</option>`
            ).join('');

            if (!ATTENDANCE_WEEKS.length) {
                select.innerHTML = '<option value="0">No attendance data</option>';
                select.disabled = true;
            }
        }

        function gradesInitializeSubjectFilter() {
            const select = document.getElementById("subjectFilter");

            if (!select) return;

            const uniqueSubjects = [...new Set(RAW_EVALUATIONS.map(item => item.subject).filter(Boolean))];
            select.innerHTML = '<option value="all">All Subjects</option>';

            uniqueSubjects.forEach(subject => {
                const option = document.createElement('option');
                option.value = subject;
                option.textContent = subject;
                select.appendChild(option);
            });
        }

        // Set Today Badge
        function attendanceSetTodayBadge() {
            const now = new Date();
            const days = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
            const months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
            document.getElementById("attendanceTodayName").textContent = days[now.getDay()];
            document.getElementById("attendanceTodayDate").textContent =
                `${months[now.getMonth()]} ${now.getDate()}, ${now.getFullYear()}`;
        }

        // Render Week
        function attendanceRenderWeek() {
            if (!ATTENDANCE_WEEKS.length) {
                document.getElementById("attendanceSummaryRow").innerHTML = '';
                document.getElementById("attendanceDaysList").innerHTML = '<p class="attendance-no-class">No attendance records yet</p>';
                return;
            }

            const idx  = +document.getElementById("attendanceWeekSelect").value;
            const week = ATTENDANCE_WEEKS[idx];

            // Summary counts
            let counts = { present: 0, late: 0, absent: 0 };
            week.days.forEach(d => d.classes.forEach(c => counts[c.status]++));

            document.getElementById("attendanceSummaryRow").innerHTML = `
                <div class="attendance-pill present">
                    <div class="attendance-pill-icon">${ATTENDANCE_CHECK_SVG}</div>
                    ${counts.present} Present
                </div>
                <div class="attendance-pill late">
                    <div class="attendance-pill-icon">${ATTENDANCE_CLOCK_SVG}</div>
                    ${counts.late} Late
                </div>
                <div class="attendance-pill absent">
                    <div class="attendance-pill-icon">${ATTENDANCE_CROSS_SVG}</div>
                    ${counts.absent} Absent
                </div>
            `;

            // Days
            const list = document.getElementById("attendanceDaysList");
            list.innerHTML = week.days.map(day => `
                <div class="attendance-day-group">
                    <div class="attendance-day-label">
                        <span>${day.name}</span>
                        <span style="font-size:12px;color:#9ca3af;font-weight:500;">${day.date}</span>
                        ${day.today ? '<span class="today-badge">Today</span>' : ''}
                        <div class="attendance-day-divider"></div>
                    </div>
                    ${day.classes.length === 0
                        ? '<p class="attendance-no-class">No classes scheduled</p>'
                        : `<div class="attendance-classes-grid">
                            ${day.classes.map(cls => `
                                <div class="attendance-class-card ${cls.status}">
                                    <div class="attendance-card-top">
                                        <div>
                                            <div class="attendance-lang-name">${cls.lang}</div>
                                            <div class="attendance-group-tag">${cls.group}</div>
                                        </div>
                                        <div class="attendance-status-badge ${cls.status}">
                                            <span class="attendance-status-dot"></span>
                                            ${ATTENDANCE_STATUS_LABEL[cls.status] ?? 'Absent'}
                                        </div>
                                    </div>
                                    <div class="attendance-card-time">
                                        ${ATTENDANCE_TIME_SVG}
                                        ${cls.time}
                                    </div>
                                </div>
                            `).join('')}
                        </div>`
                    }
                </div>
            `).join('');
        }

        function gradesRenderWeek() {
            const list = document.getElementById("gradesList");
            const summary = document.getElementById("summaryPills");
            const weekIndex = +document.getElementById("attendanceWeekSelect").value || 0;
            const subjectFilter = document.getElementById("subjectFilter").value;
            const resultFilter = document.getElementById("resultFilter").value;
            const weekData = GRADE_WEEKS[weekIndex] || { items: [] };

            const filtered = weekData.items.filter(item => {
                const tier = item.hasGrade ? (item.grade >= 10 ? 'good' : 'poor') : 'empty';
                return (subjectFilter === 'all' || item.subject === subjectFilter) &&
                    (resultFilter === 'all' || tier === resultFilter);
            });

            const gradedOnly = weekData.items.filter(item => item.hasGrade);
            const pass = gradedOnly.filter(item => item.grade >= 10).length;
            const fail = gradedOnly.filter(item => item.grade < 10).length;
            const avg = gradedOnly.length
                ? (gradedOnly.reduce((sum, item) => sum + item.grade, 0) / gradedOnly.length).toFixed(1)
                : '0.0';

            summary.innerHTML = `
                <div class="pill good"><div class="pill-dot"></div>${pass} Pass</div>
                <div class="pill poor"><div class="pill-dot"></div>${fail} Fail</div>
                <div class="pill avg"><div class="pill-dot"></div>Avg ${avg}</div>
            `;

            if (!filtered.length) {
                list.innerHTML = '<div class="empty-state">No grades match this week and selected filters.</div>';
                return;
            }

            const grouped = {};
            filtered.forEach(item => {
                if (!grouped[item.subject]) grouped[item.subject] = [];
                grouped[item.subject].push(item);
            });

            list.innerHTML = Object.entries(grouped).map(([subject, items]) => `
                <div class="section-divider">
                    <span>${subject}</span>
                    <div class="line"></div>
                </div>
                ${items.map(item => {
                    const tier = item.hasGrade ? (item.grade >= 10 ? 'good' : 'poor') : 'poor';
                    return `
                        <div class="grade-card ${tier}">
                            <div class="accent-bar"></div>
                            <div class="grade-left">
                                <div class="grade-badge">
                                    <span class="grade-num">${item.gradeLabel}</span>
                                </div>
                                <div class="assessment-name">${item.assessment}</div>
                                <span class="group-tag">${item.group}</span>
                            </div>
                            <div class="v-divider"></div>
                            <div class="grade-right">
                                <div class="teacher-row">
                                    <div class="teacher-avatar">${item.initials}</div>
                                    <span class="teacher-name">${item.teacher}</span>
                                    <span class="teacher-spacer"></span>
                                    <button class="teacher-contact-btn" type="button" data-contact-url="${item.contactUrl}" title="Contact ${item.teacher}" aria-label="Contact ${item.teacher}" ${item.contactUrl ? '' : 'disabled'}>
                                        <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                    </button>
                                </div>
                                <div class="grade-date">Entered: ${item.enteredDate}</div>
                                <div class="feedback-text">${item.feedback}</div>
                            </div>
                        </div>
                    `;
                }).join('')}
            `).join('');

            list.querySelectorAll('.teacher-contact-btn[data-contact-url]').forEach(button => {
                button.addEventListener('click', () => {
                    const targetUrl = button.dataset.contactUrl || '';

                    if (targetUrl) {
                        window.location.href = targetUrl;
                    }
                });
            });
        }

        function handleWeekChange() {
            attendanceRenderWeek();
            gradesRenderWeek();
        }

        // Initialize
        attendanceInitializeWeekSelector();
        gradesInitializeSubjectFilter();
        attendanceSetTodayBadge();
        handleWeekChange();
    </script>
    {{-- Attendance History END --}}
</x-layouts.student>
