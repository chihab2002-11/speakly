<x-layouts.admin :title="__('Manage Courses')" :user="auth()->user()" :current-route="'admin.courses.index'">
    <div class="mx-auto w-full max-w-7xl space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight md:text-5xl" style="color: #181D19; letter-spacing: -0.9px;">Manage Courses</h1>
                <p class="mt-3 text-lg leading-8" style="color: #3F4941;">
                    Create and maintain course catalog entries used for timetable and class planning.
                </p>
            </div>
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

        <section class="grid gap-4 lg:grid-cols-4">
            <article class="relative overflow-hidden rounded-2xl p-5 text-white" style="background: #2D8C5E; box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 160px;">
                <p class="text-xs font-bold uppercase tracking-[1.2px]">Total Courses</p>
                <p class="mt-3 text-4xl font-black leading-none">{{ $totalCourses }}</p>
                <p class="mt-5 text-sm font-semibold">Catalog entries available</p>
                <div class="pointer-events-none absolute -bottom-5 right-3 h-14 w-14 rounded-full border border-white/30"></div>
            </article>

            <article class="rounded-2xl border p-5" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 160px;">
                <p class="text-xs font-bold uppercase tracking-[1.2px]" style="color: #525C87;">Total Groups</p>
                <p class="mt-3 text-4xl font-extrabold" style="color: #1A1B22;">{{ $totalClasses }}</p>
                <p class="mt-2 text-sm" style="color: #444653;">Classes linked to courses</p>
            </article>

            <article class="rounded-2xl border p-5" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 160px;">
                <p class="text-xs font-bold uppercase tracking-[1.2px]" style="color: #611E00;">Tuition Sum</p>
                <p class="mt-3 text-3xl font-extrabold" style="color: #1A1B22;">{{ number_format($totalTuition) }} DZD</p>
                <p class="mt-2 text-sm" style="color: #444653;">Combined listed course price</p>
            </article>

            <article class="rounded-2xl border p-5" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 160px;">
                <p class="text-xs font-bold uppercase tracking-[1.2px]" style="color: #1E3A8A;">Average Price</p>
                <p class="mt-3 text-3xl font-extrabold" style="color: #1A1B22;">{{ number_format($avgCoursePrice) }} DZD</p>
                <p class="mt-2 text-sm" style="color: #444653;">Average course tuition</p>
            </article>
        </section>

        <section class="rounded-2xl border p-5" style="background-color: #FFFFFF; border-color: #F1F5F9; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);">
            <h2 class="mb-4 text-xl font-bold" style="color: #181D19;">Add Course</h2>

            <form method="POST" action="{{ route('admin.courses.store') }}" class="grid gap-4 md:grid-cols-5">
                @csrf

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Course Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;" placeholder="English Communication A1">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Price (DZD)</label>
                    <input type="number" min="1" step="1" name="price" value="{{ old('price') }}" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;" placeholder="12000">
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Description</label>
                    <input type="text" name="description" value="{{ old('description') }}" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;" placeholder="Beginner communication course">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Program</label>
                    <select name="program_id" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;">
                        <option value="">No program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" @selected((string) old('program_id') === (string) $program->id)>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <button type="submit" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white" style="background-color: #2D8C5E;">
                        Create Course
                    </button>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border" style="background-color: #FFFFFF; border-color: #F1F5F9; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);">
            <div class="flex items-center justify-between border-b px-6 py-5" style="border-color: #F1F5F9;">
                <h2 class="text-[20px] font-bold" style="color: #181D19;">Courses</h2>
                <span class="text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">{{ $courses->count() }} Course(s)</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr style="background-color: #F0F5EE;">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">Code</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">Program</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">Price</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">Description</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">Classes</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-[1.2px]" style="color: #3F4941;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses as $course)
                            <tr class="border-t" style="border-color: #F1F5F9;">
                                <td class="px-6 py-5 text-sm font-semibold" style="color: #181D19;">{{ $course->name }}</td>
                                <td class="px-6 py-5 text-sm font-mono" style="color: #3F4941;">{{ $course->code }}</td>
                                <td class="px-6 py-5 text-sm" style="color: #3F4941;">{{ $course->program?->name ?? 'Unassigned' }}</td>
                                <td class="px-6 py-5 text-sm font-semibold" style="color: #181D19;">{{ number_format($course->price) }} DZD</td>
                                <td class="px-6 py-5 text-sm" style="color: #3F4941;">{{ $course->description ?: 'No description' }}</td>
                                <td class="px-6 py-5 text-sm" style="color: #3F4941;">{{ $course->classes_count }}</td>
                                <td class="px-6 py-5">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            onclick="openEditCourseModal(this)"
                                            data-id="{{ $course->id }}"
                                            data-name="{{ $course->name }}"
                                            data-price="{{ $course->price }}"
                                            data-program-id="{{ $course->program_id }}"
                                            data-description="{{ $course->description }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-gray-100"
                                            title="Edit"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #94A3B8;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" onsubmit="return confirm('Delete this course?')">
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
                                <td colspan="7" class="px-6 py-12 text-center text-sm" style="color: #64748B;">No courses yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div id="editCourseModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" onclick="if(event.target===this){closeEditCourseModal()}">
        <div class="w-full max-w-xl rounded-2xl p-6" style="background-color: #FFFFFF;" onclick="event.stopPropagation()">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-xl font-bold" style="color: #181D19;">Edit Course</h3>
                <button type="button" onclick="closeEditCourseModal()" class="rounded-lg p-2 hover:bg-gray-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #64748B;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="editCourseForm" method="POST" action="" class="grid gap-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Course Name</label>
                    <input id="edit_course_name" name="name" type="text" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Price (DZD)</label>
                    <input id="edit_course_price" name="price" type="number" min="1" step="1" required class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Program</label>
                    <select id="edit_course_program_id" name="program_id" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;">
                        <option value="">No program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase" style="color: #3F4941;">Description</label>
                    <textarea id="edit_course_description" name="description" rows="3" class="w-full rounded-xl border px-4 py-2.5 text-sm" style="border-color: #E2E8F0;"></textarea>
                </div>

                <div class="mt-2 flex justify-end gap-3">
                    <button type="button" onclick="closeEditCourseModal()" class="rounded-xl border px-4 py-2.5 text-sm font-semibold" style="border-color: #E2E8F0; color: #3F4941;">Cancel</button>
                    <button type="submit" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white" style="background-color: #2D8C5E;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditCourseModal(button) {
            const modal = document.getElementById('editCourseModal');
            const form = document.getElementById('editCourseForm');

            if (!modal || !form || !button) {
                return;
            }

            const courseId = button.getAttribute('data-id');
            form.action = `{{ url('/admin/courses') }}/${courseId}`;

            document.getElementById('edit_course_name').value = button.getAttribute('data-name') || '';
            document.getElementById('edit_course_price').value = button.getAttribute('data-price') || '0';
            document.getElementById('edit_course_program_id').value = button.getAttribute('data-program-id') || '';
            document.getElementById('edit_course_description').value = button.getAttribute('data-description') || '';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEditCourseModal() {
            const modal = document.getElementById('editCourseModal');
            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
</x-layouts.admin>
