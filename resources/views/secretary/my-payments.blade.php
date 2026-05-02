<x-layouts.secretary :title="__('My Payments')" :current-route="'secretary.my-payments'">
    @include('employee.my-payments-content', [
        'employee' => $employee,
        'employeePayment' => $employeePayment,
        'paymentData' => $paymentData,
        'receiptUrl' => route('secretary.my-payments.receipt-pdf'),
    ])
</x-layouts.secretary>

