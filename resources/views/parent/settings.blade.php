<x-layouts.parent 
    :title="'Settings'"
    :pageTitle="'Settings'"
    :currentRoute="'settings'"
    :user="$user ?? null"
>
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
            Account Settings
        </h1>
        <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
            Manage your personal information, notification preferences, and security settings.
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
                                    style="background-color: {{ $child['color'] }};"
                                >
                                    <span class="text-sm font-bold" style="color: {{ $child['textColor'] }};">
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
                            <a 
                                href="#" 
                                class="text-xs font-bold transition-opacity hover:opacity-80"
                                style="color: var(--lumina-primary);"
                            >
                                View Profile
                            </a>
                        </div>
                    @endforeach
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
                <form class="flex flex-col gap-5">
                    <div class="grid gap-5 sm:grid-cols-2">
                        {{-- Full Name --}}
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Full Name</label>
                            <input 
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
                                type="tel" 
                                value="{{ $user->phone ?? '+213 555 123 456' }}"
                                class="rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2"
                                style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);"
                            >
                        </div>

                        {{-- Preferred Language --}}
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Preferred Language</label>
                            <div class="relative">
                                <select 
                                    class="w-full appearance-none rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2 cursor-pointer"
                                    style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);"
                                >
                                    <option value="english" selected>English</option>
                                    <option value="french">French</option>
                                    <option value="arabic">Arabic</option>
                                </select>
                                <svg class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2" fill="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                                    <path d="M7 10l5 5 5-5z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Address --}}
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Address</label>
                        <input 
                            type="text" 
                            value="{{ $user->address ?? '123 Rue des Martyrs, Algiers, Algeria' }}"
                            class="rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2"
                            style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);"
                        >
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

            {{-- Notification Preferences Card --}}
            <div 
                class="flex flex-col rounded-3xl border p-8"
                style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;"
            >
                {{-- Header --}}
                <div class="mb-6 flex items-center gap-2">
                    <svg class="h-5 w-5" fill="currentColor" style="color: var(--lumina-primary);" viewBox="0 0 24 24">
                        <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z"/>
                    </svg>
                    <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                        Notification Preferences
                    </h3>
                </div>

                {{-- Notification Options --}}
                <div class="flex flex-col gap-4">
                    {{-- Email Notifications --}}
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
                                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold" style="color: var(--lumina-text-primary);">Email Notifications</p>
                                <p class="text-xs" style="color: var(--lumina-text-muted);">Receive updates about grades and events</p>
                            </div>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" class="peer sr-only" checked>
                            <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-[var(--lumina-primary)] peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none"></div>
                        </label>
                    </div>

                    {{-- SMS Notifications --}}
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
                                    <path d="M15.5 1h-8C6.12 1 5 2.12 5 3.5v17C5 21.88 6.12 23 7.5 23h8c1.38 0 2.5-1.12 2.5-2.5v-17C18 2.12 16.88 1 15.5 1zm-4 21c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm4.5-4H7V4h9v14z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold" style="color: var(--lumina-text-primary);">SMS Notifications</p>
                                <p class="text-xs" style="color: var(--lumina-text-muted);">Urgent alerts via text message</p>
                            </div>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" class="peer sr-only">
                            <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-[var(--lumina-primary)] peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none"></div>
                        </label>
                    </div>

                    {{-- Payment Reminders --}}
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
                                    <path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold" style="color: var(--lumina-text-primary);">Payment Reminders</p>
                                <p class="text-xs" style="color: var(--lumina-text-muted);">Get notified before payment due dates</p>
                            </div>
                        </div>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="checkbox" class="peer sr-only" checked>
                            <div class="peer h-6 w-11 rounded-full bg-gray-200 after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-[var(--lumina-primary)] peer-checked:after:translate-x-full peer-checked:after:border-white peer-focus:outline-none"></div>
                        </label>
                    </div>
                </div>
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
</x-layouts.parent>
