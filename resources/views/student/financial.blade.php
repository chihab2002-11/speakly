<x-layouts.student :title="__('Financial Information')" :currentRoute="'financial'">
    @php
        $offers = collect($scholarshipOffers ?? [])->values();
        $selectedOffer = is_array($selectedScholarshipOffer ?? null) ? $selectedScholarshipOffer : $offers->first();
        $initialOfferKey = $selectedOffer['key'] ?? ($offers->first()['key'] ?? null);
    @endphp

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

    {{-- Header Section --}}
    <div class="mb-8 flex flex-col gap-6">
        {{-- Left: Title & Badge --}}
        <div class="flex flex-col gap-3">
            {{-- Verified Account Badge --}}
            <div 
                class="inline-flex w-fit items-center gap-2 rounded-full px-3 py-1"
                style="background-color: #DDE1FF; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);"
            >
                <svg class="h-3 w-3" fill="currentColor" style="color: #001453;" viewBox="0 0 24 24">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
                </svg>
                <span class="text-xs font-bold" style="color: #001453; letter-spacing: 0.3px;">
                    Verified Account - Adult Student
                </span>
            </div>

            {{-- Title --}}
            <h1 class="font-inter text-5xl font-extrabold" style="color: #181D19; letter-spacing: -2.4px;">
                Financial Ledger
            </h1>
            <p class="text-base font-medium" style="color: #3F4941;">
                Lumina Academy Central Billing Portal
            </p>

            <div class="mt-2 flex flex-wrap items-center gap-2 text-sm" style="color: #3F4941;">
                <span class="rounded-full px-3 py-1" style="background-color: #E5E9E3;">
                    Outstanding before discount: {{ number_format($totalOutstandingBeforeDiscount ?? 0, 0, ',', ' ') }} DZD
                </span>
                <span class="rounded-full px-3 py-1" style="background-color: #D1FAE5; color: #065F46;">
                    Active discount: {{ (int) ($scholarshipDiscount ?? 0) }}%
                </span>
                <span class="rounded-full px-3 py-1" style="background-color: #F0F5EE;">
                    Discount amount: {{ number_format($discountAmount ?? 0, 0, ',', ' ') }} DZD
                </span>
            </div>
        </div>

    </div>

    {{-- Bento Grid Content --}}
    <div class="relative grid gap-8 lg:grid-cols-3">
        {{-- Left Column: Tuition Ledger (2 columns) --}}
        <div class="flex flex-col gap-6 lg:col-span-2">
            {{-- Tuition Ledger Header --}}
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold" style="color: #181D19; letter-spacing: -0.6px;">
                    Tuition Ledger
                </h2>
                <span 
                    class="rounded-full px-3 py-1 text-xs font-semibold"
                    style="background-color: #E5E9E3; color: #3F4941;"
                >
                    {{ $academicYear ?? '2024' }} Academic Year
                </span>
            </div>

            {{-- Tuition Table --}}
            <div 
                class="overflow-hidden rounded-xl border"
                style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.15);"
            >
                <table class="w-full">
                    <thead>
                        <tr style="background-color: #F0F5EE;">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #3F4941; letter-spacing: 1.2px;">
                                Course / Service
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #3F4941; letter-spacing: 1.2px;">
                                Applied Discount
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider" style="color: #3F4941; letter-spacing: 1.2px;">
                                Amount
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider" style="color: #3F4941; letter-spacing: 1.2px;">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ledgerItems as $item)
                            @php
                                $discountPercent = (int) ($item['discount_percent'] ?? 0);
                                $discountAmountForItem = (int) ($item['discount_amount'] ?? 0);
                                $finalAmount = (int) ($item['final_amount'] ?? $item['amount'] ?? 0);
                            @endphp
                            <tr class="border-t" style="border-color: rgba(190, 201, 191, 0.15);">
                                {{-- Course/Service --}}
                                <td class="px-6 py-6">
                                    <div class="flex items-center gap-4">
                                        {{-- Icon --}}
                                        <div 
                                            class="flex h-10 w-10 items-center justify-center rounded {{ $item['status'] === 'outstanding' ? 'bg-emerald-50' : 'bg-slate-50' }}"
                                        >
                                            @if($item['icon'] === 'course')
                                                <svg class="h-5 w-5 {{ $item['status'] === 'outstanding' ? 'text-emerald-700' : 'text-slate-600' }}" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                                                </svg>
                                            @elseif($item['icon'] === 'workshop')
                                                <svg class="h-5 w-5" fill="currentColor" style="color: #475569;" viewBox="0 0 24 24">
                                                    <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm6.93 6h-2.95c-.32-1.25-.78-2.45-1.38-3.56 1.84.63 3.37 1.91 4.33 3.56zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14C4.1 13.36 4 12.69 4 12s.1-1.36.26-2h3.38c-.08.66-.14 1.32-.14 2 0 .68.06 1.34.14 2H4.26zm.82 2h2.95c.32 1.25.78 2.45 1.38 3.56-1.84-.63-3.37-1.91-4.33-3.56zm2.95-8H5.08c.96-1.66 2.49-2.93 4.33-3.56C8.81 5.55 8.35 6.75 8.03 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82c-.43 1.43-1.08 2.76-1.91 3.96zM14.34 14H9.66c-.09-.66-.16-1.32-.16-2 0-.68.07-1.35.16-2h4.68c.09.65.16 1.32.16 2 0 .68-.07 1.34-.16 2zm.25 5.56c.6-1.11 1.06-2.31 1.38-3.56h2.95c-.96 1.65-2.49 2.93-4.33 3.56zM16.36 14c.08-.66.14-1.32.14-2 0-.68-.06-1.34-.14-2h3.38c.16.64.26 1.31.26 2s-.1 1.36-.26 2h-3.38z"/>
                                                </svg>
                                            @else
                                                <svg class="h-5 w-5" fill="currentColor" style="color: #475569;" viewBox="0 0 24 24">
                                                    <path d="M21 5c-1.11-.35-2.33-.5-3.5-.5-1.95 0-4.05.4-5.5 1.5-1.45-1.1-3.55-1.5-5.5-1.5S2.45 4.9 1 6v14.65c0 .25.25.5.5.5.1 0 .15-.05.25-.05C3.1 20.45 5.05 20 6.5 20c1.95 0 4.05.4 5.5 1.5 1.35-.85 3.8-1.5 5.5-1.5 1.65 0 3.35.3 4.75 1.05.1.05.15.05.25.05.25 0 .5-.25.5-.5V6c-.6-.45-1.25-.75-2-1zm0 13.5c-1.1-.35-2.3-.5-3.5-.5-1.7 0-4.15.65-5.5 1.5V8c1.35-.85 3.8-1.5 5.5-1.5 1.2 0 2.4.15 3.5.5v11.5z"/>
                                                </svg>
                                            @endif
                                        </div>
                                        {{-- Info --}}
                                        <div class="flex flex-col">
                                            <span class="text-base font-bold" style="color: #181D19;">
                                                {{ $item['name'] }}
                                            </span>
                                            <span class="text-sm" style="color: #3F4941;">
                                                {{ $item['type'] }} &bull; {{ $item['period'] }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold" style="color: #181D19;">
                                            {{ $discountPercent > 0 ? $discountPercent.'%' : 'None' }}
                                        </span>
                                        @if($discountAmountForItem > 0)
                                            <span class="text-xs" style="color: #3F4941;">
                                                -{{ number_format($discountAmountForItem, 0, ',', ' ') }} DZD
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                {{-- Amount --}}
                                <td class="px-6 py-6 text-right">
                                    <div class="flex flex-col items-end gap-1">
                                        <span class="text-base font-bold" style="color: #181D19;">
                                            {{ number_format($finalAmount, 0, ',', ' ') }} DZD
                                        </span>
                                        @if($discountAmountForItem > 0)
                                            <span class="text-xs" style="color: #64748B;">
                                                Before: {{ number_format($item['amount'], 0, ',', ' ') }} DZD
                                            </span>
                                        @endif
                                        <span class="text-xs font-semibold" style="color: #B45309;">
                                            Remaining: {{ number_format($item['remaining'] ?? 0, 0, ',', ' ') }} DZD
                                        </span>
                                    </div>
                                </td>
                                {{-- Status --}}
                                <td class="px-6 py-6 text-center">
                                    @if($item['status'] === 'outstanding')
                                        <span 
                                            class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold uppercase"
                                            style="background-color: #FFDAD6; color: #93000A; letter-spacing: -0.6px;"
                                        >
                                            <span class="h-1.5 w-1.5 rounded-full" style="background-color: #BA1A1A;"></span>
                                            Outstanding
                                        </span>
                                    @else
                                        <span 
                                            class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold uppercase"
                                            style="background-color: #C1E6CC; color: #476853; letter-spacing: -0.6px;"
                                        >
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                                            </svg>
                                            Paid
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm" style="color: #3F4941;">
                                    No financial ledger entries yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right Column: Payment Progress (1 column) --}}
        <div class="flex flex-col gap-4 lg:col-span-1">
            {{-- Payment Progress Header --}}
            <h2 class="text-2xl font-bold" style="color: #181D19; letter-spacing: -0.6px;">
                Payment Progress
            </h2>

            {{-- Payment Progress Card --}}
            <div 
                class="relative overflow-hidden rounded-xl p-8"
                style="background: linear-gradient(135deg, #065F46 0%, #022C22 100%); box-shadow: 0px 20px 25px -5px rgba(0, 0, 0, 0.1), 0px 8px 10px -6px rgba(0, 0, 0, 0.1);"
            >
                {{-- Blur overlay --}}
                <div 
                    class="pointer-events-none absolute -right-16 -top-16 h-32 w-32 rounded-full blur-[20px]"
                    style="background: rgba(255, 255, 255, 0.05);"
                ></div>

                {{-- Content --}}
                <div class="relative flex flex-col gap-4">
                    {{-- Icon --}}
                    <div 
                        class="flex h-12 w-12 items-center justify-center rounded-lg"
                        style="background: rgba(255, 255, 255, 0.1);"
                    >
                        <svg class="h-5 w-5" fill="currentColor" style="color: #6EE7B7;" viewBox="0 0 24 24">
                            <path d="M19 14V6c0-1.1-.9-2-2-2H3c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zm-2 0H3V6h14v8zm-7-1c1.66 0 3-1.34 3-3S11.66 7 10 7s-3 1.34-3 3 1.34 3 3 3zm11-5v10H5v2h16c1.1 0 2-.9 2-2V8h-2z"/>
                        </svg>
                    </div>

                    {{-- Title --}}
                    <h3 class="text-xl font-bold leading-tight text-white">
                        Tuition Payment Progress
                    </h3>

                    {{-- Description --}}
                    <p class="text-sm leading-relaxed" style="color: rgba(209, 250, 229, 0.7);">
                        Tracks how much of your enrolled course tuition has already been paid.
                    </p>

                    {{-- Progress --}}
                    <div class="mt-4 flex items-end justify-between gap-4">
                        <span class="text-4xl font-black text-white">
                            {{ $paidPercentage ?? 0 }}%
                        </span>
                        <span class="text-xs font-bold uppercase tracking-wider" style="color: #6EE7B7; letter-spacing: 1.2px;">
                            Paid
                        </span>
                    </div>

                    @php
                        $progressWidth = min(max((int) ($paidPercentage ?? 0), 0), 100);
                    @endphp
                    <div class="h-2 overflow-hidden rounded-full" style="background: rgba(255, 255, 255, 0.12);">
                        <div
                            class="h-full rounded-full"
                            style="--progress-width: {{ $progressWidth }}%; width: var(--progress-width); background-color: #6EE7B7;"
                        ></div>
                    </div>

                    <div class="mt-3 border-t pt-4" style="border-color: rgba(255, 255, 255, 0.12);">
                        <span class="text-xs font-bold uppercase tracking-wider" style="color: #6EE7B7; letter-spacing: 1.2px;">
                            Remaining Unpaid
                        </span>
                        <p class="mt-1 text-2xl font-black text-white">
                            {{ number_format($totalRemaining ?? 0, 0, ',', ' ') }} DZD
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                <h2 class="text-2xl font-bold" style="color: #181D19; letter-spacing: -0.6px;">
                    Scholarship Offers
                </h2>

                <div id="studentScholarshipCard" class="relative overflow-hidden rounded-xl p-8" style="background: linear-gradient(135deg, #065F46 0%, #022C22 100%); box-shadow: 0px 20px 25px -5px rgba(0, 0, 0, 0.1), 0px 8px 10px -6px rgba(0, 0, 0, 0.1);">
                    <div class="pointer-events-none absolute -right-16 -top-16 h-32 w-32 rounded-full blur-[20px]" style="background: rgba(255, 255, 255, 0.05);"></div>

                    <div id="studentScholarshipCardContent" class="relative flex flex-col gap-4 transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <button id="studentScholarshipPrevBtn" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full border text-white/80 transition-all hover:scale-105 hover:text-white" style="border-color: rgba(255,255,255,0.35); background: rgba(255,255,255,0.08);">&larr;</button>
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg" style="background: rgba(255, 255, 255, 0.1);">
                                <svg class="h-5 w-5" fill="currentColor" style="color: #6EE7B7;" viewBox="0 0 24 24">
                                    <path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94.63 1.5 1.98 2.63 3.61 2.96V19H7v2h10v-2h-4v-3.1c1.63-.33 2.98-1.46 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/>
                                </svg>
                            </div>
                            <button id="studentScholarshipNextBtn" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full border text-white/80 transition-all hover:scale-105 hover:text-white" style="border-color: rgba(255,255,255,0.35); background: rgba(255,255,255,0.08);">&rarr;</button>
                        </div>

                        <h3 id="studentScholarshipTitle" class="text-3xl font-bold leading-tight text-white">{{ $selectedOffer['title'] ?? 'Scholarship Offer' }}</h3>
                        <p id="studentScholarshipTarget" class="text-sm font-semibold uppercase tracking-wide" style="color: rgba(209, 250, 229, 0.95);">{{ $selectedOffer['targetLabel'] ?? 'Your enrollment' }}</p>
                        <p id="studentScholarshipDescription" class="text-base leading-relaxed" style="color: rgba(209, 250, 229, 0.8);">{{ $selectedOffer['description'] ?? 'Scholarship details unavailable.' }}</p>
                        <div id="studentScholarshipProgress" class="rounded-lg px-3 py-2 text-sm" style="background: rgba(255,255,255,0.1); color: #D1FAE5;">
                            Progress: {{ (int) ($selectedOffer['progressPercent'] ?? 0) }}%<br>
                            {{ $selectedOffer['remainingText'] ?? '' }}
                        </div>

                        <div class="mt-2 flex items-end justify-between">
                            <span id="studentScholarshipPercent" class="text-5xl font-black text-white">{{ (int) ($selectedOffer['discountPercent'] ?? 0) }}%</span>
                            <span id="studentScholarshipState" class="text-sm font-bold uppercase tracking-wider" style="color: #6EE7B7; letter-spacing: 1.2px;">
                                @if($selectedOffer['isActive'] ?? false)
                                    Activated for your account
                                @elseif($selectedOffer['isEligible'] ?? false)
                                    Available for your enrollment
                                @else
                                    Keep progressing
                                @endif
                            </span>
                        </div>

                        <form method="POST" action="{{ route('student.financial.scholarships.activate') }}" class="mt-2">
                            @csrf
                            <input id="studentScholarshipOfferKeyInput" type="hidden" name="offer_key" value="{{ $selectedOffer['key'] ?? '' }}">
                            <button id="studentScholarshipActivateBtn" type="submit" class="w-full rounded-lg px-4 py-2 text-sm font-bold text-white transition-opacity disabled:cursor-not-allowed disabled:opacity-50" style="background-color: #10B981;">
                                @if($selectedOffer['isActive'] ?? false)
                                    Active Discount
                                @elseif($selectedOffer['isEligible'] ?? false)
                                    Activate Discount
                                @else
                                    Not Eligible Yet
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment History Section --}}
    <div class="mt-10 flex flex-col gap-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold" style="color: #181D19; letter-spacing: -0.6px;">
                Payment History
            </h2>
            <a 
                href="#" 
                class="inline-flex items-center gap-1 text-sm font-bold transition-colors hover:opacity-80"
                style="color: #006A41;"
            >
                View Full Archive
                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/>
                </svg>
            </a>
        </div>

        {{-- Receipt Cards Grid --}}
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @forelse($receipts as $receipt)
                <div 
                    class="flex items-center justify-between rounded-xl border p-5"
                    style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.15);"
                >
                    {{-- Left: Icon + Info --}}
                    <div class="flex items-center gap-4">
                        {{-- Receipt Icon --}}
                        <div 
                            class="flex h-12 w-10 items-center justify-center rounded-lg"
                            style="background-color: #F0F5EE;"
                        >
                            <svg class="h-5 w-5" fill="currentColor" style="color: #3F4941;" viewBox="0 0 24 24">
                                <path d="M19.5 3.5L18 2l-1.5 1.5L15 2l-1.5 1.5L12 2l-1.5 1.5L9 2 7.5 3.5 6 2v14H3v3c0 1.66 1.34 3 3 3h12c1.66 0 3-1.34 3-3V2l-1.5 1.5zM19 19c0 .55-.45 1-1 1s-1-.45-1-1v-3H8V5h11v14z"/>
                            </svg>
                        </div>
                        {{-- Info --}}
                        <div class="flex flex-col">
                            <span class="text-base font-bold" style="color: #181D19;">
                                {{ $receipt['invoice'] }}
                            </span>
                            <span class="text-xs font-medium" style="color: #3F4941;">
                                {{ $receipt['date'] }} &bull; {{ $receipt['method'] }}
                                @if($receipt['last4'])
                                    <br>****{{ $receipt['last4'] }}
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Right: Amount + PDF --}}
                    <div class="flex flex-col items-end gap-2">
                        <span class="text-base font-black" style="color: #181D19;">
                            {{ number_format($receipt['amount'], 0, ',', ' ') }} DZD
                        </span>
                        <a 
                            href="{{ route('student.financial.payments.pdf', $receipt['payment_id']) }}"
                            target="_blank"
                            class="inline-flex items-center gap-1 text-xs font-bold transition-colors hover:opacity-80"
                            style="color: #006A41;"
                        >
                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/>
                            </svg>
                            PDF
                        </a>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border p-6 text-center text-sm" style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.15); color: #3F4941;">
                    No receipts available yet.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Security Footer --}}
    <div 
        class="mt-8 flex items-center justify-between border-t pt-8"
        style="border-color: rgba(190, 201, 191, 0.15);"
    >
        <div class="flex items-center gap-4">
            <svg class="h-5 w-5" fill="currentColor" style="color: #059669;" viewBox="0 0 24 24">
                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
            </svg>
            <span class="text-sm font-medium" style="color: #3F4941;">
                Secure 256-bit SSL Encrypted Billing Portal
            </span>
        </div>
    </div>

    <script id="studentScholarshipOffers" type="application/json">@json($offers->values())</script>
    <script id="studentScholarshipInitialOfferKey" type="application/json">@json($initialOfferKey)</script>
    <script>
        (function () {
            const offers = JSON.parse(document.getElementById('studentScholarshipOffers')?.textContent || '[]');
            const initialOfferKey = JSON.parse(document.getElementById('studentScholarshipInitialOfferKey')?.textContent || 'null');
            const prevBtn = document.getElementById('studentScholarshipPrevBtn');
            const nextBtn = document.getElementById('studentScholarshipNextBtn');
            const content = document.getElementById('studentScholarshipCardContent');
            const titleEl = document.getElementById('studentScholarshipTitle');
            const targetEl = document.getElementById('studentScholarshipTarget');
            const descriptionEl = document.getElementById('studentScholarshipDescription');
            const progressEl = document.getElementById('studentScholarshipProgress');
            const percentEl = document.getElementById('studentScholarshipPercent');
            const stateEl = document.getElementById('studentScholarshipState');
            const activateBtn = document.getElementById('studentScholarshipActivateBtn');
            const offerKeyInput = document.getElementById('studentScholarshipOfferKeyInput');

            if (! offers.length || ! titleEl) {
                return;
            }

            let index = Math.max(0, offers.findIndex((offer) => offer.key === initialOfferKey));

            const render = () => {
                const offer = offers[index] || offers[0];
                const isActive = Boolean(offer.isActive);
                const isEligible = Boolean(offer.isEligible);
                const canActivate = isEligible && ! isActive;

                titleEl.textContent = offer.title || 'Scholarship Offer';
                targetEl.textContent = offer.targetLabel || 'Your enrollment';
                descriptionEl.textContent = offer.description || 'Scholarship details unavailable.';
                progressEl.innerHTML = 'Progress: ' + Number(offer.progressPercent || 0) + '%<br>' + (offer.remainingText || '');
                percentEl.textContent = Number(offer.discountPercent || 0) + '%';
                stateEl.textContent = isActive
                    ? 'Activated for your account'
                    : (isEligible ? 'Available for your enrollment' : 'Keep progressing');

                if (offerKeyInput) {
                    offerKeyInput.value = offer.key || '';
                }

                if (activateBtn) {
                    activateBtn.disabled = ! canActivate;
                    activateBtn.textContent = isActive
                        ? 'Active Discount'
                        : (canActivate ? 'Activate Discount' : 'Not Eligible Yet');
                }
            };

            const animateTo = (nextIndex) => {
                if (! content) {
                    index = (nextIndex + offers.length) % offers.length;
                    render();
                    return;
                }

                content.classList.add('opacity-0', 'translate-y-1');
                window.setTimeout(() => {
                    index = (nextIndex + offers.length) % offers.length;
                    render();
                    content.classList.remove('opacity-0', 'translate-y-1');
                }, 170);
            };

            prevBtn?.addEventListener('click', () => animateTo(index - 1));
            nextBtn?.addEventListener('click', () => animateTo(index + 1));

            render();
        })();
    </script>
</x-layouts.student>
