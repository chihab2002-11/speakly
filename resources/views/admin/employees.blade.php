<x-layouts.admin :title="__('Manage Employees')" :user="auth()->user()" :current-route="'admin.employees.index'">
    <div class="mx-auto w-full max-w-7xl space-y-8">
        <section class="space-y-3">
            <h1 class="text-4xl font-extrabold tracking-tight md:text-5xl" style="color: #1A1B22; letter-spacing: -1.2px;">Manage employees</h1>
            <p class="max-w-3xl text-base leading-7" style="color: #444653;">
                Manage your educational institution's core human resources. Set permissions for administrative staff and coordinate language assignments for your teaching faculty.
            </p>
        </section>

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

        <section class="grid gap-4 lg:grid-cols-3">
            <article class="relative overflow-hidden rounded-2xl p-6 text-white" style="background: #2D8C5E; box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 170px;">
                <p class="text-xs font-bold uppercase tracking-[1.2px]">Total Active Staff</p>
                <p class="mt-3 text-5xl font-black leading-none">{{ $totalActiveStaff }}</p>
                <p class="mt-6 text-sm font-semibold">+12% from last term</p>
                <div class="pointer-events-none absolute -bottom-6 right-3 h-16 w-16 rounded-full border border-white/30"></div>
                <div class="pointer-events-none absolute -bottom-3 right-20 h-9 w-9 rounded-full bg-white/15"></div>
            </article>

            <article class="rounded-2xl border p-5" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 170px;">
                <div class="flex items-center justify-between">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl" style="background: #64748B; color: #BDD9CE;">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2a3 3 0 00-5-2.83M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2a3 3 0 015-2.83m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-[1.2px]" style="color: #525C87;">Secretaries</span>
                </div>
                <p class="mt-4 text-4xl font-extrabold" style="color: #1A1B22;">{{ $activeSecretaries }}</p>
                <p class="mt-2 text-sm" style="color: #444653;">Managing enrollment and logistics</p>
            </article>

            <article class="rounded-2xl border p-5" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 170px;">
                <div class="flex items-center justify-between">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl" style="background: #FFDBCE; color: #802A00;">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422A12.083 12.083 0 0112 20.055a12.083 12.083 0 01-6.16-9.477L12 14z"/></svg>
                    </span>
                    <span class="text-xs font-bold uppercase tracking-[1.2px]" style="color: #611E00;">Teachers</span>
                </div>
                <p class="mt-4 text-4xl font-extrabold" style="color: #1A1B22;">{{ $activeTeachers }}</p>
                <p class="mt-2 text-sm" style="color: #444653;">Across {{ $activeLanguages }} active language tags</p>
            </article>
        </section>

        <section class="space-y-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-4xl font-bold tracking-tight" style="color: #1A1B22; letter-spacing: -0.6px;">Secretaries</h2>
                    <p class="text-sm" style="color: #444653;">Administrative and system access management</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <form method="GET" action="{{ route('admin.employees.index') }}" class="flex items-center gap-2">
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search staff or records..." class="w-64 rounded-lg border px-3 py-2 text-sm" style="background: #F4F2FC; border-color: rgba(196, 197, 213, 0.2);">
                        <button type="submit" class="rounded-lg border px-3 py-2 text-sm font-semibold" style="border-color: rgba(196, 197, 213, 0.25); color: #2D8C5E;">Filter</button>
                    </form>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl" style="background: #FFFFFF; box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04);">
                <table class="min-w-full">
                    <thead>
                        <tr style="background: #F4F2FC;">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Staff Member</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Contact Email</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($secretaries as $secretary)
                            <tr class="border-t" style="border-color: rgba(196, 197, 213, 0.1);">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold" style="background: #DBEAFE; color: #2D8C5E;">{{ $secretary->initials() }}</span>
                                        <div>
                                            <p class="text-xl font-bold leading-5" style="color: #1A1B22;">{{ $secretary->name }}</p>
                                            <p class="mt-1 text-sm" style="color: #444653;">{{ $secretary->phone ?: 'Administrative Staff' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-base" style="color: #444653;">{{ $secretary->email }}</td>
                                <td class="px-6 py-5">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" onclick="openEditSecretaryModal(this)" data-id="{{ $secretary->id }}" data-name="{{ $secretary->name }}" data-email="{{ $secretary->email }}" data-phone="{{ $secretary->phone }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-gray-100" title="Edit">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #444653;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <form method="POST" action="{{ route('admin.employees.secretaries.destroy', $secretary) }}" onsubmit="return confirm('Delete this secretary account?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-red-50" title="Delete">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #444653;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V4h6v3m-7 4v6m4-6v6m5 4H7a2 2 0 01-2-2V7h14v12a2 2 0 01-2 2z"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-sm" style="color: #64748B;">No secretaries found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="space-y-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-4xl font-bold tracking-tight" style="color: #1A1B22; letter-spacing: -0.6px;">Academic Faculty</h2>
                    <p class="text-sm" style="color: #444653;">Teacher expertise and language curriculum assignment</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl" style="background: #FFFFFF; box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04);">
                <table class="min-w-full">
                    <thead>
                        <tr style="background: #F4F2FC;">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Teacher</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Contact Email</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Assigned Languages</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Management</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($teachers as $teacher)
                            @php($tags = $teacherLanguageMap[$teacher->id] ?? [])
                            <tr class="border-t" style="border-color: rgba(196, 197, 213, 0.1);">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold" style="background: #DBEAFE; color: #2D8C5E;">{{ $teacher->initials() }}</span>
                                        <div>
                                            <p class="text-xl font-bold leading-5" style="color: #1A1B22;">{{ $teacher->name }}</p>
                                            <p class="mt-1 text-sm font-semibold" style="color: #2D8C5E;">{{ $teacher->bio ? 'Senior Faculty' : 'Faculty Member' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-base" style="color: #444653;">{{ $teacher->email }}</td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-wrap gap-2">
                                        @forelse($tags as $index => $tag)
                                            <span class="rounded-md px-2.5 py-1 text-[10px] font-bold uppercase {{ $index === 2 ? 'bg-[#872D00] text-white' : 'bg-[#64748B] text-[#BDD9CE]' }}">
                                                {{ $tag }}
                                            </span>
                                        @empty
                                            <span class="text-xs" style="color: #64748B;">No language tags yet</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex justify-end items-center gap-3">
                                        <form method="POST" action="{{ route('admin.employees.teachers.assign-language', $teacher) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="preferred_language" class="rounded-lg border px-2 py-1 text-xs" style="border-color: rgba(196, 197, 213, 0.3);">
                                                @foreach($languageOptions as $languageOption)
                                                    <option value="{{ $languageOption }}" @selected($teacher->preferred_language === $languageOption)>{{ strtoupper($languageOption) }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-bold text-white" style="background: #2D8C5E;">Assign to Language</button>
                                        </form>

                                        <button type="button" onclick="openEditTeacherModal(this)" data-id="{{ $teacher->id }}" data-name="{{ $teacher->name }}" data-email="{{ $teacher->email }}" data-phone="{{ $teacher->phone }}" data-preferred-language="{{ $teacher->preferred_language }}" data-bio="{{ $teacher->bio }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-gray-100" title="Edit">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #444653;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <form method="POST" action="{{ route('admin.employees.teachers.destroy', $teacher) }}" onsubmit="return confirm('Delete this teacher account?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-red-50" title="Delete">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #444653;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V4h6v3m-7 4v6m4-6v6m5 4H7a2 2 0 01-2-2V7h14v12a2 2 0 01-2 2z"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm" style="color: #64748B;">No teachers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div id="editSecretaryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" onclick="if(event.target===this){closeEditSecretaryModal()}">
        <div class="w-full max-w-xl rounded-2xl p-6" style="background:#FFFFFF;" onclick="event.stopPropagation()">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xl font-bold" style="color:#1A1B22;">Edit Secretary</h3>
                <button type="button" onclick="closeEditSecretaryModal()" class="rounded-lg p-2 hover:bg-gray-100">×</button>
            </div>
            <form id="editSecretaryForm" method="POST" action="" class="grid gap-3">
                @csrf
                @method('PATCH')
                <input id="edit_secretary_name" name="name" type="text" required class="rounded-lg border px-3 py-2 text-sm" style="border-color:#E2E8F0;">
                <input id="edit_secretary_email" name="email" type="email" required class="rounded-lg border px-3 py-2 text-sm" style="border-color:#E2E8F0;">
                <input id="edit_secretary_phone" name="phone" type="text" class="rounded-lg border px-3 py-2 text-sm" style="border-color:#E2E8F0;">
                <div class="mt-2 flex justify-end gap-2">
                    <button type="button" onclick="closeEditSecretaryModal()" class="rounded-lg border px-4 py-2 text-sm font-semibold" style="border-color:#E2E8F0;color:#3F4941;">Cancel</button>
                    <button type="submit" class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background:#2D8C5E;">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editTeacherModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" onclick="if(event.target===this){closeEditTeacherModal()}">
        <div class="w-full max-w-xl rounded-2xl p-6" style="background:#FFFFFF;" onclick="event.stopPropagation()">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-xl font-bold" style="color:#1A1B22;">Edit Teacher</h3>
                <button type="button" onclick="closeEditTeacherModal()" class="rounded-lg p-2 hover:bg-gray-100">×</button>
            </div>
            <form id="editTeacherForm" method="POST" action="" class="grid gap-3">
                @csrf
                @method('PATCH')
                <input id="edit_teacher_name" name="name" type="text" required class="rounded-lg border px-3 py-2 text-sm" style="border-color:#E2E8F0;">
                <input id="edit_teacher_email" name="email" type="email" required class="rounded-lg border px-3 py-2 text-sm" style="border-color:#E2E8F0;">
                <input id="edit_teacher_phone" name="phone" type="text" class="rounded-lg border px-3 py-2 text-sm" style="border-color:#E2E8F0;">
                <select id="edit_teacher_language" name="preferred_language" class="rounded-lg border px-3 py-2 text-sm" style="border-color:#E2E8F0;">
                    <option value="">Preferred language</option>
                    @foreach($languageOptions as $languageOption)
                        <option value="{{ $languageOption }}">{{ strtoupper($languageOption) }}</option>
                    @endforeach
                </select>
                <textarea id="edit_teacher_bio" name="bio" rows="3" class="rounded-lg border px-3 py-2 text-sm" style="border-color:#E2E8F0;"></textarea>
                <div class="mt-2 flex justify-end gap-2">
                    <button type="button" onclick="closeEditTeacherModal()" class="rounded-lg border px-4 py-2 text-sm font-semibold" style="border-color:#E2E8F0;color:#3F4941;">Cancel</button>
                    <button type="submit" class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background:#2D8C5E;">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditSecretaryModal(button) {
            const modal = document.getElementById('editSecretaryModal');
            const form = document.getElementById('editSecretaryForm');
            if (!modal || !form || !button) return;

            const id = button.getAttribute('data-id') || '';
            form.action = `{{ url('/admin/employees/secretaries') }}/${id}`;

            document.getElementById('edit_secretary_name').value = button.getAttribute('data-name') || '';
            document.getElementById('edit_secretary_email').value = button.getAttribute('data-email') || '';
            document.getElementById('edit_secretary_phone').value = button.getAttribute('data-phone') || '';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function closeEditSecretaryModal() {
            const modal = document.getElementById('editSecretaryModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        function openEditTeacherModal(button) {
            const modal = document.getElementById('editTeacherModal');
            const form = document.getElementById('editTeacherForm');
            if (!modal || !form || !button) return;

            const id = button.getAttribute('data-id') || '';
            form.action = `{{ url('/admin/employees/teachers') }}/${id}`;

            document.getElementById('edit_teacher_name').value = button.getAttribute('data-name') || '';
            document.getElementById('edit_teacher_email').value = button.getAttribute('data-email') || '';
            document.getElementById('edit_teacher_phone').value = button.getAttribute('data-phone') || '';
            document.getElementById('edit_teacher_language').value = button.getAttribute('data-preferred-language') || '';
            document.getElementById('edit_teacher_bio').value = button.getAttribute('data-bio') || '';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function closeEditTeacherModal() {
            const modal = document.getElementById('editTeacherModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
</x-layouts.admin>
