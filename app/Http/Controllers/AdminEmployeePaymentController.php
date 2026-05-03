<?php

namespace App\Http\Controllers;

use App\Models\EmployeePayment;
use App\Models\User;
use App\Support\EmployeePaymentReceiptPdf;
use App\Support\EmployeePaymentService;
use App\Support\RoleNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdminEmployeePaymentController extends Controller
{
    public function __construct(
        private EmployeePaymentService $employeePaymentService,
        private EmployeePaymentReceiptPdf $receiptPdf,
        private RoleNotificationService $roleNotificationService,
    ) {}

    public function index(Request $request): View
    {
        $role = strtolower((string) $request->query('role', 'all'));
        $status = strtolower((string) $request->query('status', 'all'));
        $search = trim((string) $request->query('search', ''));

        $role = in_array($role, ['all', 'teacher', 'secretary'], true) ? $role : 'all';
        $status = in_array($status, ['all', 'paid', 'unpaid', 'partial', 'pending'], true) ? $status : 'all';

        $overview = $this->employeePaymentService->overview($role, $status, $search);

        return view('admin.employee-payments', [
            'rows' => $overview['rows'],
            'totals' => $overview['totals'],
            'role' => $role,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function update(Request $request, User $employee): RedirectResponse
    {
        $this->ensurePayableEmployee($employee);

        $validated = $request->validate([
            'expected_salary' => ['required', 'integer', 'min:0', 'max:100000000'],
            'amount_paid' => ['required', 'integer', 'min:0', 'max:100000000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee->load('employeePayment');
        $previousAmountPaid = $employee->employeePayment?->amount_paid;
        $newPaymentAmount = (int) $validated['amount_paid'];
        $totalAmountPaid = (int) ($previousAmountPaid ?? 0) + $newPaymentAmount;

        $employee->employeePayment()->updateOrCreate(
            [],
            [
                'recorded_by' => $request->user()->id,
                'expected_salary' => (int) $validated['expected_salary'],
                'amount_paid' => $totalAmountPaid,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        $employee->refresh()->loadMissing(['employeePayment', 'roles:id,name']);

        $this->roleNotificationService->notifyEmployeePaymentChanged(
            employee: $employee,
            previousAmountPaid: $previousAmountPaid,
            paymentData: $this->employeePaymentService->employeeRow($employee),
        );

        return redirect()
            ->route('admin.employee-payments.index', $request->only(['role', 'status', 'search']))
            ->with('success', 'Employee payment details updated successfully.');
    }

    public function show(Request $request, User $employee): View
    {
        $this->ensurePayableEmployee($employee);

        $employee->load(['employeePayment', 'roles:id,name']);

        $employeePayment = $employee->employeePayment ?? new EmployeePayment;
        $paymentData = $this->employeePaymentService->employeeRow($employee);

        return view('admin.employee-payment-details', [
            'employee' => $employee,
            'employeePayment' => $employeePayment,
            'paymentData' => $paymentData,
        ]);
    }

    public function receiptPdf(Request $request, User $employee): Response
    {
        $this->ensurePayableEmployee($employee);

        $employee->load(['employeePayment', 'roles:id,name']);

        $employeePayment = $employee->employeePayment ?? new EmployeePayment;
        $paymentData = $this->employeePaymentService->employeeRow($employee);

        $pdf = $this->receiptPdf->render(
            employee: $employee,
            employeePayment: $employeePayment,
            paymentData: $paymentData,
        );

        $receiptIdentifier = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($employee->email ?? $employee->id));
        $filename = 'employee-payment-receipt-'.$receiptIdentifier.'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.addslashes($filename).'"',
        ]);
    }

    private function ensurePayableEmployee(User $employee): void
    {
        $employee->loadMissing('roles:id,name');

        abort_unless(
            $employee->approved_at !== null && $employee->roles->pluck('name')->intersect(['teacher', 'secretary'])->isNotEmpty(),
            404
        );
    }
}
