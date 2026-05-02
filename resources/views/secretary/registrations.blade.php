<x-layouts.secretary :title="__('Registrations')" :current-route="'secretary.registrations'">
    <style>
        .secretary-role-option {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dbe3df;
            border-radius: 0.75rem;
            padding: 0.7rem;
            cursor: pointer;
            transition: all .18s ease;
            background: #ffffff;
        }

        .secretary-role-option:hover {
            border-color: rgba(45, 140, 94, 0.6);
            background: rgba(45, 140, 94, 0.05);
        }

        .secretary-role-option.selected {
            border-color: #2D8C5E;
            background: rgba(45, 140, 94, 0.1);
            box-shadow: 0 0 0 2px rgba(45, 140, 94, 0.18);
        }

        .secretary-role-option input[type="radio"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }
    </style>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="inline-flex rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-wide" style="background: #ECFDF3; color: #166534;">
                Internal Registration Desk
            </p>
            <h1 class="mt-3 text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
                Registrations
            </h1>
            <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
                Familiar register flow inside secretary account. New accounts are created and then sent to approvals queue.
            </p>
        </div>

        <a
            href="{{ route('approvals.index', ['role' => 'secretary']) }}"
            class="inline-flex items-center rounded-xl border px-4 py-2 text-sm font-semibold transition hover:bg-gray-50"
            style="border-color: var(--lumina-border); color: var(--lumina-text-primary);"
        >
            Open Approvals Queue ({{ $pendingCount }})
        </a>
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

    <section class="overflow-hidden rounded-3xl border" style="background: white; border-color: var(--lumina-border-light);">
        <div class="grid gap-0 lg:grid-cols-5">
            <div class="border-b p-6 lg:col-span-2 lg:border-b-0 lg:border-r" style="background: #E8F9F1; border-color: var(--lumina-border-light);">
                <h2 class="text-2xl font-semibold" style="color: #1E3A2F;">Create Account</h2>
                <p class="mt-2 text-sm" style="color: #3F4941;">
                    Use the same fields as public registration. The created account remains pending until approved.
                </p>

                <div class="mt-6 space-y-3">
                    <div class="rounded-xl border bg-white p-3" style="border-color: #CFE9DD;">
                        <p class="text-xs font-semibold uppercase" style="color: #446651;">Step 1</p>
                        <p class="mt-1 text-sm" style="color: #1E3A2F;">Enter personal info and set role.</p>
                    </div>
                    <div class="rounded-xl border bg-white p-3" style="border-color: #CFE9DD;">
                        <p class="text-xs font-semibold uppercase" style="color: #446651;">Step 2</p>
                        <p class="mt-1 text-sm" style="color: #1E3A2F;">Create account with temporary password.</p>
                    </div>
                    <div class="rounded-xl border bg-white p-3" style="border-color: #CFE9DD;">
                        <p class="text-xs font-semibold uppercase" style="color: #446651;">Step 3</p>
                        <p class="mt-1 text-sm" style="color: #1E3A2F;">Approve or reject from queue.</p>
                    </div>
                </div>
            </div>

            <div class="p-6 lg:col-span-3">
                <form method="POST" action="{{ route('secretary.registrations.store') }}" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="name" class="mb-2 block text-xs font-semibold uppercase tracking-wide" style="color: var(--lumina-text-secondary);">Full Name</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2" style="color: #94A3B8;">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" required class="w-full rounded-xl border py-2.5 pl-9 pr-3 text-sm outline-none" style="border-color: var(--lumina-border); background: #F8FAFC;">
                            </div>
                        </div>

                        <div>
                            <label for="email" class="mb-2 block text-xs font-semibold uppercase tracking-wide" style="color: var(--lumina-text-secondary);">Email Address</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2" style="color: #94A3B8;">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </span>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-xl border py-2.5 pl-9 pr-3 text-sm outline-none" style="border-color: var(--lumina-border); background: #F8FAFC;">
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="password" class="mb-2 block text-xs font-semibold uppercase tracking-wide" style="color: var(--lumina-text-secondary);">Password</label>
                            <div class="relative">
                                <input id="password" name="password" type="password" required class="w-full rounded-xl border py-2.5 pl-3 pr-10 text-sm outline-none" style="border-color: var(--lumina-border); background: #F8FAFC;">
                                <button
                                    type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2"
                                    style="color: #64748B;"
                                    aria-label="Show password"
                                    title="Show password"
                                    onclick="toggleSecretaryRegistrationPassword('password', 'secretaryPasswordEyeIcon', this)"
                                >
                                    <svg id="secretaryPasswordEyeIcon" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="password_confirmation" class="mb-2 block text-xs font-semibold uppercase tracking-wide" style="color: var(--lumina-text-secondary);">Confirm Password</label>
                            <div class="relative">
                                <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full rounded-xl border py-2.5 pl-3 pr-10 text-sm outline-none" style="border-color: var(--lumina-border); background: #F8FAFC;">
                                <button
                                    type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2"
                                    style="color: #64748B;"
                                    aria-label="Show password"
                                    title="Show password"
                                    onclick="toggleSecretaryRegistrationPassword('password_confirmation', 'secretaryPasswordConfirmationEyeIcon', this)"
                                >
                                    <svg id="secretaryPasswordConfirmationEyeIcon" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-wide" style="color: var(--lumina-text-secondary);">Select Role</label>
                        <div id="secretaryRoleGroup" class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            <label class="secretary-role-option{{ old('requested_role', 'student') === 'student' ? ' selected' : '' }}">
                                <input type="radio" name="requested_role" value="student"{{ old('requested_role', 'student') === 'student' ? ' checked' : '' }}>
                                <span class="text-sm font-medium" style="color: #334155;">Student</span>
                            </label>
                            <label class="secretary-role-option{{ old('requested_role') === 'teacher' ? ' selected' : '' }}">
                                <input type="radio" name="requested_role" value="teacher"{{ old('requested_role') === 'teacher' ? ' checked' : '' }}>
                                <span class="text-sm font-medium" style="color: #334155;">Teacher</span>
                            </label>
                            <label class="secretary-role-option{{ old('requested_role') === 'parent' ? ' selected' : '' }}">
                                <input type="radio" name="requested_role" value="parent"{{ old('requested_role') === 'parent' ? ' checked' : '' }}>
                                <span class="text-sm font-medium" style="color: #334155;">Parent</span>
                            </label>
                            <label class="secretary-role-option{{ old('requested_role') === 'secretary' ? ' selected' : '' }}">
                                <input type="radio" name="requested_role" value="secretary"{{ old('requested_role') === 'secretary' ? ' checked' : '' }}>
                                <span class="text-sm font-medium" style="color: #334155;">Secretary</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label for="date_of_birth" class="mb-2 block text-xs font-semibold uppercase tracking-wide" style="color: var(--lumina-text-secondary);">Date of Birth</label>
                            <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth') }}" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none" style="border-color: var(--lumina-border); background: #F8FAFC;">
                        </div>

                        <div id="secretaryParentEmailField">
                            <label for="parent_email" class="mb-2 block text-xs font-semibold uppercase tracking-wide" style="color: var(--lumina-text-secondary);">Parent Email (Under 18 Student)</label>
                            <input id="parent_email" name="parent_email" type="email" value="{{ old('parent_email') }}" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none" style="border-color: var(--lumina-border); background: #F8FAFC;">
                        </div>
                    </div>

                    <div id="secretaryProgramField">
                        <label for="secretary_program_id" class="mb-2 block text-xs font-semibold uppercase tracking-wide" style="color: var(--lumina-text-secondary);">Program Selection</label>
                        <select id="secretary_program_id" name="program_id" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none" style="border-color: var(--lumina-border); background: #F8FAFC;">
                            <option value="">Select a program</option>
                            @foreach ($availablePrograms as $program)
                                <option value="{{ $program->id }}" @selected((string) old('program_id') === (string) $program->id)>
                                    {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs" style="color: var(--lumina-text-secondary);">
                            Choose the language program first, then the student course.
                        </p>
                    </div>

                    <div id="secretaryCourseField">
                        <label for="secretary_course_id" class="mb-2 block text-xs font-semibold uppercase tracking-wide" style="color: var(--lumina-text-secondary);">Course Selection</label>
                        <select id="secretary_course_id" name="course_id" data-selected-course="{{ old('course_id') }}" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none" style="border-color: var(--lumina-border); background: #F8FAFC;">
                            <option value="">Select a program first</option>
                        </select>
                        <p class="mt-2 text-xs" style="color: var(--lumina-text-secondary);">
                            Required for student registrations only. The selected course will be copied into the student payment workflow after approval.
                        </p>
                    </div>

                    <div id="secretaryRegistrationDocumentField">
                        <label id="secretaryRegistrationDocumentLabel" for="secretary_registration_document" class="mb-2 block text-xs font-semibold uppercase tracking-wide" style="color: var(--lumina-text-secondary);">Registration Document</label>
                        <input id="secretary_registration_document" name="registration_document" type="file" class="w-full rounded-xl border px-3 py-2.5 text-sm outline-none file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:font-medium file:text-emerald-700" style="border-color: var(--lumina-border); background: #F8FAFC;">
                        <p id="secretaryRegistrationDocumentHint" class="mt-2 text-xs" style="color: var(--lumina-text-secondary);">
                            Upload the required file for the selected role.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 rounded-xl border px-4 py-3 sm:flex-row sm:items-center sm:justify-between" style="border-color: #BFDBFE; background: #EFF6FF;">
                        <p class="text-xs" style="color: #1E3A8A;">
                            This creates a pending account. Approval is still required before portal access.
                        </p>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl px-5 py-2.5 text-sm font-semibold text-white transition-all hover:opacity-90" style="background-color: var(--lumina-primary);">
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script id="secretaryRegistrationCoursesData" type="application/json">
        @json(($availableCourses ?? collect())->values()->toArray())
    </script>

    <script>
        function toggleSecretaryRegistrationPassword(inputId, iconId, button) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (!input || !icon) {
                return;
            }

            if (input.type === 'password') {
                input.type = 'text';
                button?.setAttribute('aria-label', 'Hide password');
                button?.setAttribute('title', 'Hide password');
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';

                return;
            }

            input.type = 'password';
            button?.setAttribute('aria-label', 'Show password');
            button?.setAttribute('title', 'Show password');
            icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
        }

        (function () {
            const group = document.getElementById('secretaryRoleGroup');
            if (!group) {
                return;
            }

            const options = group.querySelectorAll('.secretary-role-option');
            options.forEach((option) => {
                const radio = option.querySelector('input[type="radio"]');
                if (!radio) {
                    return;
                }

                option.addEventListener('click', function () {
                    options.forEach((item) => item.classList.remove('selected'));
                    this.classList.add('selected');
                });

                radio.addEventListener('change', function () {
                    options.forEach((item) => item.classList.remove('selected'));
                    option.classList.add('selected');
                    toggleSecretaryCourseField(this.value);
                });
            });

            const programSelect = document.getElementById('secretary_program_id');
            if (programSelect) {
                programSelect.addEventListener('change', function () {
                    setSecretaryCourseOptions(this.value, '');
                });
            }

            toggleSecretaryCourseField(group.querySelector('input[type="radio"]:checked')?.value ?? 'student');
        })();

        function toggleSecretaryCourseField(role) {
            const parentEmailField = document.getElementById('secretaryParentEmailField');
            const parentEmailInput = document.getElementById('parent_email');
            const programField = document.getElementById('secretaryProgramField');
            const programSelect = document.getElementById('secretary_program_id');
            const courseField = document.getElementById('secretaryCourseField');
            const courseSelect = document.getElementById('secretary_course_id');
            const documentField = document.getElementById('secretaryRegistrationDocumentField');
            const documentInput = document.getElementById('secretary_registration_document');
            const documentLabel = document.getElementById('secretaryRegistrationDocumentLabel');
            const documentHint = document.getElementById('secretaryRegistrationDocumentHint');

            if (!parentEmailField || !parentEmailInput || !programField || !programSelect || !courseField || !courseSelect || !documentField || !documentInput || !documentLabel || !documentHint) {
                return;
            }

            const isStudent = role === 'student';
            const selectedCourseId = courseSelect.dataset.selectedCourse ?? '';
            const documentConfig = {
                student: {
                    label: 'Upload Birth Certificate',
                    hint: 'Upload the student birth certificate as PDF, JPG, or PNG.',
                    accept: '.pdf,.jpg,.jpeg,.png',
                },
                teacher: {
                    label: 'Upload C.V',
                    hint: 'Upload the teacher C.V as PDF or Word document.',
                    accept: '.pdf,.doc,.docx',
                },
                secretary: {
                    label: 'Upload C.V',
                    hint: 'Upload the secretary C.V as PDF or Word document.',
                    accept: '.pdf,.doc,.docx',
                },
            };
            const activeDocumentConfig = documentConfig[role] ?? null;

            parentEmailField.style.display = isStudent ? 'block' : 'none';
            parentEmailInput.disabled = !isStudent;
            programField.style.display = isStudent ? 'block' : 'none';
            courseField.style.display = isStudent ? 'block' : 'none';
            programSelect.disabled = !isStudent;
            documentField.style.display = activeDocumentConfig ? 'block' : 'none';
            documentInput.disabled = !activeDocumentConfig;

            if (!isStudent) {
                parentEmailInput.value = '';
                programSelect.value = '';
                setSecretaryCourseOptions('', selectedCourseId);
                courseSelect.disabled = true;
            }

            if (activeDocumentConfig) {
                documentInput.value = '';
                documentLabel.textContent = activeDocumentConfig.label;
                documentHint.textContent = activeDocumentConfig.hint;
                documentInput.setAttribute('accept', activeDocumentConfig.accept);
            } else {
                documentInput.value = '';
                documentInput.removeAttribute('accept');
            }

            if (isStudent) {
                setSecretaryCourseOptions(programSelect.value, selectedCourseId);
            }
        }

        function setSecretaryCourseOptions(programId, selectedCourseId) {
            const select = document.getElementById('secretary_course_id');
            if (!select) {
                return;
            }

            const courses = JSON.parse(document.getElementById('secretaryRegistrationCoursesData')?.textContent ?? '[]');
            const filteredCourses = courses.filter((course) => String(course.program_id) === String(programId));
            const placeholder = !programId
                ? 'Select a program first'
                : filteredCourses.length === 0
                    ? 'No available courses for this program'
                    : 'Select a course';

            select.innerHTML = '';
            select.appendChild(new Option(placeholder, ''));

            filteredCourses.forEach((course) => {
                const option = new Option(`${course.name} (${Number(course.price).toLocaleString()} DA)`, String(course.id));
                select.appendChild(option);
            });

            const courseExists = filteredCourses.some((course) => String(course.id) === String(selectedCourseId));
            select.value = courseExists ? String(selectedCourseId) : '';
            select.disabled = filteredCourses.length === 0;
            select.dataset.selectedCourse = select.value;
        }
    </script>
</x-layouts.secretary>
