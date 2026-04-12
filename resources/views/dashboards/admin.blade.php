<x-layouts.admin :title="__('Admin Dashboard')" :user="$user" :current-route="'role.dashboard'">
    <div class="mx-auto w-full max-w-7xl space-y-8">
        <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
            <div class="max-w-2xl">
                <h1 class="text-4xl font-extrabold tracking-tight md:text-5xl" style="color: #181D19; letter-spacing: -0.9px;">Visitor Page Content</h1>
                <p class="mt-3 text-lg leading-8 md:text-xl" style="color: #3F4941;">
                    Manage the programs, languages, and certifications displayed on your public-facing portal.
                </p>
            </div>

            <button
                type="button"
                onclick="openCreateProgramModal()"
                class="inline-flex h-[98px] w-[168px] items-center justify-center gap-3 rounded-xl px-6 py-4 text-center text-[16px] font-bold text-white shadow-sm transition hover:opacity-90"
                style="background-color: #2D8C5E;"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add New<br/>Program</span>
            </button>
        </div>

        @if(session('success'))
            <div class="rounded-xl border px-4 py-3 text-sm font-semibold" style="background-color: #D1FAE5; border-color: #A7F3D0; color: #065F46;">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-xl border px-4 py-3" style="background-color: #FEF2F2; border-color: #FECACA; color: #991B1B;">
                <ul class="list-disc space-y-1 pl-5 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="hidden">
            <form method="POST" action="{{ route('admin.programs.reorder') }}">
                @csrf
                @method('PATCH')
                @foreach($programs as $program)
                    <input type="hidden" name="ordered_ids[]" value="{{ $program->id }}">
                @endforeach
            </form>
        </div>

        <section class="overflow-hidden rounded-2xl border" style="background-color: #FFFFFF; border-color: #F1F5F9; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);">
            <div class="flex flex-col gap-3 border-b px-6 py-5 md:flex-row md:items-center md:justify-between" style="border-color: #F1F5F9;">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-5 w-5 items-center justify-center" style="color: #2D8C5E;">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </span>
                    <h2 class="text-[20px] font-bold" style="color: #181D19;">Program Management</h2>
                </div>
                <span class="text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">{{ $activeProgramsCount }} Active Programs</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr style="background-color: #F0F5EE;">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">Order</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">Program Name</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">Lang Code</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($programs as $program)
                            <tr class="border-t" style="border-color: #F1F5F9;">
                                <td class="px-6 py-5 text-sm font-medium" style="color: #3F4941;">
                                    <div class="flex items-center gap-3">
                                        <span>{{ $program->sort_order }}</span>
                                        <div class="flex items-center gap-1">
                                            @if(! $loop->first)
                                                <form method="POST" action="{{ route('admin.programs.move', ['program' => $program, 'direction' => 'up']) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="inline-flex h-6 w-6 items-center justify-center rounded hover:bg-gray-100" title="Move up">
                                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #94A3B8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                                    </button>
                                                </form>
                                            @endif
                                            @if(! $loop->last)
                                                <form method="POST" action="{{ route('admin.programs.move', ['program' => $program, 'direction' => 'down']) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="inline-flex h-6 w-6 items-center justify-center rounded hover:bg-gray-100" title="Move down">
                                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #94A3B8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $program->flag_url }}" alt="{{ $program->name }}" class="h-5 w-7 rounded object-cover shadow-sm">
                                        <div>
                                            <p class="text-xl font-bold leading-tight" style="color: #181D19;">{{ $program->title }}</p>
                                            <p class="max-w-sm text-sm" style="color: #3F4941;">{{ $program->description }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-5 font-mono text-sm" style="color: #3F4941;">{{ $program->locale_code ?: strtoupper($program->code) }}</td>

                                <td class="px-6 py-5 text-center">
                                    <form method="POST" action="{{ route('admin.programs.toggle', $program) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="relative inline-flex h-6 w-11 items-center rounded-full transition {{ $program->is_active ? 'bg-[#2D8C5E]' : 'bg-[#CBD5E1]' }}">
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition {{ $program->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                        </button>
                                    </form>
                                </td>

                                <td class="px-6 py-5">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            onclick="openEditProgramModal(this)"
                                            data-id="{{ $program->id }}"
                                            data-code="{{ $program->code }}"
                                            data-locale-code="{{ $program->locale_code }}"
                                            data-name="{{ $program->name }}"
                                            data-title="{{ $program->title }}"
                                            data-description="{{ $program->description }}"
                                            data-full-description="{{ $program->full_description }}"
                                            data-flag-url="{{ $program->flag_url }}"
                                            data-active="{{ $program->is_active ? '1' : '0' }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-gray-100"
                                            title="Edit"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #94A3B8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <form method="POST" action="{{ route('admin.programs.destroy', $program) }}" onsubmit="return confirm('Delete this program?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-red-50" title="Delete">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #94A3B8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V4h6v3m-7 4v6m4-6v6m5 4H7a2 2 0 01-2-2V7h14v12a2 2 0 01-2 2z"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm" style="color: #64748B;">No programs yet. Add your first language program.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t px-6 py-4 text-center" style="background-color: #F8FAFC; border-color: #F1F5F9;">
                <span class="text-sm font-bold" style="color: #2D8C5E;">Total programs: {{ $programs->count() }}</span>
            </div>
        </section>
    </div>

    <div id="createProgramModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" onclick="if(event.target===this){closeCreateProgramModal()}">
        <div class="w-full max-w-2xl rounded-2xl p-6" style="background-color: #FFFFFF;" onclick="event.stopPropagation()">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-xl font-bold" style="color: #181D19;">Add New Program</h3>
                <button type="button" onclick="closeCreateProgramModal()" class="rounded-lg p-2 hover:bg-gray-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748B;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.programs.store') }}" class="grid gap-4 md:grid-cols-2">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Program Code</label>
                    <input name="code" required placeholder="en" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Locale Code</label>
                    <input name="locale_code" placeholder="EN-GB" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Name</label>
                    <input name="name" required placeholder="English" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Title</label>
                    <input name="title" required placeholder="English Mastery" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;" />
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Description</label>
                    <input name="description" required placeholder="Short card description" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;" />
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Full Description</label>
                    <textarea name="full_description" rows="4" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Flag URL</label>
                    <input name="flag_url" required placeholder="https://flagcdn.com/w80/gb.png" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;" />
                </div>
                <div class="md:col-span-2 flex items-center gap-2">
                    <input type="checkbox" id="create_is_active" name="is_active" value="1" checked>
                    <label for="create_is_active" class="text-sm" style="color: #3F4941;">Visible on Visitor page</label>
                </div>
                <div class="md:col-span-2 mt-2 flex justify-end gap-3">
                    <button type="button" onclick="closeCreateProgramModal()" class="rounded-xl border px-4 py-2.5 text-sm font-semibold" style="border-color: #E2E8F0; color: #3F4941;">Cancel</button>
                    <button type="submit" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white" style="background-color: #2D8C5E;">Create Program</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editProgramModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" onclick="if(event.target===this){closeEditProgramModal()}">
        <div class="w-full max-w-2xl rounded-2xl p-6" style="background-color: #FFFFFF;" onclick="event.stopPropagation()">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-xl font-bold" style="color: #181D19;">Edit Program</h3>
                <button type="button" onclick="closeEditProgramModal()" class="rounded-lg p-2 hover:bg-gray-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748B;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="editProgramForm" method="POST" action="" class="grid gap-4 md:grid-cols-2">
                @csrf
                @method('PATCH')
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Program Code</label>
                    <input id="edit_code" name="code" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Locale Code</label>
                    <input id="edit_locale_code" name="locale_code" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Name</label>
                    <input id="edit_name" name="name" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Title</label>
                    <input id="edit_title" name="title" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;" />
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Description</label>
                    <input id="edit_description" name="description" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;" />
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Full Description</label>
                    <textarea id="edit_full_description" name="full_description" rows="4" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Flag URL</label>
                    <input id="edit_flag_url" name="flag_url" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;" />
                </div>
                <div class="md:col-span-2 flex items-center gap-2">
                    <input type="checkbox" id="edit_is_active" name="is_active" value="1">
                    <label for="edit_is_active" class="text-sm" style="color: #3F4941;">Visible on Visitor page</label>
                </div>
                <div class="md:col-span-2 mt-2 flex justify-end gap-3">
                    <button type="button" onclick="closeEditProgramModal()" class="rounded-xl border px-4 py-2.5 text-sm font-semibold" style="border-color: #E2E8F0; color: #3F4941;">Cancel</button>
                    <button type="submit" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white" style="background-color: #2D8C5E;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateProgramModal() {
            const modal = document.getElementById('createProgramModal');
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeCreateProgramModal() {
            const modal = document.getElementById('createProgramModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openEditProgramModal(button) {
            const modal = document.getElementById('editProgramModal');
            const form = document.getElementById('editProgramForm');

            if (!modal || !form || !button) {
                return;
            }

            const programId = button.getAttribute('data-id');
            form.action = `{{ url('/admin/programs') }}/${programId}`;

            document.getElementById('edit_code').value = button.getAttribute('data-code') || '';
            document.getElementById('edit_locale_code').value = button.getAttribute('data-locale-code') || '';
            document.getElementById('edit_name').value = button.getAttribute('data-name') || '';
            document.getElementById('edit_title').value = button.getAttribute('data-title') || '';
            document.getElementById('edit_description').value = button.getAttribute('data-description') || '';
            document.getElementById('edit_full_description').value = button.getAttribute('data-full-description') || '';
            document.getElementById('edit_flag_url').value = button.getAttribute('data-flag-url') || '';
            document.getElementById('edit_is_active').checked = button.getAttribute('data-active') === '1';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEditProgramModal() {
            const modal = document.getElementById('editProgramModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
</x-layouts.admin>
