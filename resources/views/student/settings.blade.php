<x-layouts.student :title="__('Account Settings')" :currentRoute="'settings'">
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
            Account Settings
        </h1>
        <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
            Manage your personal information, security preferences, and academic progress details.
        </p>
    </div>

    {{-- Main Grid Layout --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Left Column --}}
        <div class="flex flex-col gap-6">
            {{-- Profile Photo Card --}}
            <div 
                class="flex flex-col items-center rounded-3xl border p-8"
                style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;"
            >
                {{-- Profile Photo --}}
                <div class="mb-4">
                    <div 
                        class="flex h-32 w-32 items-center justify-center overflow-hidden rounded-full"
                        style="background: linear-gradient(135deg, var(--lumina-primary) 0%, var(--lumina-primary-light) 100%);"
                    >
                        @if($user->avatar ?? false)
                            <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                        @else
                            <span class="text-4xl font-bold text-white">
                                {{ $user ? $user->initials() : 'AT' }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Student Name & ID --}}
                <h2 class="text-xl font-bold" style="color: var(--lumina-text-primary); font-family: 'Young Serif', Georgia, serif;">
                    {{ $user->name ?? 'Alex Thompson' }}
                </h2>
                <p class="text-sm" style="color: var(--lumina-text-muted);">
                    Student ID: {{ $studentId ?? 'LUM-2024-8891' }}
                </p>

                {{-- Upload Button --}}
                <button 
                    class="mt-6 rounded-xl px-6 py-3 text-sm font-bold text-white transition-all hover:opacity-90 cursor-pointer"
                    style="background-color: var(--lumina-primary);"
                >
                    Upload New Photo
                </button>
                <p class="mt-2 text-xs" style="color: var(--lumina-text-muted);">
                    JPG, GIF or PNG. Max size of 800K
                </p>
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
                <form class="flex flex-col gap-5">
                    <div class="grid gap-5 sm:grid-cols-2">
                        {{-- Full Name --}}
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Full Name</label>
                            <input 
                                type="text" 
                                value="{{ $user->name ?? 'Alex Thompson' }}"
                                class="rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2"
                                style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);"
                            >
                        </div>

                        {{-- Email Address --}}
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Email Address</label>
                            <input 
                                type="email" 
                                value="{{ $user->email ?? 'alex.thompson@lumina.edu' }}"
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
                                type="tel" 
                                value="{{ $user->phone ?? '+1 (555) 012-3456' }}"
                                class="rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2"
                                style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);"
                            >
                        </div>

                        {{-- Primary Language --}}
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Primary Language</label>
                            <div class="relative">
                                <select 
                                    class="w-full appearance-none rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2 cursor-pointer"
                                    style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);"
                                >
                                    <option value="english" selected>English</option>
                                    <option value="french">French</option>
                                    <option value="spanish">Spanish</option>
                                    <option value="german">German</option>
                                    <option value="arabic">Arabic</option>
                                </select>
                                <svg class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2" fill="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                                    <path d="M7 10l5 5 5-5z"/>
                                </svg>
                            </div>
                        </div>
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

            {{-- Security & Privacy Card --}}
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
                        Security & Privacy
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
                                <path d="M17.65 6.35C16.2 4.9 14.21 4 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08c-.82 2.33-3.04 4-5.65 4-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold" style="color: var(--lumina-text-primary);">Password Reset</p>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">Last changed {{ $passwordLastChanged ?? '3 months ago' }}</p>
                        </div>
                    </div>
                    <a 
                        href="{{ route('student.password') }}" 
                        class="text-sm font-bold cursor-pointer transition-opacity hover:opacity-80"
                        style="color: var(--lumina-primary);"
                        wire:navigate
                    >
                        Change Password
                    </a>
                </div>

                {{-- Two-Factor Authentication --}}
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
                                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold" style="color: var(--lumina-text-primary);">Two-Factor Authentication</p>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">Add an extra layer of security to your account</p>
                        </div>
                    </div>
                    {{-- Toggle Switch --}}
                    <label class="relative inline-flex cursor-pointer items-center">
                        <input type="checkbox" class="peer sr-only" {{ ($twoFactorEnabled ?? false) ? 'checked' : '' }}>
                        <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-[var(--lumina-primary)] peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none"></div>
                    </label>
                </div>

                {{-- Active Sessions & Login History --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    {{-- Active Sessions --}}
                    <div 
                        class="flex flex-col gap-2 rounded-xl border p-4 transition-colors hover:bg-gray-50 cursor-pointer"
                        style="border-color: var(--lumina-border);"
                    >
                        <svg class="h-5 w-5" fill="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                            <path d="M4 6h18V4H4c-1.1 0-2 .9-2 2v11H0v3h14v-3H4V6zm19 2h-6c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h6c.55 0 1-.45 1-1V9c0-.55-.45-1-1-1zm-1 9h-4v-7h4v7z"/>
                        </svg>
                        <p class="text-sm font-bold" style="color: var(--lumina-text-primary);">Active Sessions</p>
                        <p class="text-xs" style="color: var(--lumina-text-muted);">Manage devices currently logged into your account.</p>
                    </div>

                    {{-- Login History --}}
                    <div 
                        class="flex flex-col gap-2 rounded-xl border p-4 transition-colors hover:bg-gray-50 cursor-pointer"
                        style="border-color: var(--lumina-border);"
                    >
                        <svg class="h-5 w-5" fill="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                            <path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/>
                        </svg>
                        <p class="text-sm font-bold" style="color: var(--lumina-text-primary);">Login History</p>
                        <p class="text-xs" style="color: var(--lumina-text-muted);">View your account activity and login attempts.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.student>
