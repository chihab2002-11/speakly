<x-layouts.parent 
    :title="'Settings'"
    :pageTitle="'Settings'"
    :currentRoute="'settings'"
    :user="$user ?? null"
    :children="$children ?? []"
>
    @if(session('success'))
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm font-semibold" style="border-color: #86efac; background-color: #f0fdf4; color: #166534;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
            Account Settings
        </h1>
        <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
            Manage your personal information and security settings.
        </p>
    </div>

    {{-- Main Grid Layout --}}
    <div class="grid gap-6 lg:grid-cols-2">
        <div class="flex flex-col gap-6">
            <div 
                class="flex flex-col items-center rounded-3xl border p-8"
                style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;"
            >
                <div class="mb-4">
                    <div 
                        class="flex h-32 w-32 items-center justify-center overflow-hidden rounded-full"
                        style="background: linear-gradient(135deg, var(--lumina-primary) 0%, var(--lumina-primary-light) 100%);"
                    >
                        @if($user->avatar ?? false)
                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-4xl font-bold text-white">
                                {{ $user ? strtoupper(substr($user->name ?? 'S', 0, 1)) : 'S' }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Parent Name --}}
                <h2 class="text-xl font-bold" style="color: var(--lumina-text-primary); font-family: 'Young Serif', Georgia, serif;">
                    {{ $user->name ?? 'Sarah Henderson' }}
                </h2>
                <p class="text-sm" style="color: var(--lumina-text-muted);">
                    Parent Account
                </p>
            </div>

            {{-- Linked Children Card --}}
            <div 
                class="flex flex-col rounded-3xl border p-8"
                style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;"
            >
                {{-- Header --}}
                <div class="mb-4 flex items-center gap-2">
                    <svg class="h-5 w-5" fill="currentColor" style="color: var(--lumina-primary);" viewBox="0 0 24 24">
                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                    </svg>
                    <h3 class="text-base font-bold" style="color: var(--lumina-text-primary);">
                        Linked Children
                    </h3>
                </div>

                {{-- Children List --}}
                <div class="flex flex-col gap-3">
                    @php
                        $linkedChildren = $children ?? [
                            ['id' => 1, 'name' => 'Alex Johnson', 'initials' => 'A', 'grade' => 'Grade 10', 'color' => 'var(--lumina-child-1)', 'textColor' => 'var(--lumina-child-1-text)'],
                            ['id' => 2, 'name' => 'Sophie Johnson', 'initials' => 'S', 'grade' => 'Grade 8', 'color' => 'var(--lumina-child-2)', 'textColor' => 'var(--lumina-child-2-text)'],
                        ];
                    @endphp

                    @foreach($linkedChildren as $child)
                        <div 
                            class="flex items-center justify-between rounded-xl border p-4"
                            style="border-color: var(--lumina-border);"
                        >
                            <div class="flex items-center gap-3">
                                <div 
                                    class="flex h-10 w-10 items-center justify-center rounded-xl"
                                    style="--child-bg: {{ $child['color'] }}; --child-text: {{ $child['textColor'] }}; background-color: var(--child-bg);"
                                >
                                    <span class="text-sm font-bold" style="color: var(--child-text);">
                                        {{ $child['initials'] }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm font-bold" style="color: var(--lumina-text-primary);">
                                        {{ $child['name'] }}
                                    </p>
                                    <p class="text-xs" style="color: var(--lumina-text-muted);">
                                        {{ $child['grade'] }}
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                class="js-open-child-profile text-xs font-bold transition-opacity hover:opacity-80"
                                style="color: var(--lumina-primary);"
                                data-child='@json($child, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)'
                            >
                                View Profile
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div id="childProfileModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
                <div id="childProfileBackdrop" class="absolute inset-0 bg-black/45"></div>
                <div class="relative z-10 w-full max-w-2xl rounded-2xl border bg-white p-6 shadow-2xl" style="border-color: var(--lumina-border);">
                    <div class="mb-5 flex items-center justify-between">
                        <h3 class="text-xl font-bold" style="color: var(--lumina-text-primary);">Child Account Profile</h3>
                        <button id="closeChildProfileModal" type="button" class="rounded-full p-2 hover:bg-gray-100" aria-label="Close profile modal">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold uppercase tracking-wide" style="color: var(--lumina-text-muted);">Full Name</label>
                            <input id="childProfileName" type="text" readonly class="rounded-xl border px-4 py-2.5 text-sm" style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary);">
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold uppercase tracking-wide" style="color: var(--lumina-text-muted);">Role</label>
                            <input id="childProfileRole" type="text" readonly class="rounded-xl border px-4 py-2.5 text-sm" style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary);">
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold uppercase tracking-wide" style="color: var(--lumina-text-muted);">Email</label>
                            <input id="childProfileEmail" type="text" readonly class="rounded-xl border px-4 py-2.5 text-sm" style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary);">
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold uppercase tracking-wide" style="color: var(--lumina-text-muted);">Phone</label>
                            <input id="childProfilePhone" type="text" readonly class="rounded-xl border px-4 py-2.5 text-sm" style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary);">
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold uppercase tracking-wide" style="color: var(--lumina-text-muted);">Date of Birth</label>
                            <input id="childProfileDob" type="text" readonly class="rounded-xl border px-4 py-2.5 text-sm" style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary);">
                        </div>

                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-semibold uppercase tracking-wide" style="color: var(--lumina-text-muted);">Member Since</label>
                            <input id="childProfileSince" type="text" readonly class="rounded-xl border px-4 py-2.5 text-sm" style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary);">
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="flex flex-col gap-6">
            {{-- Personal Details Card --}}
            <div 
                class="flex flex-col rounded-3xl border p-8"
                style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;"
            >
                {{-- Header --}}
                <div class="mb-6 flex items-center gap-2">
                    <svg class="h-5 w-5" fill="currentColor" style="color: var(--lumina-primary);" viewBox="0 0 24 24">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                    <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                        Personal Details
                    </h3>
                </div>

                {{-- Form Fields --}}
                <form method="POST" action="{{ route('parent.settings.update') }}" class="flex flex-col gap-5">
                    @csrf
                    <div class="grid gap-5 sm:grid-cols-2">
                        {{-- Full Name --}}
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Full Name</label>
                            <input 
                                name="name"
                                type="text" 
                                value="{{ $user->name ?? 'Sarah Henderson' }}"
                                class="rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2"
                                style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);"
                            >
                        </div>

                        {{-- Email Address --}}
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Email Address</label>
                            <input 
                                name="email"
                                type="email" 
                                value="{{ $user->email ?? 'sarah.henderson@email.com' }}"
                                class="rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2"
                                style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);"
                            >
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        {{-- Phone Number --}}
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Phone Number</label>
                            <input 
                                name="phone"
                                type="tel" 
                                value="{{ $user->phone ?? '+213 555 123 456' }}"
                                class="rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2"
                                style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);"
                            >
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Bio</label>
                        <textarea
                            name="bio"
                            class="rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2"
                            style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);"
                            rows="3"
                        >{{ $user->bio ?? '' }}</textarea>
                    </div>

                    {{-- Save Button --}}
                    <button 
                        type="submit"
                        class="mt-2 w-fit rounded-xl px-8 py-3 text-sm font-bold text-white transition-all hover:opacity-90 cursor-pointer"
                        style="background-color: var(--lumina-primary);"
                    >
                        Save Changes
                    </button>
                </form>
            </div>

            {{-- Security Card --}}
            <div 
                class="flex flex-col rounded-3xl border p-8"
                style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;"
            >
                {{-- Header --}}
                <div class="mb-6 flex items-center gap-2">
                    <svg class="h-5 w-5" fill="currentColor" style="color: var(--lumina-primary);" viewBox="0 0 24 24">
                        <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                    </svg>
                    <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                        Security
                    </h3>
                </div>

                {{-- Password Reset --}}
                <div 
                    class="mb-4 flex items-center justify-between rounded-xl border p-4"
                    style="border-color: var(--lumina-border);"
                >
                    <div class="flex items-center gap-3">
                        <div 
                            class="flex h-10 w-10 items-center justify-center rounded-full"
                            style="background-color: var(--lumina-bg-card);"
                        >
                            <svg class="h-5 w-5" fill="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold" style="color: var(--lumina-text-primary);">Change Password</p>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">Last changed {{ $passwordLastChanged ?? '30 days ago' }}</p>
                        </div>
                    </div>
                    <a 
                        href="{{ route('parent.password') }}"
                        class="text-sm font-bold cursor-pointer transition-opacity hover:opacity-80"
                        style="color: var(--lumina-primary);"
                        wire:navigate
                    >
                        Update
                    </a>
                </div>

                {{-- Two-Factor Authentication --}}
                <div 
                    class="flex items-center justify-between rounded-xl border p-4"
                    style="border-color: var(--lumina-border);"
                >
                    <div class="flex items-center gap-3">
                        <div 
                            class="flex h-10 w-10 items-center justify-center rounded-full"
                            style="background-color: var(--lumina-bg-card);"
                        >
                            <svg class="h-5 w-5" fill="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold" style="color: var(--lumina-text-primary);">Two-Factor Authentication</p>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">Add extra security to your account</p>
                        </div>
                    </div>
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" class="peer sr-only" {{ ($twoFactorEnabled ?? false) ? 'checked' : '' }}>
                        <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-[var(--lumina-primary)] peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none"></div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('childProfileModal');
            const backdrop = document.getElementById('childProfileBackdrop');
            const closeButton = document.getElementById('closeChildProfileModal');
            const triggers = document.querySelectorAll('.js-open-child-profile');

            const fields = {
                name: document.getElementById('childProfileName'),
                role: document.getElementById('childProfileRole'),
                email: document.getElementById('childProfileEmail'),
                phone: document.getElementById('childProfilePhone'),
                dob: document.getElementById('childProfileDob'),
                since: document.getElementById('childProfileSince'),
            };

            const fallback = (value, empty = 'Not provided') => String(value || '').trim() || empty;

            const hideModal = () => {
                if (!modal) return;
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            };

            const showModal = (child) => {
                if (!modal) return;

                fields.name.value = fallback(child.name, 'Unknown child');
                fields.role.value = fallback(child.grade, 'Student');
                fields.email.value = fallback(child.email);
                fields.phone.value = fallback(child.phone);
                fields.dob.value = fallback(child.dateOfBirth);
                fields.since.value = fallback(child.memberSince);

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };

            triggers.forEach((button) => {
                button.addEventListener('click', function () {
                    const payload = this.getAttribute('data-child') || '{}';

                    try {
                        showModal(JSON.parse(payload));
                    } catch (error) {
                        showModal({});
                    }
                });
            });

            [backdrop, closeButton].forEach((node) => {
                if (node) {
                    node.addEventListener('click', hideModal);
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    hideModal();
                }
            });
        })();
    </script>
</x-layouts.parent>
