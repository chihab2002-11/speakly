<x-layouts.secretary :title="__('Student Payments')" :current-route="'secretary.payments'">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl" style="color: var(--lumina-text-primary); letter-spacing: -0.9px;">
            Student Payments
        </h1>
        <p class="mt-2 text-base" style="color: var(--lumina-text-secondary);">
            Follow payment progress and identify pending balances quickly.
        </p>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm font-semibold" style="background-color: #D1FAE5; border-color: #A7F3D0; color: #065F46;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm font-semibold" style="background-color: #FEF2F2; border-color: #FECACA; color: #991B1B;">
            {{ session('error') }}
        </div>
    @endif

    @if(! $paymentsEnabled)
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="background-color: #FFF7ED; border-color: #FDBA74; color: #9A3412;">
            Payments storage is not ready yet. Run <code>php artisan migrate</code> to create the <code>tuition_payments</code> table.
        </div>
    @endif

    @if(! $coursePricingEnabled)
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="background-color: #EFF6FF; border-color: #93C5FD; color: #1E3A8A;">
            Course pricing column is missing. Run <code>php artisan migrate</code> to apply course price migration.
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

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light);">
            <p class="text-sm" style="color: var(--lumina-text-muted);">Tracked Students</p>
            <p class="mt-1 text-3xl font-bold" style="color: var(--lumina-text-primary);">{{ $totalStudents }}</p>
        </div>
        <div class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light);">
            <p class="text-sm" style="color: var(--lumina-text-muted);">Total Tuition</p>
            <p class="mt-1 text-2xl font-bold" style="color: var(--lumina-text-primary);">{{ number_format($totalEstimatedRevenue) }} DA</p>
        </div>
        <div class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light);">
            <p class="text-sm" style="color: var(--lumina-text-muted);">Collected</p>
            <p class="mt-1 text-2xl font-bold" style="color: #15803D;">{{ number_format($totalCollected) }} DA</p>
        </div>
        <div class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light);">
            <p class="text-sm" style="color: var(--lumina-text-muted);">Outstanding</p>
            <p class="mt-1 text-2xl font-bold" style="color: #B45309;">{{ number_format($totalOutstanding) }} DA</p>
        </div>
        <div class="rounded-2xl border p-5" style="background: white; border-color: var(--lumina-border-light);">
            <p class="text-sm" style="color: var(--lumina-text-muted);">Paid / Pending</p>
            <p class="mt-1 text-lg font-bold" style="color: var(--lumina-text-primary);">{{ $paidCount }} / {{ $pendingCount }}</p>
        </div>
    </div>

    <div class="mb-4 rounded-2xl border p-4" style="background: white; border-color: var(--lumina-border-light);">
        <form method="GET" action="{{ route('secretary.payments') }}" class="grid gap-3 md:grid-cols-3">
            <div>
                <label for="search" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Search Student</label>
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
                <label for="status" class="mb-1 block text-xs font-semibold" style="color: var(--lumina-text-secondary);">Payment Status</label>
                <select
                    id="status"
                    name="status"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none"
                    style="border-color: var(--lumina-border); background: #F8FAFC;"
                >
                    <option value="" @selected($status === '')>All</option>
                    <option value="paid" @selected($status === 'paid')>Paid</option>
                    <option value="pending" @selected($status === 'pending')>Pending</option>
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
                    href="{{ route('secretary.payments') }}"
                    class="rounded-lg border px-4 py-2 text-sm font-semibold"
                    style="border-color: var(--lumina-border); color: var(--lumina-text-secondary);"
                >
                    Reset
                </a>
            </div>
        </form>
    </div>

    <section class="overflow-hidden rounded-3xl border" style="background: white; border-color: var(--lumina-border-light);">
        @if($payments->isEmpty())
            <div class="p-12 text-center">
                <p class="text-lg font-semibold" style="color: var(--lumina-text-primary);">No payment records found</p>
                <p class="mt-2 text-sm" style="color: var(--lumina-text-muted);">Try changing your search or filter criteria.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead style="background-color: #F8FAFC;">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-muted);">Student</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-muted);">Selected Course</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-muted);">Course Price</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-muted);">Applied Discount</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-muted);">Paid</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-muted);">Remaining</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-muted);">Status</th>
                            <th class="px-4 py-3 text-right font-semibold" style="color: var(--lumina-text-muted);">Record Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                            @php
                                $student = $payment['student'];
                                $discountPercent = (int) ($payment['discount_percent'] ?? 0);
                                $discountAmount = (int) ($payment['discount'] ?? 0);
                                $netDue = (int) ($payment['net_due'] ?? $payment['course_price'] ?? 0);
                            @endphp
                            <tr class="border-t" style="border-color: var(--lumina-border);">
                                <td class="px-4 py-4">
                                    <p class="font-semibold" style="color: var(--lumina-text-primary);">{{ $student->name }}</p>
                                    <p class="text-xs" style="color: var(--lumina-text-muted);">{{ $student->email }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-semibold" style="color: var(--lumina-text-primary);">{{ $payment['selected_course_name'] }}</p>
                                    <p class="text-xs" style="color: var(--lumina-text-muted);">
                                        {{ $payment['selected_course_code'] ?: $payment['academic_year'] }}
                                    </p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-semibold" style="color: var(--lumina-text-primary);">{{ number_format($netDue) }} DA</p>
                                    @if($discountAmount > 0)
                                        <p class="text-xs" style="color: var(--lumina-text-muted);">
                                            Before: {{ number_format($payment['gross_due'] ?? $payment['course_price']) }} DA
                                        </p>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-semibold" style="color: var(--lumina-text-primary);">{{ $discountPercent > 0 ? $discountPercent.'%' : 'None' }}</p>
                                    @if($discountAmount > 0)
                                        <p class="text-xs" style="color: var(--lumina-text-muted);">-{{ number_format($discountAmount) }} DA</p>
                                    @endif
                                </td>
                                <td class="px-4 py-4" style="color: #15803D;">{{ number_format($payment['amount_paid']) }} DA</td>
                                <td class="px-4 py-4 font-semibold" style="color: #B45309;">{{ number_format($payment['balance']) }} DA</td>
                                <td class="px-4 py-4">
                                    @if($payment['status'] === 'paid')
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" style="background: #DCFCE7; color: #166534;">
                                            Paid
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold" style="background: #FEF3C7; color: #92400E;">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <form method="POST" action="{{ route('secretary.payments.store') }}" target="_blank" data-refresh-after-payment class="flex justify-end gap-2">
                                        @csrf
                                        <input type="hidden" name="student_id" value="{{ $student->id }}">
                                        <input
                                            type="number"
                                            name="amount"
                                            min="1"
                                            step="1"
                                            required
                                            @disabled(! $paymentsEnabled)
                                            class="w-28 rounded-lg border px-2.5 py-1.5 text-sm"
                                            style="border-color: var(--lumina-border);"
                                            placeholder="Amount"
                                        >
                                        <select
                                            name="method"
                                            @disabled(! $paymentsEnabled)
                                            class="w-32 rounded-lg border px-2.5 py-1.5 text-sm"
                                            style="border-color: var(--lumina-border);"
                                        >
                                            @foreach($methods as $methodKey => $methodLabel)
                                                <option value="{{ $methodKey }}">{{ $methodLabel }}</option>
                                            @endforeach
                                        </select>
                                        <button
                                            type="submit"
                                            @disabled(! $paymentsEnabled)
                                            class="whitespace-nowrap rounded-lg px-3 py-1.5 text-xs font-semibold text-white {{ $paymentsEnabled ? '' : 'cursor-not-allowed opacity-60' }}"
                                            style="background-color: var(--lumina-primary);"
                                        >
                                            Save &amp; Print Receipt
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <script>
        document.querySelectorAll('[data-refresh-after-payment]').forEach((form) => {
            form.addEventListener('submit', () => {
                window.setTimeout(() => {
                    window.location.reload();
                }, 1200);
            });
        });
    </script>
</x-layouts.secretary>
