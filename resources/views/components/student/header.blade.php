@props([
    'user' => null
])

@php
    $user = $user ?? auth()->user();
    
    // Get user's actual role
    $userRole = 'Student'; // Default
    try {
        if ($user->hasRole('admin')) {
            $userRole = 'Admin';
        } elseif ($user->hasRole('teacher')) {
            $userRole = 'Teacher';
        } elseif ($user->hasRole('parent')) {
            $userRole = 'Parent';
        } elseif ($user->hasRole('secretary')) {
            $userRole = 'Secretary';
        } elseif ($user->hasRole('student')) {
            $userRole = 'Student';
        }
    } catch (\Exception $e) {
        // Fallback to requested_role
        $userRole = ucfirst($user->requested_role ?? 'Student');
    }
    
    // Get unread notifications count
    $unreadNotificationsCount = $user ? $user->unreadNotifications()->count() : 0;

    $assignedGroups = collect();

    if ($user && method_exists($user, 'enrolledClasses')) {
        $assignedGroups = $user->enrolledClasses()
            ->with('course:id,name')
            ->get()
            ->map(function ($class): array {
                $label = (string) ($class->course?->name ?: ('Class #'.$class->id));

                return [
                    'id' => (int) $class->id,
                    'label' => $label,
                ];
            })
            ->unique('id')
            ->values();
    }

    $singleAssignedGroup = $assignedGroups->count() === 1 ? $assignedGroups->first() : null;
@endphp

<header 
    class="sticky top-0 z-30 flex items-center justify-between border-b px-4 py-3 md:px-8"
    style="background: rgba(255, 255, 255, 0.8); border-color: rgba(226, 232, 240, 0.5); backdrop-filter: blur(12px); box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);"
