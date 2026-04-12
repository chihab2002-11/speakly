<x-layouts.student :title="__('Change Password')" :currentRoute="'settings'">
    {{-- Page Header with Back Link --}}
    <div class="mb-8">
        <a 
            href="{{ route('student.settings') }}" 
            class="mb-4 inline-flex items-center gap-2 text-sm font-medium transition-opacity hover:opacity-80 cursor-pointer"
            style="color: var(--lumina-primary);"
            wire:navigate
        >
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
            </svg>
            Back to Settings
        </a>
        <h1 class="font-inter text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
            Change Password
        </h1>
        <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
            Update your password to keep your account secure.
        </p>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border p-4 text-sm font-semibold" style="background-color: #D1FAE5; border-color: #A7F3D0; color: #065F46;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Password Change Card --}}
    <div class="mx-auto max-w-xl">
        <div 
            class="flex flex-col rounded-3xl border p-8"
            style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.1); border-radius: 24px;"
        >
            {{-- Header Icon --}}
            <div class="mb-6 flex items-center gap-3">
                <div 
                    class="flex h-12 w-12 items-center justify-center rounded-full"
                    style="background-color: var(--lumina-bg-card);"
                >
                    <svg class="h-6 w-6" fill="currentColor" style="color: var(--lumina-primary);" viewBox="0 0 24 24">
                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                        Update Your Password
                    </h3>
                    <p class="text-sm" style="color: var(--lumina-text-muted);">
                        Ensure your password is strong and unique
                    </p>
                </div>
            </div>

            {{-- Password Form --}}
            <form class="flex flex-col gap-5" method="POST" action="{{ route('student.password.update') }}">
                @csrf
                
                {{-- Current Password --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">
                        Current Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            name="current_password"
                            placeholder="Enter your current password"
                            class="w-full rounded-xl border px-4 py-3 pr-12 text-sm outline-none transition-all focus:ring-2"
                            style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);"
                            required
                        >
                        <button 
                            type="button"
                            onclick="togglePassword(this)"
                            class="absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer"
                        >
                            <svg class="h-5 w-5 eye-icon" fill="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                                <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                            </svg>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-xs font-semibold" style="color: #b91c1c;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Divider --}}
                <div class="my-2 border-t" style="border-color: var(--lumina-border);"></div>

                {{-- New Password --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">
                        New Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            name="password"
                            placeholder="Enter your new password"
                            class="w-full rounded-xl border px-4 py-3 pr-12 text-sm outline-none transition-all focus:ring-2"
                            style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);"
                            required
                        >
                        <button 
                            type="button"
                            onclick="togglePassword(this)"
                            class="absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer"
                        >
                            <svg class="h-5 w-5 eye-icon" fill="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                                <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs font-semibold" style="color: #b91c1c;">{{ $message }}</p>
                    @enderror
                    {{-- Password Requirements --}}
                    <div class="mt-1 flex flex-col gap-1">
                        <p class="text-xs" style="color: var(--lumina-text-muted);">Password must contain:</p>
                        <ul class="ml-4 list-disc text-xs" style="color: var(--lumina-text-muted);">
                            <li>At least 8 characters</li>
                            <li>One uppercase letter</li>
                            <li>One number</li>
                        </ul>
                    </div>
                </div>

                {{-- Confirm New Password --}}
                <div class="flex flex-col gap-2">
                    <label class="text-sm font-medium" style="color: var(--lumina-text-secondary);">
                        Confirm New Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            name="password_confirmation"
                            placeholder="Confirm your new password"
                            class="w-full rounded-xl border px-4 py-3 pr-12 text-sm outline-none transition-all focus:ring-2"
                            style="background-color: var(--lumina-bg-card); border-color: var(--lumina-border); color: var(--lumina-text-primary); --tw-ring-color: var(--lumina-primary);"
                            required
                        >
                        <button 
                            type="button"
                            onclick="togglePassword(this)"
                            class="absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer"
                        >
                            <svg class="h-5 w-5 eye-icon" fill="currentColor" style="color: var(--lumina-text-muted);" viewBox="0 0 24 24">
                                <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <p class="text-xs font-semibold" style="color: #b91c1c;">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Action Buttons --}}
                <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <a 
                        href="{{ route('student.settings') }}"
                        class="rounded-xl border px-6 py-3 text-center text-sm font-bold transition-all hover:bg-gray-50 cursor-pointer"
                        style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);"
                        wire:navigate
                    >
                        Cancel
                    </a>
                    <button 
                        type="submit"
                        class="rounded-xl px-8 py-3 text-sm font-bold text-white transition-all hover:opacity-90 cursor-pointer"
                        style="background-color: var(--lumina-primary);"
                    >
                        Update Password
                    </button>
                </div>
            </form>
        </div>

        {{-- Security Tips Card --}}
        <div 
            class="mt-6 flex items-start gap-3 rounded-2xl p-4"
            style="background-color: var(--lumina-bg-card);"
        >
            <svg class="h-5 w-5 flex-shrink-0" fill="currentColor" style="color: var(--lumina-primary);" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold" style="color: var(--lumina-text-primary);">Security Tip</p>
                <p class="text-xs" style="color: var(--lumina-text-muted);">
                    Never share your password with anyone. Lumina Academy staff will never ask for your password.
                    Consider using a password manager to generate and store strong, unique passwords.
                </p>
            </div>
        </div>
    </div>

    {{-- Toggle Password Visibility Script --}}
    <script>
        function togglePassword(button) {
            const input = button.parentElement.querySelector('input');
            const icon = button.querySelector('.eye-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>';
            }
        }
    </script>
</x-layouts.student>
