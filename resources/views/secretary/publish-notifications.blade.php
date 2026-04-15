<x-layouts.secretary :title="__('Publish Notifications')" :current-route="'secretary.publish-notifications'">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
            Publish Notifications
        </h1>
        <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
            Broadcast announcements to selected academy audiences.
        </p>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light);">
            <p class="text-sm" style="color: var(--lumina-text-muted);">Students</p>
            <p class="mt-1 text-3xl font-bold" style="color: var(--lumina-text-primary);">{{ $audienceCounts['students'] }}</p>
        </div>
        <div class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light);">
            <p class="text-sm" style="color: var(--lumina-text-muted);">Parents</p>
            <p class="mt-1 text-3xl font-bold" style="color: var(--lumina-text-primary);">{{ $audienceCounts['parents'] }}</p>
        </div>
        <div class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light);">
            <p class="text-sm" style="color: var(--lumina-text-muted);">Teachers</p>
            <p class="mt-1 text-3xl font-bold" style="color: var(--lumina-text-primary);">{{ $audienceCounts['teachers'] }}</p>
        </div>
        <div class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light);">
            <p class="text-sm" style="color: var(--lumina-text-muted);">Admins</p>
            <p class="mt-1 text-3xl font-bold" style="color: var(--lumina-text-primary);">{{ $audienceCounts['admins'] }}</p>
        </div>
        <div class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light);">
            <p class="text-sm" style="color: var(--lumina-text-muted);">Secretaries</p>
            <p class="mt-1 text-3xl font-bold" style="color: var(--lumina-text-primary);">{{ $audienceCounts['secretaries'] }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm font-semibold" style="background-color: #ECFDF3; border-color: #BBF7D0; color: #166534;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="background-color: #FEF2F2; border-color: #FECACA; color: #991B1B;">
            <p class="font-semibold">Please fix the following:</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-3xl border p-6" style="background: white; border-color: var(--lumina-border-light);">
        <form method="POST" action="{{ route('secretary.publish-notifications.send') }}" class="space-y-4">
            @csrf

            <div>
                <label for="title" class="mb-1 block text-sm font-semibold" style="color: var(--lumina-text-secondary);">Title</label>
                <input
                    id="title"
                    name="title"
                    type="text"
                    maxlength="120"
                    value="{{ old('title') }}"
                    required
                    class="w-full rounded-xl border px-4 py-2 text-sm outline-none"
                    style="border-color: var(--lumina-border); background: #F8FAFC;"
                >
            </div>

            <div>
                <label for="message" class="mb-1 block text-sm font-semibold" style="color: var(--lumina-text-secondary);">Message</label>
                <textarea
                    id="message"
                    name="message"
                    rows="5"
                    maxlength="2000"
                    required
                    class="w-full rounded-xl border px-4 py-3 text-sm outline-none"
                    style="border-color: var(--lumina-border); background: #F8FAFC;"
                >{{ old('message') }}</textarea>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="audience" class="mb-1 block text-sm font-semibold" style="color: var(--lumina-text-secondary);">Audience</label>
                    <select
                        id="audience"
                        name="audience"
                        class="w-full rounded-xl border px-4 py-2 text-sm outline-none"
                        style="border-color: var(--lumina-border); background: #F8FAFC;"
                    >
                        <option value="all" @selected(old('audience', 'all') === 'all')>All core users (students, parents, teachers, admins)</option>
                        <option value="students" @selected(old('audience') === 'students')>Students only</option>
                        <option value="parents" @selected(old('audience') === 'parents')>Parents only</option>
                        <option value="teachers" @selected(old('audience') === 'teachers')>Teachers only</option>
                    </select>
                </div>

                <div>
                    <label for="url" class="mb-1 block text-sm font-semibold" style="color: var(--lumina-text-secondary);">Action URL (optional)</label>
                    <input
                        id="url"
                        name="url"
                        type="url"
                        maxlength="255"
                        value="{{ old('url') }}"
                        placeholder="https://..."
                        class="w-full rounded-xl border px-4 py-2 text-sm outline-none"
                        style="border-color: var(--lumina-border); background: #F8FAFC;"
                    >
                </div>
            </div>

            <label class="inline-flex items-center gap-2 text-sm" style="color: var(--lumina-text-secondary);">
                <input type="checkbox" name="include_secretaries" value="1" @checked(old('include_secretaries'))>
                Include secretaries in this announcement
            </label>

            <div class="pt-2">
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-all hover:opacity-90"
                    style="background-color: var(--lumina-primary);"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.868v4.264a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12c0 4.971-4.029 9-9 9s-9-4.029-9-9 4.029-9 9-9 9 4.029 9 9z" />
                    </svg>
                    Publish Notification
                </button>
            </div>
        </form>
    </section>
</x-layouts.secretary>
