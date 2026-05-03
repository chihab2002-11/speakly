<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class SecretaryAnnouncementNotification extends Notification
{
    use BroadcastsDatabaseNotifications;
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public ?string $url,
        public int $issuerId,
        public string $issuerName,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'type' => 'secretary_announcement',
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'issuer_id' => $this->issuerId,
            'issuer_name' => $this->issuerName,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable)->data;
    }
}
