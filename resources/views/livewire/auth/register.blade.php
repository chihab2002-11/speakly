<x-layouts::auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Full name')"
            />
            <!-- Requested Role -->
            <div>
                <label class="text-sm font-medium text-zinc-700 dark:text-zinc-200">
                    {{ __('Register as') }}
                </label>

                <select
                    name="requested_role"
                    required
                    class="mt-1 w-full rounded-md border-zinc-300 dark:border-zinc-700 dark:bg-zinc-900"
                >
                    <option value="" disabled {{ old('requested_role') ? '' : 'selected' }}>
                        {{ __('Choose a role') }}
                    </option>
                    <option value="student" {{ old('requested_role') === 'student' ? 'selected' : '' }}>
                        {{ __('Student') }}
                    </option>
                    <option value="parent" {{ old('requested_role') === 'parent' ? 'selected' : '' }}>
                        {{ __('parent') }}
                    </option>
                    <option value="teacher" {{ old('requested_role') === 'teacher' ? 'selected' : '' }}>
                        {{ __('Teacher') }}
                    </option>
                    <option value="secretary" {{ old('requested_role') === 'secretary' ? 'selected' : '' }}>
                        {{ __('Secretary') }}
                    </option>
                </select>

                @error('requested_role')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
