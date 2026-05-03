<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class EmployeePaymentRecordedNotification extends Notification
{
    use BroadcastsDatabaseNotifications;
    use Queueable;

    public function __construct(
        public int $paidAmount,
        public int $remainingAmount,
        public int $fullSalary,
        public string $status,
        public ?string $url = null,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $isFullyPaid = $this->status === 'paid';

        return new DatabaseMessage([
            'type' => 'employee_payment_recorded',
            'title' => $isFullyPaid ? 'Salary fully paid' : 'Payment recorded',
            'message' => $isFullyPaid
                ? sprintf(
                    'You received a payment of %s. Your salary for this period is now fully paid. Full salary: %s.',
                    $this->formatMoney($this->paidAmount),
                    $this->formatMoney($this->fullSalary),
                )
                : sprintf(
                    'You received a payment of %s. Remaining salary: %s. Full salary: %s.',
                    $this->formatMoney($this->paidAmount),
                    $this->formatMoney($this->remainingAmount),
                    $this->formatMoney($this->fullSalary),
                ),
            'url' => $this->url,
            'action' => 'recorded',
            'paid_amount' => $this->paidAmount,
            'remaining_amount' => $this->remainingAmount,
            'full_salary' => $this->fullSalary,
            'status' => $this->status,
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable)->data;
    }

    private function formatMoney(int $amount): string
    {
        return number_format($amount, 0, '.', ',').' DZD';
    }
}
