@php
    /** @var \App\Models\User $employee */
    $employee = $employee ?? auth()->user();
    $roleLabel = (string) ($paymentData['role'] ?? 'Employee');
    $hasPaymentRecord = $employeePayment !== null;
    $reference = 'EMP-'.str_pad((string) ($employeePayment?->id ?? 0), 6, '0', STR_PAD_LEFT);
@endphp

<div class="mx-auto w-full max-w-4xl space-y-8">
    {{-- Header --}}
    <div class="mb-8 flex flex-col gap-6">
        <div class="flex flex-col gap-3">
            <div
                class="inline-flex w-fit items-center gap-2 rounded-full px-3 py-1"
                style="background-color: #DDE1FF; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);"
            >
                <svg class="h-3 w-3" fill="currentColor" style="color: #001453;" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                </svg>
                <span class="text-xs font-bold" style="color: #001453; letter-spacing: 0.3px;">
                    {{ $roleLabel }}
                </span>
            </div>

            <h1 class="font-inter text-5xl font-extrabold" style="color: #181D19; letter-spacing: -2.4px;">
                My Payments
            </h1>
            <p class="text-base font-medium" style="color: #3F4941;">
                {{ $employee->name }} &bull; {{ $employee->email }}
            </p>
        </div>
    </div>

    @if(! $hasPaymentRecord)
        <section class="rounded-2xl border p-6 text-sm" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); color: #3F4941;">
            No payment information has been recorded yet.
        </section>
    @endif

    {{-- Summary Cards --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border p-5" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 120px;">
            <p class="text-sm" style="color: #444653;">Expected Salary</p>
            <p class="mt-2 text-3xl font-extrabold" style="color: #1A1B22;">{{ number_format((int) $paymentData['expected_salary'], 0, ',', ' ') }} DA</p>
            <p class="mt-3 text-xs" style="color: #64748B;">Total salary amount</p>
        </article>

        <article class="rounded-2xl border p-5" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 120px;">
            <p class="text-sm" style="color: #444653;">Amount Paid</p>
            <p class="mt-2 text-3xl font-extrabold" style="color: #15803D;">{{ number_format((int) $paymentData['amount_paid'], 0, ',', ' ') }} DA</p>
            <p class="mt-3 text-xs" style="color: #64748B;">Recorded payments</p>
        </article>

        <article class="rounded-2xl border p-5" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 120px;">
            <p class="text-sm" style="color: #444653;">Remaining Amount</p>
            <p class="mt-2 text-3xl font-extrabold" style="color: #B45309;">{{ number_format((int) $paymentData['remaining'], 0, ',', ' ') }} DA</p>
            <p class="mt-3 text-xs" style="color: #64748B;">Never below zero</p>
        </article>

        <article class="rounded-2xl border p-5" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 120px;">
            <p class="text-sm" style="color: #444653;">Payment Status</p>
            <div class="mt-2">
                @if(($paymentData['status'] ?? 'pending') === 'paid')
                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-bold" style="background-color: #C1E6CC; color: #476853;">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                        Paid
                    </span>
                @elseif(($paymentData['status'] ?? 'pending') === 'partial')
                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-bold" style="background-color: #FEEFC3; color: #7A4E00;">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 22c5.52 0 10-4.48 10-10S17.52 2 12 2 2 6.48 2 12s4.48 10 10 10zm-1-16h2v6h-2V6zm0 8h2v2h-2v-2z"/></svg>
                        Partial
                    </span>
                @elseif(($paymentData['status'] ?? 'pending') === 'unpaid')
                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-bold" style="background-color: #FFDAD6; color: #93000A;">
                        <span class="h-2 w-2 rounded-full" style="background-color: #BA1A1A;"></span>
                        Unpaid
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-bold" style="background-color: #E5E9E3; color: #444653;">
                        <span class="h-2 w-2 rounded-full" style="background-color: #666;"></span>
                        Pending
                    </span>
                @endif
            </div>
            <p class="mt-3 text-xs" style="color: #64748B;">Current payment status</p>
        </article>
    </section>

    {{-- Details --}}
    <section class="overflow-hidden rounded-2xl border p-6" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04);">
        <h2 class="mb-6 text-xl font-bold" style="color: #1A1B22;">Payment Information</h2>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-semibold" style="color: #444653;">Employee</label>
                <p class="mt-2 text-base" style="color: #1A1B22;">{{ $employee->name }}</p>
            </div>

            <div>
                <label class="block text-sm font-semibold" style="color: #444653;">Email Address</label>
                <p class="mt-2 text-base" style="color: #1A1B22;">{{ $employee->email }}</p>
            </div>

            <div>
                <label class="block text-sm font-semibold" style="color: #444653;">Role</label>
                <p class="mt-2 text-base" style="color: #1A1B22;">{{ $roleLabel }}</p>
            </div>

            <div>
                <label class="block text-sm font-semibold" style="color: #444653;">Reference</label>
                <p class="mt-2 text-base" style="color: #1A1B22;">{{ $reference }}</p>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-semibold" style="color: #444653;">Notes</label>
                <p class="mt-2 text-base" style="color: #1A1B22;">{{ $paymentData['notes'] ?? '-' }}</p>
            </div>

            <div>
                <label class="block text-sm font-semibold" style="color: #444653;">Last Updated</label>
                <p class="mt-2 text-base" style="color: #1A1B22;">
                    {{ $paymentData['updated_at']?->format('Y-m-d H:i') ?? '-' }}
                </p>
            </div>
        </div>
    </section>

    {{-- Actions --}}
    <section class="flex flex-wrap gap-3">
        <a
            href="{{ $receiptUrl }}"
            target="_blank"
            class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white"
            style="background: #2D8C5E;"
        >
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
            </svg>
            Download Receipt (PDF)
        </a>
    </section>
</div>

