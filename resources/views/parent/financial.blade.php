<x-layouts.parent 
    :title="'Financial Information'"
    :pageTitle="'Financial Information'"
    :currentRoute="'financial'"
    :user="$user ?? null"
    :children="$children ?? []"
>
    {{-- Page Header --}}
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div class="flex flex-col gap-1">
            <h2 
                class="text-3xl font-black"
                style="color: var(--lumina-text-primary); letter-spacing: -0.9px;"
            >
                Financial Information
            </h2>
            <p class="text-sm" style="color: var(--lumina-text-muted);">
                Manage tuition payments and view transaction history
            </p>
        </div>
        
        {{-- Quick Stats --}}
        <div class="flex gap-4">
            <div class="rounded-2xl border p-4" style="background-color: #FFFFFF; border-color: var(--lumina-border-light);">
                <span class="text-xs font-medium" style="color: var(--lumina-text-muted);">Total Outstanding</span>
                <p class="text-2xl font-black" style="color: var(--lumina-accent-red);">
                    {{ number_format($totalOutstanding ?? 260000, 0, ',', ' ') }} DZD
                </p>
            </div>
            <div class="rounded-2xl border p-4" style="background-color: #FFFFFF; border-color: var(--lumina-border-light);">
                <span class="text-xs font-medium" style="color: var(--lumina-text-muted);">Total Paid (This Year)</span>
                <p class="text-2xl font-black" style="color: var(--lumina-primary);">
                    {{ number_format($totalPaid ?? 365000, 0, ',', ' ') }} DZD
                </p>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Pending Invoices (2 columns) --}}
        <div class="lg:col-span-2">
            <div 
                class="rounded-3xl border"
                style="background-color: #FFFFFF; border-color: var(--lumina-border-light);"
            >
                {{-- Section Header --}}
                <div class="flex items-center justify-between border-b p-6" style="border-color: var(--lumina-border);">
                    <div class="flex items-center gap-3">
                        <div 
                            class="flex h-10 w-10 items-center justify-center rounded-xl"
                            style="background-color: rgba(186, 26, 26, 0.1);"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-accent-red);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                                Pending Invoices
                            </h3>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">
                                {{ count($invoices ?? []) }} invoice(s) awaiting payment
                            </p>
                        </div>
                    </div>
                    <button 
                        class="rounded-xl px-4 py-2 text-sm font-bold transition-all hover:opacity-90"
                        style="background-color: var(--lumina-primary); color: white;"
                    >
                        Pay All
                    </button>
                </div>

                {{-- Invoices List --}}
                <div class="divide-y" style="border-color: var(--lumina-border);">
                    @forelse($invoices ?? [] as $invoice)
                        <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-start gap-4">
                                <div 
                                    class="flex h-12 w-12 items-center justify-center rounded-xl"
                                    style="background-color: var(--lumina-bg-card);"
                                >
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-text-secondary);">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs font-semibold" style="color: var(--lumina-text-muted);">
                                        {{ $invoice['id'] }}
                                    </span>
                                    <h4 class="font-bold" style="color: var(--lumina-text-primary);">
                                        {{ $invoice['description'] }}
                                    </h4>
                                    <span class="text-sm" style="color: var(--lumina-text-secondary);">
                                        {{ $invoice['child'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="text-lg font-black" style="color: var(--lumina-text-primary);">
                                    {{ number_format($invoice['amount'], 0, ',', ' ') }} DZD
                                </span>
                                <span class="text-xs" style="color: var(--lumina-accent-red);">
                                    Due: {{ $invoice['dueDate'] }}
                                </span>
                                <button 
                                    class="rounded-lg px-4 py-1.5 text-xs font-bold transition-all hover:opacity-90"
                                    style="background-color: var(--lumina-dark-green); color: white;"
                                >
                                    Pay Now
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center p-12 text-center">
                            <svg class="mb-4 h-16 w-16 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-primary);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h4 class="text-lg font-bold" style="color: var(--lumina-text-primary);">All Caught Up!</h4>
                            <p class="text-sm" style="color: var(--lumina-text-muted);">No pending invoices at this time.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Payment Methods Card --}}
        <div class="flex flex-col gap-6">
            {{-- Payment Options --}}
            <div 
                class="rounded-3xl border p-6"
                style="background-color: #FFFFFF; border-color: var(--lumina-border-light);"
            >
                <h3 class="mb-4 text-lg font-bold" style="color: var(--lumina-text-primary);">
                    Payment Methods
                </h3>
                <div class="flex flex-col gap-3">
                    <div class="flex items-center gap-3 rounded-xl p-3" style="background-color: var(--lumina-bg-card);">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg" style="background-color: #EEF2FF;">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #4F46E5;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-sm font-semibold" style="color: var(--lumina-text-primary);">CIB Card</span>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">Secure online payment</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3 rounded-xl p-3" style="background-color: var(--lumina-bg-card);">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg" style="background-color: #FEF3C7;">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: #D97706;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-sm font-semibold" style="color: var(--lumina-text-primary);">Bank Transfer</span>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">BNA, CPA, BADR</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3 rounded-xl p-3" style="background-color: var(--lumina-bg-card);">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg" style="background-color: #D1FAE5;">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-primary);">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <span class="text-sm font-semibold" style="color: var(--lumina-text-primary);">Cash at Office</span>
                            <p class="text-xs" style="color: var(--lumina-text-muted);">Mon-Fri, 8AM-4PM</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bank Details --}}
            <div 
                class="rounded-3xl border p-6"
                style="background-color: var(--lumina-dark-green);"
            >
                <h3 class="mb-4 text-lg font-bold text-white">
                    Bank Transfer Details
                </h3>
                <div class="flex flex-col gap-3 text-sm">
                    <div class="flex justify-between">
                        <span style="color: #A7F3D0;">Bank Name:</span>
                        <span class="font-semibold text-white">BNA Algeria</span>
                    </div>
                    <div class="flex justify-between">
                        <span style="color: #A7F3D0;">Account Name:</span>
                        <span class="font-semibold text-white">Lumina Academy</span>
                    </div>
                    <div class="flex justify-between">
                        <span style="color: #A7F3D0;">RIB:</span>
                        <span class="font-semibold text-white">001 00000 0123456789 01</span>
                    </div>
                    <div class="mt-2 rounded-lg p-3" style="background-color: rgba(255,255,255,0.1);">
                        <p class="text-xs" style="color: #A7F3D0;">
                            Please include student name and invoice number in the transfer reference.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment History --}}
    <div 
        class="mt-6 rounded-3xl border"
        style="background-color: #FFFFFF; border-color: var(--lumina-border-light);"
    >
        {{-- Section Header --}}
        <div class="flex items-center justify-between border-b p-6" style="border-color: var(--lumina-border);">
            <div class="flex items-center gap-3">
                <div 
                    class="flex h-10 w-10 items-center justify-center rounded-xl"
                    style="background-color: var(--lumina-accent-green-bg);"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color: var(--lumina-primary);">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold" style="color: var(--lumina-text-primary);">
                        Payment History
                    </h3>
                    <p class="text-xs" style="color: var(--lumina-text-muted);">
                        Your completed transactions
                    </p>
                </div>
            </div>
            <button 
                class="rounded-xl px-4 py-2 text-sm font-semibold transition-all hover:bg-gray-100"
                style="color: var(--lumina-primary); border: 1px solid var(--lumina-border);"
            >
                Download All
            </button>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr style="background-color: var(--lumina-bg-card);">
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted);">
                            Reference
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted);">
                            Child
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted);">
                            Description
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted);">
                            Amount
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted);">
                            Date
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted);">
                            Method
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider" style="color: var(--lumina-text-muted);">
                            Receipt
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color: var(--lumina-border);">
                    @forelse($paymentHistory ?? [] as $payment)
                        <tr class="transition-colors hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold" style="color: var(--lumina-text-primary);">
                                {{ $payment['id'] }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm" style="color: var(--lumina-text-secondary);">
                                {{ $payment['child'] }}
                            </td>
                            <td class="px-6 py-4 text-sm" style="color: var(--lumina-text-secondary);">
                                {{ $payment['description'] }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-bold" style="color: var(--lumina-primary);">
                                {{ number_format($payment['amount'], 0, ',', ' ') }} DZD
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm" style="color: var(--lumina-text-muted);">
                                {{ $payment['paidDate'] }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm" style="color: var(--lumina-text-muted);">
                                {{ $payment['method'] }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right">
                                <button class="text-sm font-semibold hover:underline" style="color: var(--lumina-primary);">
                                    Download
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <p class="text-sm" style="color: var(--lumina-text-muted);">No payment history available.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.parent>
