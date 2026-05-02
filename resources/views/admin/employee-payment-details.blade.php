<x-layouts.admin :title="__('Employee Payment Details')" :user="auth()->user()">
    <div class="mx-auto w-full max-w-4xl space-y-8">
        {{-- Header Section --}}
        <div class="mb-8 flex flex-col gap-6">
            {{-- Breadcrumb + Back Button --}}
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('admin.employee-payments.index') }}" class="text-blue-600 hover:text-blue-700 font-medium">
                    Employee Payments
                </a>
                <span style="color: #3F4941;">/</span>
                <span style="color: #3F4941;">{{ $employee->name }}</span>
            </div>

            {{-- Left: Title & Badge --}}
            <div class="flex flex-col gap-3">
                {{-- Role Badge --}}
                <div 
                    class="inline-flex w-fit items-center gap-2 rounded-full px-3 py-1"
                    style="background-color: #DDE1FF; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);"
                >
                    <svg class="h-3 w-3" fill="currentColor" style="color: #001453;" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                    </svg>
                    <span class="text-xs font-bold" style="color: #001453; letter-spacing: 0.3px;">
                        {{ $paymentData['role'] }}
                    </span>
                </div>

                {{-- Title --}}
                <h1 class="font-inter text-5xl font-extrabold" style="color: #181D19; letter-spacing: -2.4px;">
                    {{ $employee->name }}
                </h1>
                <p class="text-base font-medium" style="color: #3F4941;">
                    {{ $employee->email }}
                </p>
            </div>
        </div>

        {{-- Summary Cards --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Expected Salary Card --}}
            <article class="rounded-2xl border p-5" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 120px;">
                <p class="text-sm" style="color: #444653;">Expected Salary</p>
                <p class="mt-2 text-3xl font-extrabold" style="color: #1A1B22;">{{ number_format($paymentData['expected_salary'], 0, ',', ' ') }} DA</p>
                <p class="mt-3 text-xs" style="color: #64748B;">Total salary amount</p>
            </article>

            {{-- Amount Paid Card --}}
            <article class="rounded-2xl border p-5" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 120px;">
                <p class="text-sm" style="color: #444653;">Amount Paid</p>
                <p class="mt-2 text-3xl font-extrabold" style="color: #15803D;">{{ number_format($paymentData['amount_paid'], 0, ',', ' ') }} DA</p>
                <p class="mt-3 text-xs" style="color: #64748B;">Recorded payments</p>
            </article>

            {{-- Remaining Amount Card --}}
            <article class="rounded-2xl border p-5" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 120px;">
                <p class="text-sm" style="color: #444653;">Remaining Amount</p>
                <p class="mt-2 text-3xl font-extrabold" style="color: #B45309;">{{ number_format($paymentData['remaining'], 0, ',', ' ') }} DA</p>
                <p class="mt-3 text-xs" style="color: #64748B;">Never below zero</p>
            </article>

            {{-- Status Card --}}
            <article class="rounded-2xl border p-5" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04); min-height: 120px;">
                <p class="text-sm" style="color: #444653;">Payment Status</p>
                <div class="mt-2">
                    @if($paymentData['status'] === 'paid')
                        <span 
                            class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-bold"
                            style="background-color: #C1E6CC; color: #476853;"
                        >
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                            </svg>
                            Paid
                        </span>
                    @elseif($paymentData['status'] === 'partial')
                        <span 
                            class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-bold"
                            style="background-color: #FFF4E6; color: #926B3C;"
                        >
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                            </svg>
                            Partial
                        </span>
                    @elseif($paymentData['status'] === 'unpaid')
                        <span 
                            class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-bold"
                            style="background-color: #FFDAD6; color: #93000A;"
                        >
                            <span class="h-2 w-2 rounded-full" style="background-color: #BA1A1A;"></span>
                            Unpaid
                        </span>
                    @else
                        <span 
                            class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-sm font-bold"
                            style="background-color: #E5E9E3; color: #444653;"
                        >
                            <span class="h-2 w-2 rounded-full" style="background-color: #666;"></span>
                            Pending
                        </span>
                    @endif
                </div>
                <p class="mt-3 text-xs" style="color: #64748B;">Current payment status</p>
            </article>
        </section>

        {{-- Details Section --}}
        <section class="overflow-hidden rounded-2xl border p-6" style="background: #FFFFFF; border-color: rgba(196, 197, 213, 0.1); box-shadow: 0px 20px 25px -5px rgba(26, 27, 34, 0.04), 0px 8px 10px -6px rgba(26, 27, 34, 0.04);">
            <h2 class="mb-6 text-xl font-bold" style="color: #1A1B22;">Payment Information</h2>
            
            <div class="grid gap-6 sm:grid-cols-2">
                {{-- Employee Email --}}
                <div>
                    <label class="block text-sm font-semibold" style="color: #444653;">Email Address</label>
                    <p class="mt-2 text-base" style="color: #1A1B22;">{{ $employee->email }}</p>
                </div>

                {{-- Employee Phone --}}
                <div>
                    <label class="block text-sm font-semibold" style="color: #444653;">Phone Number</label>
                    <p class="mt-2 text-base" style="color: #1A1B22;">{{ $employee->phone ?? '-' }}</p>
                </div>

                {{-- Notes --}}
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold" style="color: #444653;">Payment Notes</label>
                    <p class="mt-2 text-base" style="color: #1A1B22;">{{ $paymentData['notes'] ?? '-' }}</p>
                </div>

                {{-- Last Updated --}}
                <div>
                    <label class="block text-sm font-semibold" style="color: #444653;">Last Updated</label>
                    <p class="mt-2 text-base" style="color: #1A1B22;">
                        {{ $paymentData['updated_at']?->format('Y-m-d H:i') ?? '-' }}
                    </p>
                </div>
            </div>
        </section>

        {{-- Actions Section --}}
        <section class="flex flex-wrap gap-3">
            <a 
                href="{{ route('admin.employee-payment.receipt-pdf', $employee) }}" 
                target="_blank"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white" 
                style="background: #2D8C5E;"
            >
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                </svg>
                Download Receipt (PDF)
            </a>

            <a 
                href="{{ route('admin.employee-payments.index') }}" 
                class="inline-flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-semibold" 
                style="border-color: rgba(196, 197, 213, 0.3); color: #444653;"
            >
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                </svg>
                Back to Payments
            </a>

            <a 
                href="{{ route('admin.employees.index') }}" 
                class="inline-flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-semibold" 
                style="border-color: rgba(196, 197, 213, 0.3); color: #444653;"
            >
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M16 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-8-4c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-5.5c0-2.33-4.67-3.5-7-3.5zm8-1c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm6 0c-.82 0-1.5.68-1.5 1.5s.68 1.5 1.5 1.5 1.5-.68 1.5-1.5-.68-1.5-1.5-1.5z"/>
                </svg>
                View Employee Profile
            </a>
        </section>
    </div>
</x-layouts.admin>
