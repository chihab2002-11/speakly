<x-layouts.teacher :title="__('Profile Settings')" :currentRoute="'settings'">
    {{--
    ================================================================================
    BACKEND SUMMARY: Teacher Profile Settings Page
    ================================================================================
    
    API Endpoints Required:
    -----------------------
    1. GET /api/teacher/profile
       - Returns: Teacher profile data
       - Response: { data: TeacherProfile }
    
    2. PUT /api/teacher/profile
       - Updates teacher profile
       - Request: { name, email, phone, language, department, bio }
       - Response: { data: TeacherProfile }
    
    3. POST /api/teacher/profile/avatar
       - Uploads profile photo
       - Request: multipart/form-data { avatar }
       - Response: { data: { avatar_url: string } }
    
    4. GET /api/teacher/profile/sessions
       - Returns: Active sessions list
       - Response: { data: Session[] }
    
    5. DELETE /api/teacher/profile/sessions/{id}
       - Terminates a session
       - Response: { success: true }
    
    Expected Response Formats:
    --------------------------
    TeacherProfile: {
        id: number,
        name: string,
        email: string,
        phone: string|null,
        avatar: string|null,
        language: string,
        department: string|null,
        bio: string|null,
        employee_id: string,
        two_factor_enabled: boolean,
        password_last_changed: datetime
    }
    
    Session: {
        id: number,
        device: string,
        ip: string,
        last_activity: datetime,
        current: boolean
    }
    ================================================================================
    --}}

    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
            Profile Settings
        </h1>
        <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
            Manage your personal information, security preferences, and account settings.
        </p>
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
                                {{ $user ? $user->initials() : 'JD' }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Teacher Name & ID --}}
                <h2 class="text-xl font-bold" style="color: var(--lumina-text-primary); font-family: 'Young Serif', Georgia, serif;">
                    {{ $user->name ?? 'Dr. Jane Doe' }}
                </h2>
                <p class="text-sm" style="color: var(--lumina-text-muted);">
                    Employee ID: TCH-{{ str_pad($user->id ?? 1, 4, '0', STR_PAD_LEFT) }}
                </p>

                {{-- Upload Button --}}
                <button 
                    class="mt-6 rounded-xl px-6 py-3 text-sm font-bold text-white transition-all hover:opacity-90 hover:scale-[1.02] active:scale-[0.98] cursor-pointer"
                    style="background-color: var(--lumina-primary);"
                >
                    Upload New Photo
                </button>
                <p class="mt-2 text-xs" style="color: var(--lumina-text-muted);">
                    JPG, GIF or PNG. Max size of 800K
                </p>
            </div>

            {{-- Teaching Information Card --}}
            <div 
                class="flex flex-col rounded-3xl border p-8"
                style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;"
            >
                {{-- Header --}}
                <div class="mb-6 flex items-center gap-2">
                    <svg class="h-5 w-5" fill="currentColor" style="color: var(--lumina-primary);" viewBox="0 0 24 24">
                        <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                    </svg>
                    <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                        Teaching Information
                    </h3>
                </div>

                {{-- Info Items --}}
                <div class="space-y-4">
                    <div 
                        class="flex items-center justify-between rounded-xl border p-4"
                        style="border-color: var(--lumina-border);"
                    >
                        <div>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">Department</p>
                            <p class="text-sm font-semibold" style="color: var(--lumina-text-primary);">Language Studies</p>
                        </div>
                        <div 
                            class="flex h-10 w-10 items-center justify-center rounded-full"
                            style="background-color: var(--lumina-accent-green-bg);"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-primary);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                    </div>

                    <div 
                        class="flex items-center justify-between rounded-xl border p-4"
                        style="border-color: var(--lumina-border);"
                    >
                        <div>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">Subjects</p>
                            <p class="text-sm font-semibold" style="color: var(--lumina-text-primary);">{{ $teachingSubjects ?? 'No assigned subjects yet' }}</p>
                        </div>
                        <div 
                            class="flex h-10 w-10 items-center justify-center rounded-full"
                            style="background-color: #DBEAFE;"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #3B82F6;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                            </svg>
                        </div>
                    </div>

                    <div 
                        class="flex items-center justify-between rounded-xl border p-4"
                        style="border-color: var(--lumina-border);"
                    >
                        <div>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">Years of Experience</p>
                            <p class="text-sm font-semibold" style="color: var(--lumina-text-primary);">{{ $yearsOfExperience ?? 0 }} Years</p>
                        </div>
                        <div 
                            class="flex h-10 w-10 items-center justify-center rounded-full"
                            style="background-color: #FEF3C7;"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #F59E0B;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </div>
                    </div>
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
                <form method="POST" action="{{ route('teacher.settings.update') }}" class="flex flex-col gap-5">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-5 sm:grid-cols-2">
                        {{-- Full Name --}}
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Full Name</label>
                            <input 
                                type="text" 
                                name="name"
                                value="{{ old('name', $user->name) }}"
                                class="rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2"
                                style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);"
                            >
                        </div>

                        {{-- Email Address --}}
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Email Address</label>
                            <input 
                                type="email" 
                                name="email"
                                value="{{ old('email', $user->email) }}"
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
                                name="phone"
                                value="{{ old('phone', $user->phone) }}"
                                class="rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2"
                                style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);"
                            >
                        </div>

                        {{-- Primary Language --}}
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Primary Language</label>
                            <div class="relative">
                                <select 
                                    name="preferred_language"
                                    class="w-full appearance-none rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2 cursor-pointer"
                                    style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);"
                                >
                                    <option value="english" @selected(old('preferred_language', $user->preferred_language) === 'english')>English</option>
                                    <option value="french" @selected(old('preferred_language', $user->preferred_language) === 'french')>French</option>
                                    <option value="spanish" @selected(old('preferred_language', $user->preferred_language) === 'spanish')>Spanish</option>
                                    <option value="german" @selected(old('preferred_language', $user->preferred_language) === 'german')>German</option>
                                    <option value="arabic" @selected(old('preferred_language', $user->preferred_language) === 'arabic')>Arabic</option>
                                </select>
                                <svg class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2" fill="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                                    <path d="M7 10l5 5 5-5z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Bio --}}
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Bio</label>
                        <textarea 
                            name="bio"
                            rows="3"
                            placeholder="Tell us a bit about yourself..."
                            class="rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2 resize-none"
                            style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);"
                        >{{ old('bio', $user->bio) }}</textarea>
                    </div>

                    {{-- Save Button --}}
                    <button 
                        type="submit"
                        class="mt-2 w-fit rounded-xl px-8 py-3 text-sm font-bold text-white transition-all hover:opacity-90 hover:scale-[1.02] active:scale-[0.98] cursor-pointer"
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
                        href="{{ route('user-password.edit') }}"
                        class="text-sm font-bold cursor-pointer transition-opacity hover:opacity-80"
                        style="color: var(--lumina-primary);"
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
                    <a
                        href="{{ route('two-factor.show') }}"
                        class="text-sm font-bold cursor-pointer transition-opacity hover:opacity-80"
                        style="color: var(--lumina-primary);"
                    >
                        {{ ($twoFactorEnabled ?? false) ? 'Manage' : 'Enable' }}
                    </a>
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
</x-layouts.teacher>
