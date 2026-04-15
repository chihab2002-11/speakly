<x-layouts.secretary :title="__('Manage Accounts')" :current-route="'secretary.accounts'">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
            Manage Accounts
        </h1>
        <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
            Monitor student and parent account lifecycle across approved, pending, and rejected states.
        </p>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm font-semibold" style="background-color: #D1FAE5; border-color: #A7F3D0; color: #065F46;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-xl border px-4 py-3" style="background-color: #FEF2F2; border-color: #FECACA; color: #991B1B;">
            <ul class="list-disc space-y-1 pl-5 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="mb-6 grid gap-4 lg:grid-cols-4">
        <article class="relative overflow-hidden rounded-2xl p-5 text-white" style="background: #2D8C5E; box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 150px;">
            <p class="text-xs font-bold uppercase tracking-[1.2px]">Managed Accounts</p>
            <p class="mt-3 text-4xl font-black leading-none">{{ $totalManagedAccounts }}</p>
            <p class="mt-4 text-sm font-semibold">Student, parent, teacher lifecycle</p>
            <div class="pointer-events-none absolute -bottom-5 right-3 h-14 w-14 rounded-full border border-white/30"></div>
        </article>

        <article class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 150px;">
            <p class="text-xs font-bold uppercase tracking-[1.2px]" style="color: #525C87;">Approved</p>
            <p class="mt-3 text-4xl font-extrabold" style="color: #15803D;">{{ $approvedAccounts }}</p>
            <p class="mt-2 text-sm" style="color: var(--lumina-text-muted);">Ready for platform access</p>
        </article>

        <article class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 150px;">
            <p class="text-xs font-bold uppercase tracking-[1.2px]" style="color: #611E00;">Pending</p>
            <p class="mt-3 text-4xl font-extrabold" style="color: #B45309;">{{ $pendingAccounts }}</p>
            <p class="mt-2 text-sm" style="color: var(--lumina-text-muted);">Awaiting approval decision</p>
        </article>

        <article class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 150px;">
            <p class="text-xs font-bold uppercase tracking-[1.2px]" style="color: #7F1D1D;">Rejected</p>
            <p class="mt-3 text-4xl font-extrabold" style="color: #B91C1C;">{{ $rejectedAccounts }}</p>
            <p class="mt-2 text-sm" style="color: var(--lumina-text-muted);">Require correction/resubmission</p>
        </article>
    </section>

    <div class="mb-4 rounded-2xl border p-4" style="background: white; border-color: var(--lumina-border-light);">
        <form method="GET" action="{{ route('secretary.accounts') }}" class="grid gap-3 md:grid-cols-4">
            <div>
                <label for="search" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Search</label>
                <input
                    id="search"
                    name="search"
                    value="{{ $search }}"
                    type="text"
                    placeholder="Name or email"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none"
                    style="border-color: var(--lumina-border); background: #F8FAFC;"
                >
            </div>

            <div>
                <label for="role" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Role</label>
                <select
                    id="role"
                    name="role"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none"
                    style="border-color: var(--lumina-border); background: #F8FAFC;"
                >
                    <option value="all" @selected($role === 'all')>All</option>
                    <option value="student" @selected($role === 'student')>Student</option>
                    <option value="parent" @selected($role === 'parent')>Parent</option>
                    <option value="teacher" @selected($role === 'teacher')>Teacher</option>
                </select>
            </div>

            <div>
                <label for="status" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Status</label>
                <select
                    id="status"
                    name="status"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none"
                    style="border-color: var(--lumina-border); background: #F8FAFC;"
                >
                    <option value="all" @selected($status === 'all')>All</option>
                    <option value="approved" @selected($status === 'approved')>Approved</option>
                    <option value="pending" @selected($status === 'pending')>Pending</option>
                    <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button
                    type="submit"
                    class="rounded-lg px-4 py-2 text-sm font-semibold text-white"
                    style="background-color: var(--lumina-primary);"
                >
                    Apply
                </button>
                <a
                    href="{{ route('secretary.accounts') }}"
                    class="rounded-lg border px-4 py-2 text-sm font-semibold"
                    style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);"
                >
                    Reset
                </a>
            </div>
        </form>
    </div>

    <section class="overflow-hidden rounded-3xl border" style="background: white; border-color: var(--lumina-border-light);">
        @if($accounts->isEmpty())
            <div class="p-12 text-center">
                <p class="text-lg font-semibold" style="color: var(--lumina-text-primary);">No accounts found</p>
                <p class="mt-2 text-sm" style="color: var(--lumina-text-muted);">No entries match the current filter.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead style="background-color: #F8FAFC;">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-muted);">Account</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-muted);">Role</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-muted);">Status</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-muted);">Created</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-muted);">Decision Details</th>
                            <th class="px-4 py-3 text-right font-semibold" style="color: var(--lumina-text-muted);">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($accounts as $account)
                            @php
                                $resolvedRole = $account->requested_role ?: $account->roles->pluck('name')->first();
                                $resolvedStatus = $account->approved_at
                                    ? 'approved'
                                    : ($account->rejected_at ? 'rejected' : 'pending');
                            @endphp
                            <tr class="border-t" style="border-color: var(--lumina-border);">
                                <td class="px-4 py-4">
                                    <p class="font-semibold" style="color: var(--lumina-text-primary);">{{ $account->name }}</p>
                                    <p class="text-xs" style="color: var(--lumina-text-muted);">{{ $account->email }}</p>
                                </td>
                                <td class="px-4 py-4" style="color: var(--lumina-text-secondary);">{{ ucfirst((string) $resolvedRole) }}</td>
                                <td class="px-4 py-4">
                                    @if($resolvedStatus === 'approved')
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" style="background: #DCFCE7; color: #166534;">Approved</span>
                                    @elseif($resolvedStatus === 'rejected')
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" style="background: #FEE2E2; color: #991B1B;">Rejected</span>
                                    @else
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" style="background: #FEF3C7; color: #92400E;">Pending</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4" style="color: var(--lumina-text-secondary);">
                                    {{ $account->created_at?->format('M j, Y') }}
                                </td>
                                <td class="px-4 py-4 text-xs" style="color: var(--lumina-text-muted);">
                                    @if($account->approved_at)
                                        Approved {{ $account->approved_at->diffForHumans() }}
                                    @elseif($account->rejected_at)
                                        Rejected {{ $account->rejected_at->diffForHumans() }}
                                        @if($account->rejection_reason)
                                            <div class="mt-1">Reason: {{ $account->rejection_reason }}</div>
                                        @endif
                                    @else
                                        Awaiting review
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            type="button"
                                            onclick="openEditAccountModal(this)"
                                            data-id="{{ $account->id }}"
                                            data-name="{{ $account->name }}"
                                            data-email="{{ $account->email }}"
                                            data-role="{{ $resolvedRole }}"
                                            data-date-of-birth="{{ optional($account->date_of_birth)->format('Y-m-d') }}"
                                            class="rounded-lg border px-3 py-1.5 text-xs font-semibold"
                                            style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);"
                                        >
                                            Edit
                                        </button>

                                        <form method="POST" action="{{ route('secretary.accounts.destroy', $account) }}" onsubmit="return confirm('Delete this account?');">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="rounded-lg border px-3 py-1.5 text-xs font-semibold"
                                                style="border-color: #FECACA; color: #991B1B;"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <div class="mt-6">
        {{ $accounts->links() }}
    </div>

    <div id="editAccountModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" onclick="if(event.target===this){closeEditAccountModal()}">
        <div class="w-full max-w-xl rounded-2xl p-6" style="background-color: #FFFFFF;" onclick="event.stopPropagation()">
            <div class="mb-5 flex items-center justify-between">
                <h3 class="text-xl font-bold" style="color: var(--lumina-text-primary);">Edit Account</h3>
                <button type="button" onclick="closeEditAccountModal()" class="rounded-lg p-2 hover:bg-gray-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: var(--lumina-text-muted);"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="editAccountForm" method="POST" action="" class="grid gap-4 md:grid-cols-2">
                @csrf
                @method('PATCH')

                <div>
                    <label class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Full Name</label>
                    <input id="edit_account_name" name="name" type="text" required class="w-full rounded-lg border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background: #F8FAFC;">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Email</label>
                    <input id="edit_account_email" name="email" type="email" required class="w-full rounded-lg border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background: #F8FAFC;">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Role</label>
                    <select id="edit_account_role" name="requested_role" required class="w-full rounded-lg border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background: #F8FAFC;">
                        <option value="student">Student</option>
                        <option value="parent">Parent</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Date of Birth</label>
                    <input id="edit_account_dob" name="date_of_birth" type="date" class="w-full rounded-lg border px-3 py-2 text-sm" style="border-color: var(--lumina-border); background: #F8FAFC;">
                </div>

                <div class="md:col-span-2 mt-2 flex justify-end gap-3">
                    <button type="button" onclick="closeEditAccountModal()" class="rounded-lg border px-4 py-2 text-sm font-semibold" style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);">Cancel</button>
                    <button type="submit" class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background-color: var(--lumina-primary);">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditAccountModal(button) {
            const modal = document.getElementById('editAccountModal');
            const form = document.getElementById('editAccountForm');

            if (!modal || !form || !button) {
                return;
            }

            const accountId = button.getAttribute('data-id') || '';
            const name = button.getAttribute('data-name') || '';
            const email = button.getAttribute('data-email') || '';
            const role = button.getAttribute('data-role') || 'student';
            const dateOfBirth = button.getAttribute('data-date-of-birth') || '';

            form.action = `{{ url('/secretary/accounts') }}/${accountId}`;

            const nameField = document.getElementById('edit_account_name');
            const emailField = document.getElementById('edit_account_email');
            const roleField = document.getElementById('edit_account_role');
            const dobField = document.getElementById('edit_account_dob');

            if (nameField) nameField.value = name;
            if (emailField) emailField.value = email;
            if (roleField) roleField.value = ['student', 'parent', 'teacher'].includes(role) ? role : 'student';
            if (dobField) dobField.value = dateOfBirth;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEditAccountModal() {
            const modal = document.getElementById('editAccountModal');
            if (!modal) {
                return;
            }

            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
</x-layouts.secretary>
