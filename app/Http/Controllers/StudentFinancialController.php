<?php

namespace App\Http\Controllers;

use App\Models\TuitionPayment;
use App\Support\PaymentReceiptPdf;
use App\Support\TuitionFinancialService;
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

        abort_unless($user->canViewStudentFinancialInformation(), 403);

        return view('student.financial', array_merge(
            ['user' => $user],
            $this->tuitionFinancialService->buildStudentPageData($user)
        ));
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
