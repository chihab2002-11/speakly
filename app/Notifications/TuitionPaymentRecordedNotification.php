<?php

namespace App\Notifications;

use App\Models\TuitionPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class TuitionPaymentRecordedNotification extends Notification
{
    use BroadcastsDatabaseNotifications;
    use Queueable;

    public function __construct(
        public int $paymentId,
        public int $amount,
        public string $paidOn,
        public string $method,
        public int $actorId,
        public string $actorName,
        public ?string $actorRole,
        public string $recipientType,
        public ?int $childId = null,
        public ?string $childName = null,
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
        $actorLabel = trim($this->actorName.($this->actorRole ? " ({$this->actorRole})" : ''));
        $amount = number_format($this->amount, 0, '.', ',').' DZD';

        $message = $this->recipientType === 'parent'
            ? "A tuition payment of {$amount} was recorded for {$this->childName} by {$actorLabel}."
            : "A tuition payment of {$amount} was recorded for your account by {$actorLabel}.";

        return new DatabaseMessage([
            'type' => 'tuition_payment_recorded',
            'title' => 'Tuition payment recorded',
            'message' => $message,
            'url' => $this->url,
            'action' => 'recorded',
            'actor_id' => $this->actorId,
            'actor_name' => $this->actorName,
            'actor_role' => $this->actorRole,
            'related_model' => TuitionPayment::class,
            'related_model_id' => $this->paymentId,
            'created_at' => now()->toIso8601String(),
            'payment_id' => $this->paymentId,
            'amount' => $this->amount,
            'paid_on' => $this->paidOn,
            'method' => $this->method,
            'recipient_type' => $this->recipientType,
            'child_id' => $this->childId,
            'child_name' => $this->childName,
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
}
