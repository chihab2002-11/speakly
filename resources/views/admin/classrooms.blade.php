<x-layouts.admin :title="__('Manage Classrooms')" :user="auth()->user()" :current-route="'admin.classrooms.index'">
    <div class="mx-auto w-full max-w-7xl space-y-8">
        <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
            <div class="max-w-2xl">
                <h1 class="text-4xl font-extrabold tracking-tight md:text-5xl" style="color: #181D19; letter-spacing: -0.9px;">Manage Classrooms</h1>
                <p class="mt-3 text-lg leading-8" style="color: #3F4941;">
                    Manage and allocate learning spaces used in schedule planning.
                </p>
            </div>

            <button
                type="button"
                onclick="openCreateClassroomModal()"
                class="inline-flex h-12 items-center justify-center gap-3 rounded-xl px-6 py-4 text-center text-[16px] font-bold text-white shadow-sm transition hover:opacity-90"
                style="background-color: #2D8C5E;"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Add New Classroom</span>
            </button>
        </div>

        @if(session('success'))
            <div class="rounded-xl border px-4 py-3 text-sm font-semibold" style="background-color: #D1FAE5; border-color: #A7F3D0; color: #065F46;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-xl border px-4 py-3 text-sm font-semibold" style="background-color: #FEF2F2; border-color: #FECACA; color: #991B1B;">
                {{ session('error') }}
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

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-xl p-6" style="background: #FFFFFF; box-shadow: 0px 20px 40px -20px rgba(26, 27, 34, 0.06);">
                <p class="text-xs font-bold uppercase tracking-[1.2px]" style="color: #94A3B8;">Total Spaces</p>
                <p class="mt-2 text-5xl font-black" style="color: #2D8C5E;">{{ $totalClassrooms }}</p>
            </article>

            <article class="rounded-xl border-l-4 p-6" style="border-left-color: #10B981; background: #FFFFFF; box-shadow: 0px 20px 40px -20px rgba(26, 27, 34, 0.06);">
                <p class="text-xs font-bold uppercase tracking-[1.2px]" style="color: #94A3B8;">Currently Active</p>
                <p class="mt-2 text-5xl font-black" style="color: #1A1B22;">{{ $activeClassrooms }}</p>
            </article>

            <article class="rounded-xl p-6" style="background: #FFFFFF; box-shadow: 0px 20px 40px -20px rgba(26, 27, 34, 0.06);">
                <p class="text-xs font-bold uppercase tracking-[1.2px]" style="color: #94A3B8;">Unscheduled</p>
                <p class="mt-2 text-5xl font-black" style="color: #1A1B22;">{{ $unusedClassrooms }}</p>
            </article>

            <article class="relative overflow-hidden rounded-xl p-6" style="background: #2D8C5E; box-shadow: 0px 20px 40px -20px rgba(26, 27, 34, 0.06);">
                <p class="text-xs font-bold uppercase tracking-[1.2px]" style="color: #DDE1FF;">System Health</p>
                <p class="mt-2 text-5xl font-black text-white">99.9%</p>
            </article>
        </div>

        <section class="overflow-hidden rounded-2xl" style="background: #FFFFFF; box-shadow: 0px 20px 40px -20px rgba(26, 27, 34, 0.06);">
            <div class="flex flex-col gap-3 border-b px-8 py-6 md:flex-row md:items-center md:justify-between" style="background: rgba(248, 250, 252, 0.5); border-color: #E8E7F1;">
                <h2 class="text-[30px] font-bold leading-none" style="color: #1A1B22;">Active Learning Spaces</h2>
                <form method="GET" action="{{ route('admin.classrooms.index') }}" class="flex items-center gap-2">
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search learning spaces"
                        class="rounded-lg border px-4 py-2 text-sm"
                        style="border-color: rgba(196, 197, 213, 0.3);"
                    >
                    <button type="submit" class="rounded-lg border px-4 py-2 text-sm font-semibold" style="border-color: rgba(196, 197, 213, 0.3); color: #1A1B22;">Filter</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr style="background: rgba(244, 242, 252, 0.3);">
                            <th class="px-8 py-6 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Room Name</th>
                            <th class="px-8 py-6 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Current Status</th>
                            <th class="px-8 py-6 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Schedule Usage</th>
                            <th class="px-8 py-6 text-right text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rooms as $room)
                            <tr class="border-t" style="border-color: #F4F2FC;">
                                <td class="px-8 py-6">
                                    <p class="text-2xl font-bold leading-tight" style="color: #1A1B22;">{{ $room->name }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    @if($room->schedules_count > 0)
                                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold" style="background: #D1FAE5; color: #065F46;">
                                            <span class="h-1.5 w-1.5 rounded-full" style="background: #059669;"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold" style="background: #FEF3C7; color: #92400E;">
                                            <span class="h-1.5 w-1.5 rounded-full" style="background: #D97706;"></span>
                                            Idle
                                        </span>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-sm" style="color: #444653;">
                                    {{ $room->schedules_count }} slot(s)
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            onclick="openEditClassroomModal(this)"
                                            data-id="{{ $room->id }}"
                                            data-name="{{ $room->name }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-gray-100"
                                            title="Edit"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #94A3B8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <form method="POST" action="{{ route('admin.classrooms.destroy', $room) }}" onsubmit="return confirm('Delete this classroom?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-red-50" title="Delete">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #CA2121;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V4h6v3m-7 4v6m4-6v6m5 4H7a2 2 0 01-2-2V7h14v12a2 2 0 01-2 2z"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-12 text-center text-sm" style="color: #64748B;">No classrooms found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between border-t px-8 py-4" style="border-color: #F4F2FC;">
                <p class="text-sm" style="color: #444653;">Showing {{ $rooms->firstItem() ?? 0 }} to {{ $rooms->lastItem() ?? 0 }} of {{ $rooms->total() }} classrooms</p>
                {{ $rooms->links() }}
            </div>
        </section>
    </div>

    <div id="createClassroomModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" onclick="if(event.target===this){closeCreateClassroomModal()}">
        <div class="w-full max-w-2xl rounded-2xl p-6" style="background-color: #FFFFFF;" onclick="event.stopPropagation()">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-xl font-bold" style="color: #181D19;">Add New Classroom</h3>
                <button type="button" onclick="closeCreateClassroomModal()" class="rounded-lg p-2 hover:bg-gray-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748B;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.classrooms.store') }}" class="grid gap-4">
                @csrf

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Room Name</label>
                    <input name="name" required placeholder="e.g. Room 101" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;" />
                </div>

                <div class="mt-2 flex justify-end gap-3">
                    <button type="button" onclick="closeCreateClassroomModal()" class="rounded-xl border px-4 py-2.5 text-sm font-semibold" style="border-color: #E2E8F0; color: #3F4941;">Cancel</button>
                    <button type="submit" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white" style="background-color: #2D8C5E;">Create Classroom</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editClassroomModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" onclick="if(event.target===this){closeEditClassroomModal()}">
        <div class="w-full max-w-xl rounded-2xl p-6" style="background-color: #FFFFFF;" onclick="event.stopPropagation()">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-xl font-bold" style="color: #181D19;">Edit Classroom</h3>
                <button type="button" onclick="closeEditClassroomModal()" class="rounded-lg p-2 hover:bg-gray-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748B;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="editClassroomForm" method="POST" action="" class="grid gap-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Room Name</label>
                    <input id="edit_room_name" name="name" type="text" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;" />
                </div>

                <div class="mt-2 flex justify-end gap-3">
                    <button type="button" onclick="closeEditClassroomModal()" class="rounded-xl border px-4 py-2.5 text-sm font-semibold" style="border-color: #E2E8F0; color: #3F4941;">Cancel</button>
                    <button type="submit" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white" style="background-color: #2D8C5E;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateClassroomModal() {
            const modal = document.getElementById('createClassroomModal');
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeCreateClassroomModal() {
            const modal = document.getElementById('createClassroomModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openEditClassroomModal(button) {
            const modal = document.getElementById('editClassroomModal');
            const form = document.getElementById('editClassroomForm');

            if (!modal || !form || !button) return;

            const roomId = button.getAttribute('data-id');
            form.action = `{{ url('/admin/classrooms') }}/${roomId}`;

            document.getElementById('edit_room_name').value = button.getAttribute('data-name') || '';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEditClassroomModal() {
            const modal = document.getElementById('editClassroomModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
</x-layouts.admin>
