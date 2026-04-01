<x-layouts::app :title="__('New message')">
    <div class="mx-auto max-w-2xl space-y-6 p-4 sm:p-6">
        @include('messages.partials.nav', ['active' => 'create'])

        <div>
            <flux:heading size="xl">{{ __('New message') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Choose a recipient and write your message.') }}</flux:text>
        </div>

        @if ($errors->any())
            <flux:callout variant="danger" icon="exclamation-circle" heading="{{ __('Please fix the errors below.') }}">
                <ul class="mt-2 list-inside list-disc text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </flux:callout>
        @endif

        <form
            action="{{ route('messages.store') }}"
            method="POST"
            class="space-y-6 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900"
        >
            @csrf

            <flux:field>
                <flux:label>{{ __('To') }}</flux:label>
                <flux:select name="receiver_id" required :placeholder="__('Select a user')">
                    @foreach ($users as $user)
                        <flux:select.option
                            value="{{ $user->id }}"
                            :selected="(string) old('receiver_id') === (string) $user->id"
                        >
                            {{ $user->name }} ({{ $user->email }})
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="receiver_id" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Subject') }} ({{ __('optional') }})</flux:label>
                <flux:input name="subject" value="{{ old('subject') }}" />
                <flux:error name="subject" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Message') }}</flux:label>
                <flux:textarea name="body" rows="8" required>{{ old('body') }}</flux:textarea>
                <flux:error name="body" />
            </flux:field>

            <div class="flex flex-wrap gap-3">
                <flux:button type="submit" variant="primary">
                    {{ __('Send message') }}
                </flux:button>
                <flux:button :href="route('messages.inbox')" variant="ghost" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>