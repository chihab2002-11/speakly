<x-layouts.teacher :title="__('My Payments')" :currentRoute="'teacher.my-payments'">
    @include('employee.my-payments-content', [
        'employee' => $employee,
        'employeePayment' => $employeePayment,
        'paymentData' => $paymentData,
        'receiptUrl' => route('teacher.my-payments.receipt-pdf'),
    ])
</x-layouts.teacher>

