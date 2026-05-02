<?php

namespace App\Http\Controllers;

use App\Models\EmployeePayment;
use App\Models\User;
use App\Support\EmployeePaymentReceiptPdf;
use App\Support\EmployeePaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EmployeeMyPaymentsController extends Controller
{
    public function __construct(
        private EmployeePaymentService $employeePaymentService,
        private EmployeePaymentReceiptPdf $receiptPdf,
    ) {}

    public function teacherIndex(Request $request): View
    {
        $employee = $this->currentEmployee($request);
        $employeePayment = $employee->employeePayment;
        $paymentData = $this->employeePaymentService->employeeRow($employee);

        return view('teacher.my-payments', [
            'employee' => $employee,
            'employeePayment' => $employeePayment,
            'paymentData' => $paymentData,
        ]);
    }

    public function secretaryIndex(Request $request): View
    {
        $employee = $this->currentEmployee($request);
        $employeePayment = $employee->employeePayment;
        $paymentData = $this->employeePaymentService->employeeRow($employee);

        return view('secretary.my-payments', [
            'employee' => $employee,
            'employeePayment' => $employeePayment,
            'paymentData' => $paymentData,
        ]);
    }

    public function teacherReceiptPdf(Request $request): Response
    {
        $employee = $this->currentEmployee($request);
        $employee->loadMissing(['employeePayment', 'roles:id,name']);

        $employeePayment = $employee->employeePayment ?? new EmployeePayment;
        $paymentData = $this->employeePaymentService->employeeRow($employee);

        $pdf = $this->receiptPdf->render(
            employee: $employee,
            employeePayment: $employeePayment,
            paymentData: $paymentData,
        );

        return $this->pdfResponse($employee, $pdf);
    }

    public function secretaryReceiptPdf(Request $request): Response
    {
        $employee = $this->currentEmployee($request);
        $employee->loadMissing(['employeePayment', 'roles:id,name']);

        $employeePayment = $employee->employeePayment ?? new EmployeePayment;
        $paymentData = $this->employeePaymentService->employeeRow($employee);

        $pdf = $this->receiptPdf->render(
            employee: $employee,
            employeePayment: $employeePayment,
            paymentData: $paymentData,
        );

        return $this->pdfResponse($employee, $pdf);
    }

    private function currentEmployee(Request $request): User
    {
        /** @var User $employee */
        $employee = $request->user();

        $employee->loadMissing(['roles:id,name', 'employeePayment']);

        abort_unless(
            $employee->approved_at !== null && $employee->roles->pluck('name')->intersect(['teacher', 'secretary'])->isNotEmpty(),
            404
        );

        return $employee;
    }

    private function pdfResponse(User $employee, string $pdf): Response
    {
        $receiptIdentifier = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($employee->email ?? $employee->id));
        $filename = 'employee-payment-receipt-'.$receiptIdentifier.'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.addslashes($filename).'"',
        ]);
    }
}
