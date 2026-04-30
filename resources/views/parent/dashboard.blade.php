<x-layouts.parent 
    :title="'Dashboard'"
    :pageTitle="'Dashboard'"
    :currentRoute="'dashboard'"
    :user="$user ?? null"
    :children="$children ?? []"
    :selectedChild="$selectedChild ?? null"
    :hideFinancial="$hideFinancial ?? false"
>
    @php
        $childrenData = collect($children ?? [])->values();

        $selectedChildId = (int) ($selectedChild['id'] ?? ($childrenData->first()['id'] ?? 0));

        $childDashboardData = $childDashboardData ?? [];

        $defaultChildData = $childDashboardData[$selectedChildId] ?? reset($childDashboardData);
        if (! is_array($defaultChildData)) {
            $defaultChildData = [
                'timetable' => [],
                'teacherFeedbacks' => [],
                'teachers' => [],
                'progress' => [],
                'attendanceProgress' => [],
                'documentsCount' => 0,
                'teachersCount' => 0,
                'unreadMessagesCount' => 0,
            ];
        }
    @endphp

    <style>
        .lumina-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #94d3bb #edf7f1;
        }
        .lumina-scrollbar::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        .lumina-scrollbar::-webkit-scrollbar-track {
            background: #edf7f1;
            border-radius: 999px;
        }
        .lumina-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #16a34a 0%, #0f766e 100%);
            border-radius: 999px;
            border: 2px solid #edf7f1;
        }
    </style>

    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div class="flex flex-col gap-2">
            <span class="text-xs font-bold uppercase tracking-wider" style="color: var(--lumina-primary); letter-spacing: 1.2px;">
                Welcome Back, {{ $user->name ?? 'Parent' }}
            </span>
            <h2 class="text-4xl font-black" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
                Parent Overview
            </h2>
            <div class="mt-2 flex items-center gap-3">
                <span class="text-xs font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted);">Select Child</span>
                @if($childrenData->count() > 1)
                    <select id="topChildSelector" class="rounded-xl border px-3 py-2 text-sm font-semibold" style="border-color: var(--lumina-border); color: var(--lumina-text-primary); background: #fff;">
                        @foreach($childrenData as $child)
                            <option value="{{ $child['id'] }}" @selected((int) $child['id'] === $selectedChildId)>{{ $child['name'] }}</option>
                        @endforeach
                    </select>
                @else
                    <span class="rounded-xl border px-3 py-2 text-sm font-semibold" style="border-color: var(--lumina-border); color: var(--lumina-text-primary); background: #fff;">
                        {{ $childrenData->first()['name'] ?? 'Child' }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-col items-end gap-1">
            <span class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Academic Term</span>
            <span class="text-xl font-bold" style="color: #065F46;">{{ $academicTerm ?? 'Spring 2024' }}</span>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="relative self-start overflow-hidden rounded-3xl border p-6 lg:col-span-2" style="background-color: #FFFFFF; border-color: var(--lumina-border-light); box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);">
            <div class="relative z-10 flex flex-col gap-4">
                <div class="mb-2 flex items-center justify-between">
                    <div class="flex flex-col gap-1">
                        <h3 class="text-xl font-bold tracking-tight" style="color: var(--lumina-text-primary);">Teacher Feedbacks</h3>
                        <p class="text-sm" style="color: var(--lumina-text-muted);">Recent comments and performance insights from each teacher.</p>
                    </div>
                    <div class="inline-flex items-center gap-1 rounded-xl border p-1" id="feedbackWeekControls" style="border-color: var(--lumina-border); background-color: #F8FBF8;">
                        <button type="button" data-feedback-week="latest" class="feedback-week-btn rounded-lg px-3 py-1.5 text-xs font-bold uppercase tracking-wide">Latest</button>
                        <button type="button" data-feedback-week="1" class="feedback-week-btn rounded-lg px-3 py-1.5 text-xs font-bold uppercase tracking-wide">Week 1</button>
                        <button type="button" data-feedback-week="2" class="feedback-week-btn rounded-lg px-3 py-1.5 text-xs font-bold uppercase tracking-wide">Week 2</button>
                        <button type="button" data-feedback-week="3" class="feedback-week-btn rounded-lg px-3 py-1.5 text-xs font-bold uppercase tracking-wide">Week 3</button>
                        <button type="button" data-feedback-week="4" class="feedback-week-btn rounded-lg px-3 py-1.5 text-xs font-bold uppercase tracking-wide">Week 4</button>
                    </div>
                </div>

                <div id="teacherFeedbackList" class="lumina-scrollbar grid max-h-[420px] gap-3 overflow-y-auto pr-1">
                    @forelse(collect($defaultChildData['teacherFeedbacks'] ?? [])->take(3) as $feedback)
                        @php
                            $feedbackTone = $feedback['tone'] ?? 'good';
                            $tonePalette = match ($feedbackTone) {
                                'bad' => ['bg' => '#FEF2F2', 'border' => '#EF4444', 'text' => '#B91C1C'],
                                default => ['bg' => '#ECFDF3', 'border' => '#10B981', 'text' => '#047857'],
                            };
                        @endphp
                        <article class="rounded-2xl border p-4" data-tone="{{ $feedbackTone }}" style="--feedback-border: {{ $tonePalette['border'] }}; --feedback-bg: {{ $tonePalette['bg'] }}; border-color: var(--feedback-border); border-left-width: 6px; background-color: var(--feedback-bg);">
                            <div class="mb-2 flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-sm font-bold" style="color: var(--lumina-text-primary);">{{ $feedback['teacher'] ?? 'Teacher' }}</h4>
                                    <p class="text-xs" style="color: var(--lumina-text-muted);">{{ $feedback['course'] ?? 'Course' }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ $feedback['messageUrl'] ?? '#' }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full border transition-colors hover:bg-gray-50" style="border-color: var(--lumina-border);" title="Message {{ $feedback['teacher'] ?? 'Teacher' }}">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #EF4444; stroke-width: 2.2;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5l-2 2V6a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H9z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            <p class="text-sm leading-6" style="color: var(--lumina-text-secondary);">{{ $feedback['comment'] ?? 'No feedback provided yet.' }}</p>
                            <p class="mt-2 text-[11px] font-semibold uppercase tracking-wide" style="color: var(--lumina-text-muted);">Updated {{ $feedback['recordedAt'] ?? 'recently' }}</p>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed p-8 text-center" style="border-color: var(--lumina-border); background-color: #FAFCFA;">
                            <p class="text-sm font-semibold" style="color: var(--lumina-text-secondary);">No teacher feedback is available yet for this child.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="relative overflow-hidden rounded-3xl p-8" style="background-color: var(--lumina-dark-green); box-shadow: 0px 20px 25px -5px rgba(2, 44, 34, 0.2), 0px 8px 10px -6px rgba(2, 44, 34, 0.2);">
            <div class="absolute -bottom-10 -right-10 h-40 w-40 rounded-full opacity-50" style="background-color: #065F46; filter: blur(32px);"></div>

            <div class="relative z-10 flex h-full flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl" style="background-color: rgba(6, 95, 70, 0.5);">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                            <line x1="1" y1="10" x2="23" y2="10"/>
                        </svg>
                    </div>
                    <span class="rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-wider text-white" style="background-color: rgba(4, 120, 87, 0.5); border-color: rgba(5, 150, 105, 0.5);">Tuition Due</span>
                </div>

                <div class="mt-10 flex flex-col gap-1">
                    <span class="text-sm font-medium" style="color: #6EE7B7;">Total Outstanding</span>
                    <span class="text-5xl font-black" style="color: #FFFFFF; letter-spacing: -2.4px;">
                        @php
                            $totalAmount = $totalOutstanding ?? 245000;
                            $formattedAmount = number_format($totalAmount, 0, ',', ' ');
                        @endphp
                        {{ $formattedAmount }} <span class="text-2xl">DZD</span>
                    </span>
                </div>

                <div class="mt-7 flex flex-col gap-4">
                    @php
                        $paymentBreakdown = $payments ?? [
                            ['child' => 'Alex', 'term' => 'Term 3', 'amount' => 122500],
                            ['child' => 'Sophie', 'term' => 'Term 3', 'amount' => 122500],
                        ];
                    @endphp

                    @foreach($paymentBreakdown as $payment)
                        <div class="flex items-center justify-between border-b py-2" style="border-color: rgba(6, 95, 70, 0.5);">
                            <span class="text-xs" style="color: #A7F3D0;">{{ $payment['child'] }} - {{ $payment['term'] }}</span>
                            <span class="text-sm font-bold text-white">{{ number_format($payment['amount'], 0, ',', ' ') }} DZD</span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 rounded-xl border px-4 py-3 text-sm font-semibold text-white" style="border-color: rgba(110, 231, 183, 0.35); background-color: rgba(6, 95, 70, 0.35);">
                    Financial summary synced with parent ledger.
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 rounded-3xl border p-6" style="background-color: #FFFFFF; border-color: var(--lumina-border-light);">
        <div class="mb-5 flex items-center justify-between">
            <div class="flex flex-col gap-3">
                <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">Student Progress</h3>
                <div class="inline-flex items-center gap-1 rounded-xl border p-1" id="progressMetricControls" style="border-color: var(--lumina-border); background-color: #F8FBF8;">
                    <button type="button" data-metric="grades" class="progress-metric-btn rounded-lg px-3 py-1.5 text-xs font-bold uppercase tracking-wide">Grades</button>
                    <button type="button" data-metric="attendance" class="progress-metric-btn rounded-lg px-3 py-1.5 text-xs font-bold uppercase tracking-wide">Attendance</button>
                </div>
            </div>
            <div class="flex items-center gap-2" id="progressWindowControls">
                <button type="button" data-week="1" class="progress-week-btn rounded-lg border px-3 py-1 text-xs font-bold">Week 1</button>
                <button type="button" data-week="2" class="progress-week-btn rounded-lg border px-3 py-1 text-xs font-bold">Week 2</button>
                <button type="button" data-week="3" class="progress-week-btn rounded-lg border px-3 py-1 text-xs font-bold">Week 3</button>
                <button type="button" data-week="4" class="progress-week-btn rounded-lg border px-3 py-1 text-xs font-bold">Week 4</button>
            </div>
        </div>

        <div class="mb-3 flex items-center justify-between">
            <div class="text-sm font-semibold" id="progressMetricTitle" style="color: var(--lumina-text-secondary);">Grades Trend</div>
            <span class="rounded-full px-3 py-1 text-xs font-bold" id="progressMetricBadge" style="background-color: #E8F5EE; color: #0E7A4E;">/20 Scale</span>
        </div>

        <div class="overflow-hidden rounded-2xl border p-4" style="border-color: var(--lumina-border); background: linear-gradient(180deg, #f8fbf7 0%, #ffffff 100%);">
            <svg id="progressChart" viewBox="0 0 760 420" class="h-[28rem] w-full" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="progressFill" x1="0" y1="0" x2="0" y2="1">
                        <stop id="progressFillStart" offset="0%" stop-color="#2D8C5E" stop-opacity="0.28" />
                        <stop id="progressFillEnd" offset="100%" stop-color="#2D8C5E" stop-opacity="0.04" />
                    </linearGradient>
                </defs>
                <line x1="40" y1="330" x2="730" y2="330" stroke="#dbe7df" stroke-width="1" />
                <line x1="40" y1="80" x2="730" y2="80" stroke="#ecf3ee" stroke-width="1" />
                <line x1="40" y1="163" x2="730" y2="163" stroke="#ecf3ee" stroke-width="1" />
                <line x1="40" y1="246" x2="730" y2="246" stroke="#ecf3ee" stroke-width="1" />
                <path id="progressArea" d="" fill="url(#progressFill)"></path>
                <polyline id="progressLine" fill="none" stroke="#1c7b50" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></polyline>
                <g id="progressBars"></g>
                <g id="progressDots"></g>
                <g id="progressLabels"></g>
            </svg>
        </div>
    </div>

    <div class="mt-4 grid gap-4 md:grid-cols-3">
        <div class="flex flex-col items-center justify-center rounded-3xl border p-4 text-center" style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border-light); min-height: 138px;">
            <svg class="mb-3 h-10 w-10 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-secondary);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="text-sm font-bold uppercase tracking-wider" style="color: var(--lumina-text-secondary); letter-spacing: 1.2px;">Uploaded Documents</span>
            <span id="documentsCount" class="mt-3 text-2xl font-black" style="color: var(--lumina-primary);">{{ $defaultChildData['documentsCount'] ?? 0 }}</span>
            <span class="text-xs" style="color: var(--lumina-text-muted);">Current child</span>
        </div>

        <div class="flex flex-col items-center justify-center rounded-3xl border p-4 text-center" style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border-light); min-height: 138px;">
            <svg class="mb-3 h-10 w-10 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-secondary);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="text-sm font-bold uppercase tracking-wider" style="color: var(--lumina-text-secondary); letter-spacing: 1.2px;">Teachers of Your Children</span>
            <span id="teachersCount" class="mt-3 text-2xl font-black" style="color: var(--lumina-primary);">{{ $defaultChildData['teachersCount'] ?? 0 }}</span>
            <button id="openTeachersModal" type="button" class="mt-2 text-xs font-semibold hover:underline" style="color: var(--lumina-primary);">View All</button>
        </div>

        <div class="flex flex-col items-center justify-center rounded-3xl border p-4 text-center" style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border-light); min-height: 138px;">
            <svg class="mb-3 h-10 w-10 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-secondary);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span class="text-sm font-bold uppercase tracking-wider" style="color: var(--lumina-text-secondary); letter-spacing: 1.2px;">Unread Messages</span>
            <span id="messagesCount" class="mt-3 text-2xl font-black" style="color: var(--lumina-accent-red);">{{ $defaultChildData['unreadMessagesCount'] ?? 0 }}</span>
            <a href="{{ route('role.messages.index', ['role' => 'parent']) }}" class="mt-2 text-xs font-semibold hover:underline" style="color: var(--lumina-primary);">View Inbox</a>
        </div>
    </div>

    <div id="teachersModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div id="teachersModalBackdrop" class="absolute inset-0 bg-black/45"></div>
        <div class="relative z-10 w-full max-w-xl rounded-2xl border bg-white p-6 shadow-2xl" style="border-color: var(--lumina-border);">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xl font-bold" style="color: var(--lumina-text-primary);">Teachers List</h3>
                <button id="closeTeachersModal" type="button" class="rounded-full p-2 hover:bg-gray-100" aria-label="Close modal">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="teachersModalList" class="max-h-[420px] space-y-2 overflow-y-auto pr-1"></div>
        </div>
    </div>

    <script id="parentChildrenData" type="application/json">@json($childrenData)</script>
    <script id="parentDashboardData" type="application/json">@json($childDashboardData)</script>
    <script id="parentSelectedChildId" type="application/json">@json($selectedChildId)</script>
    <script>
        (function () {
            const childrenData = JSON.parse(document.getElementById('parentChildrenData')?.textContent || '[]');
            const dashboardData = JSON.parse(document.getElementById('parentDashboardData')?.textContent || '{}');
            const initialSelectedChildId = Number(JSON.parse(document.getElementById('parentSelectedChildId')?.textContent || '0'));

            const topSelector = document.getElementById('topChildSelector');
            const teacherFeedbackList = document.getElementById('teacherFeedbackList');
            const documentsCount = document.getElementById('documentsCount');
            const teachersCount = document.getElementById('teachersCount');
            const messagesCount = document.getElementById('messagesCount');

            const openTeachersModal = document.getElementById('openTeachersModal');
            const teachersModal = document.getElementById('teachersModal');
            const teachersModalBackdrop = document.getElementById('teachersModalBackdrop');
            const closeTeachersModal = document.getElementById('closeTeachersModal');
            const teachersModalList = document.getElementById('teachersModalList');

            const progressLine = document.getElementById('progressLine');
            const progressArea = document.getElementById('progressArea');
            const progressBars = document.getElementById('progressBars');
            const progressDots = document.getElementById('progressDots');
            const progressLabels = document.getElementById('progressLabels');
            const progressMetricButtons = document.querySelectorAll('.progress-metric-btn');
            const progressWeekButtons = document.querySelectorAll('.progress-week-btn');
            const feedbackWeekButtons = document.querySelectorAll('.feedback-week-btn');
            const progressMetricTitle = document.getElementById('progressMetricTitle');
            const progressMetricBadge = document.getElementById('progressMetricBadge');
            const progressFillStart = document.getElementById('progressFillStart');
            const progressFillEnd = document.getElementById('progressFillEnd');

            let currentChildId = Number(topSelector?.value || initialSelectedChildId || 0);
            let currentMetric = 'attendance';
            let currentWeek = 4;
            let currentFeedbackWeek = 'latest';

            function getChildData(childId) {
                return dashboardData[String(childId)] || null;
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function renderTeacherFeedbacks(childId) {
                const historyFeedbacks = getChildData(childId)?.teacherFeedbacks || [];
                const feedbacks = currentFeedbackWeek === 'latest'
                    ? historyFeedbacks.slice(0, 3)
                    : historyFeedbacks.filter((feedback) => Number(feedback.week) === Number(currentFeedbackWeek));

                if (!teacherFeedbackList) {
                    return;
                }

                if (!feedbacks.length) {
                    teacherFeedbackList.innerHTML = `
                        <div class="rounded-2xl border border-dashed p-8 text-center" style="border-color: var(--lumina-border); background-color: #FAFCFA;">
                            <p class="text-sm font-semibold" style="color: var(--lumina-text-secondary);">No teacher feedback is available yet for this child.</p>
                        </div>
                    `;
                    return;
                }

                const toneStyles = {
                    good: { bg: '#ECFDF3', border: '#10B981', text: '#047857' },
                    bad: { bg: '#FEF2F2', border: '#EF4444', text: '#B91C1C' },
                };

                teacherFeedbackList.innerHTML = feedbacks.map((feedback) => {
                    const tone = String(feedback.tone || 'good');
                    const toneStyle = toneStyles[tone] || toneStyles.good;

                    return `
                        <article class="rounded-2xl border p-4" style="border-color: ${toneStyle.border}; border-left-width: 6px; background-color: ${toneStyle.bg};">
                            <div class="mb-2 flex items-start justify-between gap-3">
                                <div>
                                    <h4 class="text-base font-bold" style="color: var(--lumina-text-primary);">${escapeHtml(feedback.teacher || 'Teacher')}</h4>
                                    <p class="text-sm" style="color: var(--lumina-text-muted);">${escapeHtml(feedback.course || 'Course')}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="${escapeHtml(feedback.messageUrl || '#')}" class="inline-flex h-11 w-11 items-center justify-center rounded-full border transition-colors hover:bg-gray-50" style="border-color: var(--lumina-border);" title="Message ${escapeHtml(feedback.teacher || 'Teacher')}">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--lumina-primary);">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5l-2 2V6a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H9z"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            <p class="text-base leading-7" style="color: var(--lumina-text-secondary);">${escapeHtml(feedback.comment || 'No feedback provided yet.')}</p>
                            <p class="mt-2 text-xs font-semibold uppercase tracking-wide" style="color: var(--lumina-text-muted);">Updated ${escapeHtml(feedback.recordedAt || 'recently')}</p>
                        </article>
                    `;
                }).join('');
            }

            function updateFeedbackWeekButtons() {
                feedbackWeekButtons.forEach((button) => {
                    const isActive = String(button.dataset.feedbackWeek || 'latest') === String(currentFeedbackWeek);

                    if (isActive) {
                        button.style.background = 'linear-gradient(135deg, #0E7A4E 0%, #0A5E3D 100%)';
                        button.style.color = '#ffffff';
                        button.style.boxShadow = '0 6px 14px rgba(10, 94, 61, 0.2)';
                    } else {
                        button.style.background = 'transparent';
                        button.style.color = 'var(--lumina-text-secondary)';
                        button.style.boxShadow = 'none';
                    }
                });
            }

            function renderSummaryCards(childId) {
                const childData = getChildData(childId);
                if (!childData) return;

                documentsCount.textContent = String(childData.documentsCount ?? 0);
                teachersCount.textContent = String(childData.teachersCount ?? 0);
                messagesCount.textContent = String(childData.unreadMessagesCount ?? 0);
            }

            function renderProgressChart(childId) {
                const childData = getChildData(childId);
                const metricConfig = currentMetric === 'attendance'
                    ? {
                        key: 'attendanceSeries',
                        title: 'Attendance Trend',
                        badge: 'Present / Late / Absent',
                        min: 0,
                        max: 100,
                        color: '#0EA5A4',
                        dotText: '#0F766E',
                        fill: '#14B8A6',
                    }
                    : {
                        key: 'progressSeries',
                        title: 'Grades Trend',
                        badge: '/20 Scale',
                        min: 0,
                        max: 20,
                        color: '#1C7B50',
                        dotText: '#135B3D',
                        fill: '#2D8C5E',
                        formatLabel: (value) => value.toFixed(1),
                    };

                const weekFilteredItems = (childData?.[metricConfig.key] || [])
                    .filter((item) => Number(item.week) <= currentWeek)
                    .sort((a, b) => {
                        const weekDiff = Number(a.week) - Number(b.week);
                        if (weekDiff !== 0) {
                            return weekDiff;
                        }

                        return 0;
                    });

                const rawValues = currentMetric === 'attendance'
                    ? weekFilteredItems.map((item) => Number(item.score)).filter((v) => Number.isFinite(v))
                    : weekFilteredItems.map((item) => Number(item.value)).filter((v) => Number.isFinite(v));

                const attendanceStatuses = currentMetric === 'attendance'
                    ? weekFilteredItems.map((item) => String(item.status || 'unknown').toLowerCase())
                    : [];

                progressMetricTitle.textContent = metricConfig.title;
                progressMetricBadge.textContent = metricConfig.badge;
                progressMetricBadge.style.backgroundColor = currentMetric === 'attendance' ? '#E6FAF8' : '#E8F5EE';
                progressMetricBadge.style.color = currentMetric === 'attendance' ? '#0F766E' : '#0E7A4E';

                if (!rawValues.length) {
                    progressLine.setAttribute('points', '');
                    progressArea.setAttribute('d', '');
                    progressBars.innerHTML = '';
                    progressDots.innerHTML = '';
                    progressLabels.innerHTML = '';
                    return;
                }

                const values = rawValues;
                const chartLeft = 40;
                const chartRight = 730;
                const chartTop = 60;
                const chartBottom = 330;
                const maxValue = metricConfig.max;
                const minValue = metricConfig.min;
                const spanX = chartRight - chartLeft;
                const spanY = chartBottom - chartTop;

                const qualityColorForValue = (value) => {
                    const ratio = (value - minValue) / Math.max(1, (maxValue - minValue));

                    if (ratio < 0.4) {
                        return '#DC2626';
                    }

                    if (ratio < 0.7) {
                        return '#EA580C';
                    }

                    return '#16A34A';
                };

                const avgValue = values.reduce((sum, value) => sum + value, 0) / values.length;
                const attendanceSummary = attendanceStatuses.reduce((acc, status) => {
                    if (status === 'present') acc.present += 1;
                    else if (status === 'late') acc.late += 1;
                    else if (status === 'absent') acc.absent += 1;
                    else acc.unknown += 1;
                    return acc;
                }, { present: 0, late: 0, absent: 0, unknown: 0 });

                const totalAttendance = Math.max(1, attendanceStatuses.length);
                const absentRatio = attendanceSummary.absent / totalAttendance;
                const lateRatio = attendanceSummary.late / totalAttendance;

                const mainColor = currentMetric === 'attendance'
                    ? (absentRatio >= 0.35 ? '#DC2626' : (lateRatio >= 0.35 ? '#EA580C' : '#16A34A'))
                    : qualityColorForValue(avgValue);
                const labelColor = mainColor;

                const attendanceColor = (status) => {
                    if (status === 'present') return '#2F855A';
                    if (status === 'late') return '#B7791F';
                    if (status === 'absent') return '#C53030';

                    return '#64748B';
                };

                progressBars.innerHTML = '';
                progressLine.setAttribute('stroke', mainColor);
                if (progressFillStart) {
                    progressFillStart.setAttribute('stop-color', mainColor);
                }
                if (progressFillEnd) {
                    progressFillEnd.setAttribute('stop-color', mainColor);
                }

                const points = values.map((value, index) => {
                    const x = chartLeft + (index * (spanX / Math.max(1, values.length - 1)));
                    const normalized = Math.max(0, Math.min(1, (value - minValue) / (maxValue - minValue || 1)));
                    const y = chartBottom - (normalized * spanY);
                    return { x, y, value, index };
                });

                if (currentMetric === 'attendance') {
                    progressLine.setAttribute('points', '');
                    progressArea.setAttribute('d', '');
                    progressDots.innerHTML = '';

                    const barSlot = spanX / Math.max(1, values.length);
                    const barWidth = Math.max(18, Math.min(42, barSlot * 0.58));

                    progressBars.innerHTML = points.map((point, index) => {
                        const status = attendanceStatuses[index] || 'unknown';
                        const barColor = attendanceColor(status);
                        const barFill = status === 'present'
                            ? 'rgba(134, 239, 172, 0.35)'
                            : (status === 'late' ? 'rgba(253, 230, 138, 0.35)' : 'rgba(252, 165, 165, 0.35)');
                        const barStroke = status === 'present'
                            ? '#065F46'
                            : (status === 'late' ? '#92400E' : '#991B1B');
                        const barX = point.x - (barWidth / 2);
                        const barHeight = Math.max(8, chartBottom - point.y);
                        const iconY = Math.max(chartTop + 14, point.y + 14);
                        const iconMarkup = status === 'present'
                            ? `<path d="M ${point.x - 4} ${iconY} L ${point.x - 1} ${iconY + 3} L ${point.x + 5} ${iconY - 4}" stroke="#1F3A34" stroke-width="2.2" fill="none" stroke-linecap="round" stroke-linejoin="round"></path>`
                            : (status === 'late'
                                ? `<circle cx="${point.x}" cy="${iconY}" r="4.3" fill="none" stroke="#78350F" stroke-width="1.8"></circle><path d="M ${point.x} ${iconY - 2} L ${point.x} ${iconY + 0.5} L ${point.x + 2} ${iconY + 2}" stroke="#78350F" stroke-width="1.7" fill="none" stroke-linecap="round" stroke-linejoin="round"></path>`
                                : `<path d="M ${point.x - 3.5} ${iconY - 3.5} L ${point.x + 3.5} ${iconY + 3.5}" stroke="#7F1D1D" stroke-width="2.2" stroke-linecap="round"></path><path d="M ${point.x + 3.5} ${iconY - 3.5} L ${point.x - 3.5} ${iconY + 3.5}" stroke="#7F1D1D" stroke-width="2.2" stroke-linecap="round"></path>`);

                        return `
                            <g>
                                <path d="M ${barX} ${chartBottom} L ${barX} ${point.y + 8} Q ${barX} ${point.y} ${barX + 8} ${point.y} L ${barX + barWidth - 8} ${point.y} Q ${barX + barWidth} ${point.y} ${barX + barWidth} ${point.y + 8} L ${barX + barWidth} ${chartBottom} Z" fill="${barFill}" stroke="${barStroke}" stroke-width="1.4" opacity="0.98"></path>
                                ${iconMarkup}
                            </g>
                        `;
                    }).join('');

                    progressLabels.innerHTML = points.map((point, index) => `
                        <text x="${point.x}" y="378" text-anchor="middle" font-size="8" fill="#64748b">S${index + 1}</text>
                    `).join('');

                    return;
                }

                const linePoints = points.map((p) => `${p.x},${p.y}`).join(' ');
                progressLine.setAttribute('points', linePoints);

                const areaPath = [
                    `M ${points[0].x} ${chartBottom}`,
                    ...points.map((p) => `L ${p.x} ${p.y}`),
                    `L ${points[points.length - 1].x} ${chartBottom}`,
                    'Z',
                ].join(' ');
                progressArea.setAttribute('d', areaPath);

                const attendanceIconPath = (status, x, y) => {
                    if (status === 'present') {
                        return `<path d="M ${x - 3} ${y} L ${x - 1} ${y + 2} L ${x + 3} ${y - 3}" stroke="#ffffff" stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round"></path>`;
                    }

                    if (status === 'late') {
                        return `
                            <circle cx="${x}" cy="${y}" r="3.3" fill="none" stroke="#ffffff" stroke-width="1.5"></circle>
                            <path d="M ${x} ${y - 1.6} L ${x} ${y} L ${x + 1.8} ${y + 1}" stroke="#ffffff" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"></path>
                        `;
                    }

                    if (status === 'absent') {
                        return `
                            <path d="M ${x - 2.5} ${y - 2.5} L ${x + 2.5} ${y + 2.5}" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round"></path>
                            <path d="M ${x + 2.5} ${y - 2.5} L ${x - 2.5} ${y + 2.5}" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round"></path>
                        `;
                    }

                    return '<circle cx="' + x + '" cy="' + y + '" r="1.8" fill="#ffffff"></circle>';
                };

                progressDots.innerHTML = points.map((p) => `
                    <g>
                        <circle cx="${p.x}" cy="${p.y}" r="7" fill="${qualityColorForValue(p.value)}"></circle>
                        <circle cx="${p.x}" cy="${p.y}" r="3" fill="#ffffff"></circle>
                        <text x="${p.x}" y="${p.y - 12}" text-anchor="middle" font-size="11" fill="${labelColor}" font-weight="700">${metricConfig.formatLabel(p.value)}</text>
                    </g>
                `).join('');

                progressLabels.innerHTML = points.map((p, index) => `
                    <text x="${p.x}" y="378" text-anchor="middle" font-size="8" fill="#64748b">S${index + 1}</text>
                `).join('');
            }

            function renderTeachersModalList(childId) {
                const teachers = getChildData(childId)?.teachers || [];
                teachersModalList.innerHTML = teachers.map((teacher) => `
                    <div class="flex items-center justify-between rounded-xl border px-3 py-3" style="border-color: var(--lumina-border);">
                        <div>
                            <p class="text-sm font-bold" style="color: var(--lumina-text-primary);">${teacher.name}</p>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">${teacher.group}</p>
                        </div>
                        <a href="${teacher.messageUrl}" class="inline-flex h-9 w-9 items-center justify-center rounded-full border transition-colors hover:bg-gray-50" style="border-color: var(--lumina-border);" title="Message ${teacher.name}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--lumina-primary);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5l-2 2V6a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H9z"/>
                            </svg>
                        </a>
                    </div>
                `).join('');
            }

            function renderAll(childId) {
                renderTeacherFeedbacks(childId);
                renderSummaryCards(childId);
                renderProgressChart(childId);
                renderTeachersModalList(childId);
            }

            function updateMetricButtons() {
                progressMetricButtons.forEach((button) => {
                    const isActive = button.dataset.metric === currentMetric;
                    if (isActive) {
                        button.style.background = 'linear-gradient(135deg, #0E7A4E 0%, #0A5E3D 100%)';
                        button.style.color = '#ffffff';
                        button.style.boxShadow = '0 6px 14px rgba(10, 94, 61, 0.2)';
                    } else {
                        button.style.background = 'transparent';
                        button.style.color = 'var(--lumina-text-secondary)';
                        button.style.boxShadow = 'none';
                    }
                });
            }

            function updateWeekButtons() {
                progressWeekButtons.forEach((button) => {
                    const isActive = Number(button.dataset.week) === currentWeek;

                    if (isActive) {
                        button.style.borderColor = 'var(--lumina-primary)';
                        button.style.backgroundColor = 'var(--lumina-primary)';
                        button.style.color = '#ffffff';
                    } else {
                        button.style.borderColor = 'var(--lumina-border)';
                        button.style.backgroundColor = '#ffffff';
                        button.style.color = 'var(--lumina-text-secondary)';
                    }
                });
            }

            function showTeachersModal() {
                teachersModal.classList.remove('hidden');
                teachersModal.classList.add('flex');
            }

            function hideTeachersModal() {
                teachersModal.classList.add('hidden');
                teachersModal.classList.remove('flex');
            }

            function syncSelectionAndRender(newChildId) {
                currentChildId = Number(newChildId || 0);

                if (topSelector) {
                    topSelector.value = String(currentChildId);
                }

                renderAll(currentChildId);
            }

            if (topSelector) {
                topSelector.addEventListener('change', function () {
                    syncSelectionAndRender(this.value);
                });
            }

            progressMetricButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    currentMetric = this.dataset.metric || 'grades';
                    updateMetricButtons();
                    renderProgressChart(currentChildId);
                });
            });

            progressWeekButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    currentWeek = Number(this.dataset.week || 4);
                    updateWeekButtons();
                    renderProgressChart(currentChildId);
                });
            });

            feedbackWeekButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    currentFeedbackWeek = this.dataset.feedbackWeek || 'latest';
                    updateFeedbackWeekButtons();
                    renderTeacherFeedbacks(currentChildId);
                });
            });

            if (openTeachersModal) {
                openTeachersModal.addEventListener('click', showTeachersModal);
            }

            [teachersModalBackdrop, closeTeachersModal].forEach((node) => {
                if (node) {
                    node.addEventListener('click', hideTeachersModal);
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    hideTeachersModal();
                }
            });

            updateMetricButtons();
            updateWeekButtons();
            updateFeedbackWeekButtons();
            syncSelectionAndRender(currentChildId);
        })();
    </script>
</x-layouts.parent>
