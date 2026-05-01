<x-layouts.admin :title="__('Settings')" :user="$user ?? null" :current-route="'admin.settings'">
    <div class="mx-auto w-full max-w-6xl space-y-6">
        <div>
            <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
                Admin Settings
            </h1>
            <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
                Manage your profile details and account security.
            </p>
        </div>

        @if (session('success'))
            <div class="rounded-xl border p-4" style="background-color: #D1FAE5; border-color: #A7F3D0; color: #065F46;">
                <p class="text-sm font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border p-4" style="background-color: #FEF2F2; border-color: #FECACA; color: #991B1B;">
                <ul class="list-disc space-y-1 pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="flex flex-col gap-6">
                <section class="flex flex-col items-center rounded-3xl border p-8" style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;">
                    <div class="mb-4 flex h-32 w-32 items-center justify-center rounded-full" style="background: linear-gradient(135deg, var(--lumina-primary) 0%, #034C3C 100%);">
                        <span class="text-4xl font-bold text-white">{{ $user ? $user->initials() : 'AD' }}</span>
                    </div>

                    <h2 class="text-xl font-bold" style="color: var(--lumina-text-primary); font-family: 'Young Serif', Georgia, serif;">
                        {{ $user->name }}
                    </h2>
                    <p class="text-sm" style="color: var(--lumina-text-muted);">
                        Admin ID: ADM-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}
                    </p>
                </section>

                <section class="rounded-3xl border p-8" style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;">
                    <div class="mb-6 flex items-center gap-2">
                        <svg class="h-5 w-5" fill="currentColor" style="color: var(--lumina-primary);" viewBox="0 0 24 24">
                            <path d="M12 3l8 4v6c0 5.25-3.5 8.75-8 10-4.5-1.25-8-4.75-8-10V7l8-4zm0 3.18L7 8.44V13c0 3.58 2.16 6.26 5 7.34 2.84-1.08 5-3.76 5-7.34V8.44l-5-2.26z"/>
                        </svg>
                        <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">Admin Information</h3>
                    </div>

                    <div class="space-y-4">
                        <div class="rounded-xl border p-4" style="border-color: var(--lumina-border);">
                            <p class="text-xs" style="color: var(--lumina-text-muted);">Role</p>
                            <p class="text-sm font-semibold" style="color: var(--lumina-text-primary);">Administrator</p>
                        </div>

                        <div class="rounded-xl border p-4" style="border-color: var(--lumina-border);">
                            <p class="text-xs" style="color: var(--lumina-text-muted);">Years in System</p>
                            <p class="text-sm font-semibold" style="color: var(--lumina-text-primary);">{{ $yearsInRole }} Year(s)</p>
                        </div>
                    </div>
                </section>
            </div>

            <div class="flex flex-col gap-6">
                <section class="rounded-3xl border p-8" style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;">
                    <div class="mb-6 flex items-center gap-2">
                        <svg class="h-5 w-5" fill="currentColor" style="color: var(--lumina-primary);" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                        <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">Personal Details</h3>
                    </div>

                    <form method="POST" action="{{ route('admin.settings.update') }}" class="flex flex-col gap-5">
                        @csrf
                        @method('PATCH')

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Full Name</label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}" class="rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2" style="background-color: var(--lumina-bg-main); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);">
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Email Address</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2" style="background-color: var(--lumina-bg-main); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);">
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Phone Number</label>
                                <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2" style="background-color: var(--lumina-bg-main); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);">
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Primary Language</label>
                                <div class="relative">
                                    <select name="preferred_language" class="w-full appearance-none rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2" style="background-color: var(--lumina-bg-main); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);">
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

                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">Bio</label>
                            <textarea name="bio" rows="3" class="resize-none rounded-xl border px-4 py-3 text-sm outline-none transition-all focus:ring-2" style="background-color: var(--lumina-bg-main); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);">{{ old('bio', $user->bio) }}</textarea>
                        </div>

                        <button type="submit" class="mt-2 w-fit rounded-xl px-8 py-3 text-sm font-bold text-white transition-all hover:opacity-90 hover:scale-[1.02] active:scale-[0.98]" style="background-color: var(--lumina-primary);">
                            Save Changes
                        </button>
                    </form>
                </section>

                <section class="rounded-3xl border p-8" style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;">
                    <div class="mb-6 flex items-center gap-2">
                        <svg class="h-5 w-5" fill="currentColor" style="color: var(--lumina-primary);" viewBox="0 0 24 24">
                            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                        </svg>
                        <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">Security</h3>
                    </div>

                    <div class="mb-4 flex items-center justify-between rounded-xl border p-4" style="border-color: var(--lumina-border);">
                        <div>
                            <p class="text-sm font-bold" style="color: var(--lumina-text-primary);">Password Reset</p>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">Last changed {{ $passwordLastChanged }}</p>
                        </div>
                        <a href="{{ route('user-password.edit') }}" class="text-sm font-bold transition-opacity hover:opacity-80" style="color: var(--lumina-primary);">Change</a>
                    </div>

                    <div class="flex items-center justify-between rounded-xl border p-4" style="border-color: var(--lumina-border);">
                        <div>
                            <p class="text-sm font-bold" style="color: var(--lumina-text-primary);">Two-Factor Authentication</p>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">Status: {{ $twoFactorEnabled ? 'Enabled' : 'Disabled' }}</p>
                        </div>
                        <a href="{{ route('two-factor.show') }}" class="text-sm font-bold transition-opacity hover:opacity-80" style="color: var(--lumina-primary);">
                            {{ $twoFactorEnabled ? 'Manage' : 'Enable' }}
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-layouts.admin>
