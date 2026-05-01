<x-layouts.parent
    :title="'Financial Information'"
    :pageTitle="'Financial Information'"
    :currentRoute="'financial'"
    :user="$user ?? null"
    :children="$children ?? []"
>
    @php
        $offers = collect($scholarshipOffers ?? [])->values();
        $selectedOffer = is_array($selectedScholarshipOffer ?? null) ? $selectedScholarshipOffer : $offers->first();
        $initialOfferKey = $selectedOffer['key'] ?? ($offers->first()['key'] ?? null);
        $initialChildId = isset($children[0]['id']) ? (int) $children[0]['id'] : null;
        $activeDiscountPercent = (int) ($scholarshipDiscount ?? 0);
    @endphp

    <style>
        .lumina-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: #94d3bb #edf7f1;
        }
        .lumina-scrollbar::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        .lumina-scrollbar::-webkit-scrollbar-track {
            background: #edf7f1;
            border-radius: 999px;
        }
        .lumina-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #16a34a 0%, #0f766e 100%);
            border-radius: 999px;
            border: 2px solid #edf7f1;
        }
    </style>

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

    <div class="mb-8 flex flex-col gap-6">
        <div class="flex flex-col gap-3">
            <div class="inline-flex w-fit items-center gap-2 rounded-full px-3 py-1" style="background-color: #DDE1FF; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);">
                <svg class="h-3 w-3" fill="currentColor" style="color: #001453;" viewBox="0 0 24 24">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
                </svg>
                <span class="text-xs font-bold" style="color: #001453; letter-spacing: 0.3px;">Verified Parent Account</span>
            </div>

            <h1 class="text-5xl font-extrabold" style="color: #181D19; letter-spacing: -2.4px;">Financial Ledger</h1>
            <p class="text-base font-medium" style="color: #3F4941;">Lumina Academy Central Billing Portal</p>

            <div class="mt-2 flex flex-wrap items-center gap-2 text-sm" style="color: #3F4941;">
                <span class="rounded-full px-3 py-1" style="background-color: #E5E9E3;">Outstanding before discount: {{ number_format($totalOutstandingBeforeDiscount ?? 0, 0, ',', ' ') }} DZD</span>
                <span class="rounded-full px-3 py-1" style="background-color: #D1FAE5; color: #065F46;">Active discount: {{ $activeDiscountPercent }}%</span>
                <span class="rounded-full px-3 py-1" style="background-color: #F0F5EE;">Discount amount: {{ number_format($discountAmount ?? 0, 0, ',', ' ') }} DZD</span>
            </div>
        </div>
    </div>

    <div class="relative grid gap-8 lg:grid-cols-3">
        <div class="flex flex-col gap-6 lg:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold" style="color: #181D19; letter-spacing: -0.6px;">Tuition Ledger</h2>
                <span class="rounded-full px-3 py-1 text-xs font-semibold" style="background-color: #E5E9E3; color: #3F4941;">{{ $academicYear ?? date('Y') . '/' . (date('Y') + 1) }} Academic Year</span>
            </div>

            <div id="ledgerFilterButtons" class="inline-flex items-center gap-2 rounded-lg border p-1" style="border-color: var(--lumina-border); background-color: #F8FBF8;">
                <button type="button" data-ledger-filter="all" class="ledger-filter-btn rounded-lg px-4 py-2 text-sm font-bold uppercase tracking-wide transition-all" style="background: linear-gradient(135deg, #0E7A4E 0%, #0A5E3D 100%); color: #ffffff; box-shadow: 0 6px 14px rgba(10, 94, 61, 0.2);">All</button>
                <button type="button" data-ledger-filter="paid" class="ledger-filter-btn rounded-lg px-4 py-2 text-sm font-bold uppercase tracking-wide transition-all" style="background: transparent; color: var(--lumina-text-secondary);">Paid</button>
                <button type="button" data-ledger-filter="outstanding" class="ledger-filter-btn rounded-lg px-4 py-2 text-sm font-bold uppercase tracking-wide transition-all" style="background: transparent; color: var(--lumina-text-secondary);">Outstanding</button>
            </div>

            <div class="overflow-hidden rounded-xl border" style="background-color: #FFFFFF; border-color: rgba(190, 201, 191, 0.15);">
                <div class="lumina-scrollbar max-h-[460px] overflow-y-auto">
                <table class="w-full">
                    <thead>
                        <tr style="background-color: #F0F5EE;">
                            <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #3F4941; letter-spacing: 1.2px;">Child / Service</th>
                            <th class="px-4 py-4 text-left text-xs font-bold uppercase tracking-wider" style="color: #3F4941; letter-spacing: 1.2px;">Discount Applied</th>
                            <th class="px-4 py-4 text-right text-xs font-bold uppercase tracking-wider" style="color: #3F4941; letter-spacing: 1.2px;">Price</th>
                            <th class="px-4 py-4 text-center text-xs font-bold uppercase tracking-wider" style="color: #3F4941; letter-spacing: 1.2px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($ledgerItems ?? []) as $item)
                            @php
                                $discountPercent = (int) ($item['discount_percent'] ?? 0);
                                $finalPrice = (int) ($item['final_amount'] ?? $item['amount'] ?? 0);
                                $status = ($item['status'] ?? 'outstanding') === 'outstanding' ? 'outstanding' : 'paid';
                            @endphp
                            <tr class="border-t ledger-row" data-ledger-status="{{ $status }}" style="border-color: rgba(190, 201, 191, 0.15);">
                                <td class="px-4 py-5">
                                    <p class="text-base font-bold" style="color: #181D19;">{{ $item['child'] ?? 'Child' }}</p>
                                    <p class="text-sm" style="color: #3F4941;">{{ $item['name'] ?? 'Tuition' }} &bull; {{ $item['period'] ?? '-' }}</p>
                                </td>
                                <td class="px-4 py-5 text-sm font-semibold" style="color: #3F4941;">{{ $discountPercent }}%</td>
                                <td class="px-4 py-5 text-right text-sm font-black" style="color: #047857;">{{ number_format($finalPrice, 0, ',', ' ') }} DZD</td>
                                <td class="px-4 py-5 text-center">
                                    @if($status === 'outstanding')
                                        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold uppercase" style="background-color: #FFDAD6; color: #93000A;">Outstanding</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold uppercase" style="background-color: #C1E6CC; color: #476853;">Paid</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm" style="color: #3F4941;">No financial ledger entries yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4 lg:col-span-1">
            <h2 class="text-2xl font-bold" style="color: #181D19; letter-spacing: -0.6px;">Scholarships</h2>

            <div id="scholarshipCard" class="relative overflow-hidden rounded-xl p-8" style="background: linear-gradient(135deg, #065F46 0%, #022C22 100%); box-shadow: 0px 20px 25px -5px rgba(0, 0, 0, 0.1), 0px 8px 10px -6px rgba(0, 0, 0, 0.1);">
                <div class="pointer-events-none absolute -right-16 -top-16 h-32 w-32 rounded-full blur-[20px]" style="background: rgba(255, 255, 255, 0.05);"></div>

                <div id="scholarshipCardContent" class="relative flex flex-col gap-4 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <button id="scholarshipPrevBtn" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full border text-white/80 transition-all hover:scale-105 hover:text-white" style="border-color: rgba(255,255,255,0.35); background: rgba(255,255,255,0.08);">&larr;</button>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg" style="background: rgba(255, 255, 255, 0.1);">
                            <svg class="h-5 w-5" fill="currentColor" style="color: #6EE7B7;" viewBox="0 0 24 24">
                                <path d="M19 5h-2V3H7v2H5c-1.1 0-2 .9-2 2v1c0 2.55 1.92 4.63 4.39 4.94.63 1.5 1.98 2.63 3.61 2.96V19H7v2h10v-2h-4v-3.1c1.63-.33 2.98-1.46 3.61-2.96C19.08 12.63 21 10.55 21 8V7c0-1.1-.9-2-2-2zM5 8V7h2v3.82C5.84 10.4 5 9.3 5 8zm14 0c0 1.3-.84 2.4-2 2.82V7h2v1z"/>
                            </svg>
                        </div>
                        <button id="scholarshipNextBtn" type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-full border text-white/80 transition-all hover:scale-105 hover:text-white" style="border-color: rgba(255,255,255,0.35); background: rgba(255,255,255,0.08);">&rarr;</button>
                    </div>

                    <div id="scholarshipChildPickerWrap" class="hidden">
                        <label for="scholarshipChildSelect" class="mb-1 block text-[11px] font-bold uppercase tracking-wider" style="color: rgba(209, 250, 229, 0.95); letter-spacing: 1.1px;">Offer Child</label>
                        <div class="rounded-xl border p-1" style="border-color: rgba(255,255,255,0.28); background: rgba(255,255,255,0.08);">
                            <select id="scholarshipChildSelect" class="w-full rounded-lg px-3 py-2 text-sm font-semibold outline-none" style="background: rgba(6, 78, 59, 0.85); color: #ECFDF5;">
                                @forelse($children as $child)
                                    <option value="{{ $child['id'] }}" @selected($initialChildId === (int) $child['id'])>{{ $child['name'] }}</option>
                                @empty
                                    <option value="">No child</option>
                                @endforelse
                            </select>
                        </div>
                    </div>

                    <h3 id="scholarshipTitle" class="text-3xl font-bold leading-tight text-white">{{ $selectedOffer['title'] ?? 'Scholarship Offer' }}</h3>
                    <p id="scholarshipTarget" class="text-sm font-semibold uppercase tracking-wide" style="color: rgba(209, 250, 229, 0.95);">{{ $selectedOffer['targetLabel'] ?? 'Target' }}</p>
                    <p id="scholarshipDescription" class="text-base leading-relaxed" style="color: rgba(209, 250, 229, 0.8);">{{ $selectedOffer['description'] ?? 'Scholarship details unavailable.' }}</p>
                    <div id="scholarshipProgress" class="rounded-lg px-3 py-2 text-sm" style="background: rgba(255,255,255,0.1); color: #D1FAE5;">Progress: {{ (int) ($selectedOffer['progressPercent'] ?? 0) }}%</div>

                    <div class="mt-2 flex items-end justify-between">
                        <span id="scholarshipPercent" class="text-5xl font-black text-white">{{ (int) ($selectedOffer['discountPercent'] ?? 0) }}%</span>
                        <span id="scholarshipState" class="text-sm font-bold uppercase tracking-wider" style="color: #6EE7B7; letter-spacing: 1.2px;">{{ ($selectedOffer['isActive'] ?? false) ? 'Activated' : 'Available Offer' }}</span>
                    </div>

                    <form method="POST" action="{{ route('parent.financial.scholarships.activate') }}" class="mt-2">
                        @csrf
                        <input id="scholarshipOfferKeyInput" type="hidden" name="offer_key" value="{{ $selectedOffer['key'] ?? '' }}">
                        <input id="scholarshipChildIdInput" type="hidden" name="selected_child_id" value="{{ $initialChildId }}">
                        <button id="scholarshipActivateBtn" type="submit" class="w-full rounded-lg px-4 py-2 text-sm font-bold text-white transition-opacity disabled:cursor-not-allowed disabled:opacity-50" style="background-color: #10B981;">
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

    <div class="mt-10 flex flex-col gap-6">
        <h2 class="text-2xl font-bold" style="color: #181D19; letter-spacing: -0.6px;">Payment History</h2>

        <section class="overflow-hidden rounded-3xl border" style="background: white; border-color: var(--lumina-border-light);">
            <div class="lumina-scrollbar max-h-[360px] overflow-y-auto">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead style="background-color: #F8FAFC;">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-muted);">Child</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-muted);">Paid For</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-muted);">Amount Paid</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-muted);">Discount Applied</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-muted);">Date</th>
                            <th class="px-4 py-3 text-left font-semibold" style="color: var(--lumina-text-muted);">Method</th>
                            <th class="px-4 py-3 text-right font-semibold" style="color: var(--lumina-text-muted);">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(($paymentHistory ?? []) as $payment)
                            <tr class="border-t" style="border-color: var(--lumina-border);">
                                <td class="px-4 py-4"><p class="font-semibold" style="color: var(--lumina-text-primary);">{{ $payment['child'] ?? 'Child' }}</p></td>
                                <td class="px-4 py-4"><p class="font-semibold" style="color: var(--lumina-text-primary);">{{ $payment['description'] ?? 'Tuition Payment' }}</p><p class="text-xs" style="color: var(--lumina-text-muted);">{{ $payment['id'] ?? 'N/A' }}</p></td>
                                <td class="px-4 py-4 font-semibold" style="color: #15803D;">{{ number_format((int) ($payment['amount'] ?? 0), 0, ',', ' ') }} DZD</td>
                                <td class="px-4 py-4" style="color: var(--lumina-text-secondary);">{{ $payment['discountApplied'] ?? 'None' }}</td>
                                <td class="px-4 py-4" style="color: var(--lumina-text-secondary);">{{ $payment['paidDate'] ?? '-' }}</td>
                                <td class="px-4 py-4" style="color: var(--lumina-text-secondary);">{{ $payment['method'] ?? 'Cash' }}</td>
                                <td class="px-4 py-4 text-right">
                                    @if(!empty($payment['receiptUrl']))
                                        <a href="{{ $payment['receiptUrl'] }}" target="_blank" class="rounded-lg border px-3 py-1.5 text-xs font-semibold" style="border-color: var(--lumina-border); color: var(--lumina-primary);">Print Receipt</a>
                                    @else
                                        <span class="rounded-lg border px-3 py-1.5 text-xs font-semibold opacity-60" style="border-color: var(--lumina-border); color: var(--lumina-primary);">Print Receipt</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-sm" style="color: var(--lumina-text-muted);">No paid transactions available yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            </div>
        </section>
    </div>

    <script id="parentScholarshipOffers" type="application/json">@json($offers->values())</script>
    <script id="parentScholarshipInitialOfferKey" type="application/json">@json($initialOfferKey)</script>
    <script>
        (function () {
            const offers = JSON.parse(document.getElementById('parentScholarshipOffers')?.textContent || '[]');
            const initialOfferKey = JSON.parse(document.getElementById('parentScholarshipInitialOfferKey')?.textContent || 'null');

            const prevBtn = document.getElementById('scholarshipPrevBtn');
            const nextBtn = document.getElementById('scholarshipNextBtn');
            const childPickerWrap = document.getElementById('scholarshipChildPickerWrap');
            const childSelect = document.getElementById('scholarshipChildSelect');
            const content = document.getElementById('scholarshipCardContent');
            const titleEl = document.getElementById('scholarshipTitle');
            const targetEl = document.getElementById('scholarshipTarget');
            const descriptionEl = document.getElementById('scholarshipDescription');
            const progressEl = document.getElementById('scholarshipProgress');
            const percentEl = document.getElementById('scholarshipPercent');
            const stateEl = document.getElementById('scholarshipState');
            const activateBtn = document.getElementById('scholarshipActivateBtn');
            const offerKeyInput = document.getElementById('scholarshipOfferKeyInput');
            const childIdInput = document.getElementById('scholarshipChildIdInput');

            if (!offers.length || !titleEl) {
                return;
            }

            let index = Math.max(0, offers.findIndex((offer) => offer.key === initialOfferKey));
            let selectedChildId = Number(childSelect?.value || 0);

            const applyChildStats = (offer) => {
                const hasChildStats = offer.childStats && typeof offer.childStats === 'object' && Object.keys(offer.childStats).length > 0;
                const stats = hasChildStats ? offer.childStats[String(selectedChildId)] : null;
                const activeStudentIds = Array.isArray(offer.activeStudentIds)
                    ? offer.activeStudentIds.map((value) => Number(value))
                    : [];
                const isActiveForSelectedChild = hasChildStats
                    ? activeStudentIds.includes(Number((stats?.studentId ?? selectedChildId) || 0))
                    : Boolean(offer.isActive);

                return {
                    ...offer,
                    targetLabel: stats?.targetLabel ?? offer.targetLabel,
                    progressPercent: Number(stats?.progressPercent ?? offer.progressPercent ?? 0),
                    remainingText: stats?.remainingText ?? offer.remainingText,
                    isEligible: Boolean(stats?.isEligible ?? offer.isEligible),
                    effectiveStudentId: Number((stats?.studentId ?? selectedChildId) || 0),
                    hasChildStats,
                    isActiveForSelectedChild,
                };
            };

            const render = () => {
                const baseOffer = offers[index] || offers[0];
                const offer = applyChildStats(baseOffer);

                titleEl.textContent = offer.title || 'Scholarship Offer';
                targetEl.textContent = offer.targetLabel || 'Target';
                descriptionEl.textContent = offer.description || 'Scholarship details unavailable.';
                progressEl.innerHTML = 'Progress: ' + Number(offer.progressPercent || 0) + '%<br>' + (offer.remainingText || '');
                percentEl.textContent = Number(offer.discountPercent || 0) + '%';
                stateEl.textContent = offer.isActiveForSelectedChild ? 'Activated' : 'Available Offer';

                if (childPickerWrap) {
                    childPickerWrap.classList.toggle('hidden', !offer.hasChildStats);
                }

                if (offerKeyInput) {
                    offerKeyInput.value = offer.key || '';
                }

                if (childIdInput) {
                    childIdInput.value = offer.hasChildStats && offer.effectiveStudentId > 0
                        ? String(offer.effectiveStudentId)
                        : '';
                }

                if (activateBtn) {
                    const canActivate = Boolean(offer.isEligible) && !Boolean(offer.isActiveForSelectedChild);
                    activateBtn.disabled = !canActivate;
                    activateBtn.textContent = offer.isActiveForSelectedChild
                        ? 'Active Discount'
                        : (canActivate ? 'Activate Discount' : 'Not Eligible Yet');
                }
            };

            const animateTo = (nextIndex) => {
                if (!content) {
                    index = nextIndex;
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
            nextBtn?.addEventListener('click', () => animateTo(index - 1));

            childSelect?.addEventListener('change', () => {
                selectedChildId = Number(childSelect.value || 0);
                render();
            });

            render();
        })();
    </script>

    <script>
        (function () {
            const ledgerFilterButtons = document.querySelectorAll('.ledger-filter-btn');
            const ledgerRows = document.querySelectorAll('.ledger-row');
            let currentFilter = 'all';

            function updateButtonStyles() {
                ledgerFilterButtons.forEach((button) => {
                    const isActive = button.dataset.ledgerFilter === currentFilter;
                    if (isActive) {
                        button.style.background = 'linear-gradient(135deg, #0E7A4E 0%, #0A5E3D 100%)';
                        button.style.color = '#ffffff';
                        button.style.boxShadow = '0 6px 14px rgba(10, 94, 61, 0.2)';
                    } else {
                        button.style.background = 'transparent';
                        button.style.color = 'var(--lumina-text-secondary)';
                        button.style.boxShadow = 'none';
                    }
                });
            }

            function filterRows() {
                ledgerRows.forEach((row) => {
                    const status = row.dataset.ledgerStatus;
                    if (currentFilter === 'all' || status === currentFilter) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            ledgerFilterButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    currentFilter = button.dataset.ledgerFilter;
                    updateButtonStyles();
                    filterRows();
                });
            });

            updateButtonStyles();
        })();
    </script>
</x-layouts.parent>
