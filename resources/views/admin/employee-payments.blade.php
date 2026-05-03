<div>
    <!-- The best way to take care of the future is to take care of the present moment. - Thich Nhat Hanh -->
</div>
<x-layouts.admin :title="__('Employee Payments')" :user="auth()->user()" :current-route="'admin.employee-payments.index'">
    <div class="mx-auto w-full max-w-7xl space-y-8">
        <section class="space-y-3">
            <h1 class="text-4xl font-extrabold tracking-tight md:text-5xl" style="color: #1A1B22; letter-spacing: -1.2px;">Employee payments</h1>
            <p class="max-w-3xl text-base leading-7" style="color: #444653;">
                Track expected salary, total paid amount, remaining balance, and payment status for teachers and secretaries.
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

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <article class="relative overflow-hidden rounded-2xl p-6 text-white" style="background: #2D8C5E; box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 158px;">
                <p class="text-xs font-bold uppercase tracking-[1.2px]">Total Employees</p>
                <p class="mt-3 text-5xl font-black leading-none">{{ $totals['total_employees'] }}</p>
                <p class="mt-5 text-sm font-semibold">Teachers and secretaries</p>
                <div class="pointer-events-none absolute -bottom-6 right-3 h-16 w-16 rounded-full border border-white/30"></div>
                <div class="pointer-events-none absolute -bottom-3 right-20 h-9 w-9 rounded-full bg-white/15"></div>
            </article>

            <article class="rounded-2xl border p-5" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 158px;">
                <p class="text-sm" style="color: #444653;">Total Salaries</p>
                <p class="mt-2 text-3xl font-extrabold" style="color: #1A1B22;">{{ number_format($totals['total_salaries']) }} DA</p>
                <p class="mt-3 text-sm" style="color: #64748B;">Expected payroll amount</p>
            </article>

            <article class="rounded-2xl border p-5" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 158px;">
                <p class="text-sm" style="color: #444653;">Total Paid</p>
                <p class="mt-2 text-3xl font-extrabold" style="color: #15803D;">{{ number_format($totals['total_paid']) }} DA</p>
                <p class="mt-3 text-sm" style="color: #64748B;">Recorded salary payments</p>
            </article>

            <article class="rounded-2xl border p-5" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 158px;">
                <p class="text-sm" style="color: #444653;">Total Remaining</p>
                <p class="mt-2 text-3xl font-extrabold" style="color: #B45309;">{{ number_format($totals['total_remaining']) }} DA</p>
                <p class="mt-3 text-sm" style="color: #64748B;">Never below zero</p>
            </article>

            <article class="rounded-2xl border p-5" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 158px;">
                <p class="text-sm" style="color: #444653;">Status Count</p>
                <p class="mt-2 text-lg font-extrabold" style="color: #1A1B22;">{{ $totals['count_paid'] }} paid / {{ $totals['count_partial'] }} partial</p>
                <p class="mt-3 text-sm" style="color: #64748B;">{{ $totals['count_unpaid'] }} unpaid / {{ $totals['count_pending'] }} pending</p>
            </article>
        </section>

        <section class="rounded-2xl border p-4" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.12); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04);">
            <form method="GET" action="{{ route('admin.employee-payments.index') }}" class="grid gap-3 md:grid-cols-4">
                <div>
                    <label for="search" class="mb-1 block text-xs font-bold uppercase tracking-[1px]" style="color: #444653;">Search</label>
                    <input id="search" name="search" value="{{ $search }}" type="text" placeholder="Name, email, or phone" class="w-full rounded-lg border px-3 py-2 text-sm" style="background: #F8FAFC; border-color: rgba(196, 197, 213, 0.3);">
                </div>

                <div>
                    <label for="role" class="mb-1 block text-xs font-bold uppercase tracking-[1px]" style="color: #444653;">Role</label>
                    <select id="role" name="role" class="w-full rounded-lg border px-3 py-2 text-sm" style="background: #F8FAFC; border-color: rgba(196, 197, 213, 0.3);">
                        <option value="all" @selected($role === 'all')>All</option>
                        <option value="teacher" @selected($role === 'teacher')>Teachers</option>
                        <option value="secretary" @selected($role === 'secretary')>Secretaries</option>
                    </select>
                </div>

                <div>
                    <label for="status" class="mb-1 block text-xs font-bold uppercase tracking-[1px]" style="color: #444653;">Status</label>
                    <select id="status" name="status" class="w-full rounded-lg border px-3 py-2 text-sm" style="background: #F8FAFC; border-color: rgba(196, 197, 213, 0.3);">
                        <option value="all" @selected($status === 'all')>All</option>
                        <option value="paid" @selected($status === 'paid')>Paid</option>
                        <option value="partial" @selected($status === 'partial')>Partial</option>
                        <option value="unpaid" @selected($status === 'unpaid')>Unpaid</option>
                        <option value="pending" @selected($status === 'pending')>Pending setup</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-lg px-4 py-2 text-sm font-bold text-white" style="background: #2D8C5E;">Apply</button>
                    <a href="{{ route('admin.employee-payments.index') }}" class="rounded-lg border px-4 py-2 text-sm font-semibold" style="border-color: rgba(196, 197, 213, 0.3); color: #444653;">Reset</a>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl" style="background: #FFFFFF; box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04);">
            @if($rows->isEmpty())
                <div class="p-12 text-center">
                    <p class="text-lg font-semibold" style="color: #1A1B22;">No employee payments found</p>
                    <p class="mt-2 text-sm" style="color: #64748B;">Try changing your search or filter criteria.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr style="background: #F4F2FC;">
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Employee</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Role</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Expected Salary</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Add Payment</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Remaining</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Status</th>
                                <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Notes</th>
                                <th class="px-4 py-4 text-right text-xs font-bold uppercase tracking-[1.2px]" style="color: #444653;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                @php
                                    $employee = $row['employee'];
                                    $formId = 'employee-payment-'.$employee->id;
                                    $statusStyles = [
                                        'paid' => 'background: #DCFCE7; color: #166534;',
                                        'partial' => 'background: #FEF3C7; color: #92400E;',
                                        'unpaid' => 'background: #FEE2E2; color: #991B1B;',
                                        'pending' => 'background: #E0E7FF; color: #3730A3;',
                                    ];
                                @endphp
                                <tr class="border-t" style="border-color: rgba(196, 197, 213, 0.1);">
                                    <td class="px-4 py-5">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold" style="background: #DBEAFE; color: #2D8C5E;">{{ $employee->initials() }}</span>
                                            <div>
                                                <p class="font-bold leading-5" style="color: #1A1B22;">{{ $employee->name }}</p>
                                                <p class="mt-1 text-xs" style="color: #64748B;">{{ $employee->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-5">
                                        <span class="rounded-md px-2.5 py-1 text-xs font-bold uppercase" style="background: #64748B; color: #BDD9CE;">{{ $row['role'] }}</span>
                                    </td>
                                    <td class="px-4 py-5">
                                        <input form="{{ $formId }}" name="expected_salary" type="number" min="0" max="100000000" step="1" value="{{ $row['expected_salary'] }}" class="w-32 rounded-lg border px-3 py-2 text-sm font-semibold" style="border-color: rgba(196, 197, 213, 0.3); color: #1A1B22;">
                                        <p class="mt-1 text-xs" style="color: #64748B;">{{ number_format($row['expected_salary']) }} DA</p>
                                    </td>
                                    <td class="px-4 py-5">
                                        <input form="{{ $formId }}" name="amount_paid" type="number" min="0" max="100000000" step="1" value="0" class="w-32 rounded-lg border px-3 py-2 text-sm font-semibold" style="border-color: rgba(196, 197, 213, 0.3); color: #15803D;">
                                        <p class="mt-1 text-xs" style="color: #15803D;">Total paid: {{ number_format($row['amount_paid']) }} DA</p>
                                    </td>
                                    <td class="px-4 py-5 font-bold" style="color: #B45309;">{{ number_format($row['remaining']) }} DA</td>
                                    <td class="px-4 py-5">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold capitalize" style="{{ $statusStyles[$row['status']] ?? $statusStyles['pending'] }}">
                                            {{ $row['status'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-5">
                                        <input form="{{ $formId }}" name="notes" type="text" value="{{ $row['notes'] }}" placeholder="Optional note" class="w-48 rounded-lg border px-3 py-2 text-sm" style="border-color: rgba(196, 197, 213, 0.3); color: #444653;">
                                    </td>
                                    <td class="px-4 py-5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.employee-payment.show', $employee) }}" class="rounded-lg border px-3 py-2 text-xs font-bold" style="border-color: rgba(196, 197, 213, 0.3); color: #444653;">View</a>
                                            <form id="{{ $formId }}" method="POST" action="{{ route('admin.employee-payments.update', $employee) }}" class="inline-flex justify-end">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="role" value="{{ $role }}">
                                                <input type="hidden" name="status" value="{{ $status }}">
                                                <input type="hidden" name="search" value="{{ $search }}">
                                                <button type="submit" class="rounded-lg px-3 py-2 text-xs font-bold text-white" style="background: #2D8C5E;">Save</button>
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
    </div>
</x-layouts.admin>
