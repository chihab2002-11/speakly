<?php

namespace App\Http\Controllers;

use App\Models\ScholarshipActivation;
use App\Models\TuitionPayment;
use App\Support\PaymentReceiptPdf;
use App\Support\TuitionFinancialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class StudentFinancialController extends Controller
{
    public function __construct(
        private TuitionFinancialService $tuitionFinancialService,
        private PaymentReceiptPdf $paymentReceiptPdf,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $selectedOfferKey = $request->query('offer');

        abort_unless($user->canViewStudentFinancialInformation(), 403);

        return view('student.financial', array_merge(
            ['user' => $user],
            $this->tuitionFinancialService->buildStudentPageData(
                $user,
                is_string($selectedOfferKey) ? $selectedOfferKey : null,
            )
        ));
    }

    public function activateScholarship(Request $request): RedirectResponse
    {
        $student = $request->user();

        abort_unless($student->canViewStudentFinancialInformation(), 403);

        $validated = $request->validate([
            'offer_key' => ['required', 'string', 'max:80'],
        ]);

        $offerKey = (string) $validated['offer_key'];
        $data = $this->tuitionFinancialService->buildStudentPageData($student, $offerKey);
        $offer = collect($data['scholarshipOffers'] ?? [])->firstWhere('key', $offerKey);

        if (! is_array($offer)) {
            return back()->with('error', 'Scholarship offer not found.');
        }

        if (! (bool) ($offer['isEligible'] ?? false)) {
            return back()->with('error', 'This discount is not eligible yet.');
        }

        ScholarshipActivation::query()->updateOrCreate(
            [
                'parent_id' => $student->parent_id ?? $student->id,
                'offer_key' => $offerKey,
                'student_id' => $student->id,
            ],
            [
                'discount_percent' => (int) ($offer['discountPercent'] ?? 0),
                'activated_at' => now(),
                'meta' => [
                    'title' => $offer['title'] ?? 'Scholarship Offer',
                    'description' => $offer['description'] ?? null,
                    'targetLabel' => $offer['targetLabel'] ?? 'Your enrollment',
                ],
            ]
        );

        return redirect()
            ->route('student.financial', ['offer' => $offerKey])
            ->with('success', 'Discount has been activated successfully.');
    }

    public function receiptPdf(Request $request, TuitionPayment $payment): Response
    {
        $student = $request->user();

        abort_unless($student->canViewStudentFinancialInformation(), 403);
        abort_unless((int) $payment->student_id === (int) $student->id, 403);

        $payment->loadMissing('student:id,name,email');

        $pdf = $this->paymentReceiptPdf->render(
            student: $student,
            payment: $payment,
            financialSummary: $this->tuitionFinancialService->buildStudentPageData($student),
        );

        $receiptIdentifier = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($payment->reference ?: $payment->id));
        $filename = 'payment-receipt-'.$receiptIdentifier.'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.addslashes($filename).'"',
        ]);
    }
}