>
    {{-- Left Side: Mobile Menu Toggle --}}
    <div class="flex items-center gap-4">
        {{-- Mobile Menu Button --}}
        <button 
            onclick="toggleSidebar()"
            class="flex h-9 w-8 items-center justify-center rounded-full lg:hidden"
            aria-label="Toggle sidebar"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
    </div>

    {{-- Right Side: Actions & User Profile --}}
    <div class="flex items-center gap-4 md:gap-6">
        {{-- Icon Buttons --}}
        <div class="flex items-center gap-2 md:gap-4">
            {{-- Notifications Button --}}
            <a href="{{ route('student.notifications') }}" class="relative flex h-9 w-9 items-center justify-center rounded-full transition-colors hover:bg-gray-100 cursor-pointer" data-live-notification-bell>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span data-live-notification-count class="{{ $unreadNotificationsCount > 0 ? '' : 'hidden' }} absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
                    {{ $unreadNotificationsCount > 9 ? '9+' : $unreadNotificationsCount }}
                </span>
            </a>

            {{-- Submit Review Button --}}
            <button
                type="button"
                id="openStudentReviewModal"
                class="flex h-9 w-9 items-center justify-center rounded-full transition-colors hover:bg-gray-100 cursor-pointer"
                title="Submit Review"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.295 3.985a1 1 0 00.95.69h4.19c.969 0 1.371 1.24.588 1.81l-3.39 2.463a1 1 0 00-.364 1.118l1.295 3.985c.3.921-.755 1.688-1.538 1.118l-3.39-2.463a1 1 0 00-1.176 0l-3.39 2.463c-.783.57-1.838-.197-1.539-1.118l1.296-3.985a1 1 0 00-.364-1.118L2.93 9.412c-.783-.57-.38-1.81.588-1.81h4.19a1 1 0 00.95-.69l1.391-3.985z"/>
                </svg>
            </button>

            {{-- Help Button --}}
            <a href="{{ route('support') }}" class="flex h-9 w-9 items-center justify-center rounded-full transition-colors hover:bg-gray-100 cursor-pointer" aria-label="Support" wire:navigate>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </a>
            
            {{-- Settings Button --}}
            <a 
                href="{{ route('student.settings') }}" 
                class="flex h-9 w-9 items-center justify-center rounded-full transition-colors hover:bg-gray-100 cursor-pointer"
                wire:navigate
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </a>
        </div>

        {{-- Vertical Divider --}}
        <div class="hidden h-8 w-px md:block" style="background-color: var(--lumina-border);"></div>

        {{-- User Profile --}}
        <div class="flex items-center gap-3">
            {{-- User Info (hidden on mobile) --}}
            <div class="hidden flex-col items-end md:flex">
                <span class="text-xs font-medium" style="color: #0F172A;">
                    {{ $user->name ?? 'Alex Thompson' }}
                </span>
                <span class="text-[10px] font-medium" style="color: var(--lumina-text-muted);">
                    {{ $userRole }}
                </span>
            </div>
            
            {{-- Avatar --}}
            <div class="h-10 w-10 overflow-hidden rounded-full border" style="border-color: var(--lumina-border);">
                @if($user && $user->avatar)
                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                @else
                    <div class="flex h-full w-full items-center justify-center text-sm font-semibold" style="background-color: var(--lumina-bg-card); color: var(--lumina-primary);">
                        {{ $user ? $user->initials() : 'AT' }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</header>

<div id="studentReviewModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4" data-store-url="{{ route('student.reviews.store') }}">
    <div id="studentReviewBackdrop" class="absolute inset-0 bg-black/45"></div>

    <div class="relative z-10 w-full max-w-xl rounded-2xl border bg-white p-6 shadow-2xl" style="border-color: var(--lumina-border);">
        <div class="mb-4 flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold" style="color: var(--lumina-text-primary);">Add Your Review</h2>
                <p class="mt-1 text-sm" style="color: var(--lumina-text-secondary);">Share your learning experience for visitors.</p>
            </div>
            <button type="button" id="closeStudentReviewModal" class="rounded-full p-2 hover:bg-gray-100" aria-label="Close">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div id="studentReviewAlert" class="mb-4 hidden rounded-lg px-3 py-2 text-sm"></div>

        <form id="studentReviewForm" class="space-y-4">
            @csrf

            @if($assignedGroups->isEmpty())
                <div class="rounded-lg border px-3 py-2 text-sm" style="border-color: #fecaca; background-color: #fef2f2; color: #991b1b;">
                    You are not assigned to any group yet, so review submission is currently unavailable.
                </div>
            @else
                @if($singleAssignedGroup)
                    <input type="hidden" name="class_id" value="{{ $singleAssignedGroup['id'] }}">
                    <div>
                        <p class="mb-1 text-sm font-semibold" style="color: var(--lumina-text-primary);">Group</p>
                        <p class="rounded-xl border px-3 py-2 text-sm" style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);">
                            {{ $singleAssignedGroup['label'] }}
                        </p>
                    </div>
                @else
                    <div>
                        <label for="reviewClassId" class="mb-1 block text-sm font-semibold" style="color: var(--lumina-text-primary);">Select Group</label>
                        <select id="reviewClassId" name="class_id" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color: var(--lumina-border);" required>
                            <option value="">Choose your group</option>
                            @foreach($assignedGroups as $group)
                                <option value="{{ $group['id'] }}">{{ $group['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label for="reviewTextInput" class="mb-1 block text-sm font-semibold" style="color: var(--lumina-text-primary);">Review</label>
                    <textarea id="reviewTextInput" name="review_text" rows="5" maxlength="1200" class="w-full rounded-xl border px-3 py-2 text-sm" style="border-color: var(--lumina-border);" placeholder="Write your review..." required></textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" id="cancelStudentReviewModal" class="rounded-xl border px-4 py-2 text-sm font-semibold" style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);">Cancel</button>
                    <button type="submit" class="rounded-xl px-4 py-2 text-sm font-semibold text-white" style="background-color: var(--lumina-primary);">Submit Review</button>
                </div>
            @endif
        </form>
    </div>
</div>

<script>
    (function () {
        const openBtn = document.getElementById('openStudentReviewModal');
        const modal = document.getElementById('studentReviewModal');
        const backdrop = document.getElementById('studentReviewBackdrop');
        const closeBtn = document.getElementById('closeStudentReviewModal');
        const cancelBtn = document.getElementById('cancelStudentReviewModal');
        const form = document.getElementById('studentReviewForm');
        const alertBox = document.getElementById('studentReviewAlert');
        const storeUrl = modal.dataset.storeUrl || '';

        if (!openBtn || !modal || !form) {
            return;
        }

        const hasGroupWarning = form.querySelector('input[name="class_id"], select[name="class_id"]');

        function showModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function hideModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function showAlert(message, type) {
            if (!alertBox) {
                return;
            }

            alertBox.textContent = message;
            alertBox.classList.remove('hidden');

            if (type === 'success') {
                alertBox.style.backgroundColor = '#ecfdf3';
                alertBox.style.color = '#166534';
                alertBox.style.border = '1px solid #99f6bf';
            } else {
                alertBox.style.backgroundColor = '#fef2f2';
                alertBox.style.color = '#991b1b';
                alertBox.style.border = '1px solid #fecaca';
            }
        }

        openBtn.addEventListener('click', function () {
            showModal();
        });

        [backdrop, closeBtn, cancelBtn].forEach(function (el) {
            if (!el) {
                return;
            }

            el.addEventListener('click', function () {
                hideModal();
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                hideModal();
            }
        });

        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (!hasGroupWarning) {
                showAlert('You must be assigned to at least one group to submit a review.', 'error');
                return;
            }

            const csrf = form.querySelector('input[name="_token"]')?.value || '';
            const formData = new FormData(form);

            try {
                const response = await fetch(storeUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const result = await response.json();

                if (!response.ok) {
                    showAlert(result.message || 'Unable to submit your review.', 'error');
                    return;
                }

                form.reset();
                showAlert(result.message || 'Review submitted successfully.', 'success');

                setTimeout(function () {
                    hideModal();
                }, 700);
            } catch (error) {
                showAlert('Unexpected error while submitting review.', 'error');
            }
        });
    })();
</script>
