<?php

namespace App\Http\Controllers;

use App\Models\ScholarshipActivation;
use App\Models\TuitionPayment;
use App\Models\User;
use App\Support\TuitionFinancialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ParentFinancialController extends Controller
{
    public function __construct(private TuitionFinancialService $tuitionFinancialService) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $selectedOfferKey = $request->query('offer');
        $children = User::query()
            ->where('parent_id', $user->id)
            ->whereNotNull('approved_at')
            ->whereHas('roles', fn ($query) => $query->where('name', 'student'))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->values()
            ->map(function (User $child, int $index): array {
                $theme = $index % 2 === 0
                    ? ['color' => 'var(--lumina-child-1)', 'textColor' => 'var(--lumina-child-1-text)']
                    : ['color' => 'var(--lumina-child-2)', 'textColor' => 'var(--lumina-child-2-text)'];

                return [
                    'id' => $child->id,
                    'name' => $child->name,
                    'initials' => $child->initials(),
                    'grade' => 'Student',
                    'color' => $theme['color'],
                    'textColor' => $theme['textColor'],
                ];
            })
            ->all();

        return view('parent.financial', array_merge(
            ['user' => $user, 'children' => $children],
            $this->tuitionFinancialService->buildParentPageData($user, is_string($selectedOfferKey) ? $selectedOfferKey : null)
        ));
    }

    public function activateScholarship(Request $request): RedirectResponse
    {
        $parent = $request->user();
        $validated = $request->validate([
            'offer_key' => ['required', 'string', 'max:80'],
            'selected_child_id' => ['nullable', 'integer'],
        ]);

        $offerKey = (string) $validated['offer_key'];
        $selectedChildId = isset($validated['selected_child_id']) ? (int) $validated['selected_child_id'] : null;
        $data = $this->tuitionFinancialService->buildParentPageData($parent, $offerKey);
        $offers = collect($data['scholarshipOffers'] ?? []);
        $offer = $offers->firstWhere('key', $offerKey);

        if (! is_array($offer)) {
            return back()->with('error', 'Scholarship offer not found.');
        }

        $effectiveEligibility = (bool) ($offer['isEligible'] ?? false);
        $effectiveStudentId = $offer['studentId'] ?? null;
        $effectiveTargetLabel = $offer['targetLabel'] ?? null;

        $hasChildScopedStats = isset($offer['childStats']) && is_array($offer['childStats']) && ! empty($offer['childStats']);

        if ($hasChildScopedStats && $selectedChildId === null) {
            return back()->with('error', 'Please select a child for this discount offer.');
        }

        if ($selectedChildId !== null && $hasChildScopedStats) {
            $childStat = $offer['childStats'][(string) $selectedChildId] ?? null;
            if (is_array($childStat)) {
                $effectiveEligibility = (bool) ($childStat['isEligible'] ?? false);
                $effectiveStudentId = $childStat['studentId'] ?? $selectedChildId;
                $effectiveTargetLabel = $childStat['targetLabel'] ?? $effectiveTargetLabel;
            } else {
                return back()->with('error', 'Selected child is not valid for this discount offer.');
            }
        }

        if (! $effectiveEligibility) {
            return back()->with('error', 'This discount is not eligible yet.');
        }

        ScholarshipActivation::query()->updateOrCreate(
            [
                'parent_id' => $parent->id,
                'offer_key' => $offerKey,
                'student_id' => $effectiveStudentId,
            ],
            [
                'discount_percent' => (int) ($offer['discountPercent'] ?? 0),
                'activated_at' => now(),
                'meta' => [
                    'title' => $offer['title'] ?? 'Scholarship Offer',
                    'description' => $offer['description'] ?? null,
                    'targetLabel' => $effectiveTargetLabel,
                ],
            ]
        );

        return redirect()
            ->route('parent.financial', ['offer' => $offerKey])
            ->with('success', 'Discount has been activated successfully.');
    }

    public function downloadReceipt(Request $request, TuitionPayment $payment): StreamedResponse|RedirectResponse
    {
        $parent = $request->user();

        $belongsToParent = User::query()
            ->where('id', $payment->student_id)
            ->where('parent_id', $parent->id)
            ->exists();

        if (! $belongsToParent) {
            abort(403);
        }

        $reference = $payment->reference ?: 'PAY-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT);
        $filename = 'receipt-'.$reference.'.txt';
        $content = implode(PHP_EOL, [
            'Lumina Academy Receipt',
            'Reference: '.$reference,
            'Child: '.($payment->student?->name ?? 'Child'),
            'Amount: '.number_format((int) $payment->amount, 0, ',', ' ').' DZD',
            'Date: '.($payment->paid_on?->format('Y-m-d') ?? '-'),
            'Method: '.ucfirst(str_replace('_', ' ', (string) $payment->method)),
        ]).PHP_EOL;

        return response()->streamDownload(
            static function () use ($content): void {
                echo $content;
            },
            $filename,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }
}
