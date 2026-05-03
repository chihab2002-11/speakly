<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccountRejectedNotification extends Notification
{
    use BroadcastsDatabaseNotifications;
    use Queueable;

    public function __construct(public ?string $reason = null) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'account_rejected',
            'title' => 'Account rejected',
            'message' => 'Your account request was rejected.',
            'reason' => $this->reason,
        ];
    }
}
