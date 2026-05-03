<?php

namespace App\Notifications;

use App\Models\Message;
use App\Support\DashboardRedirector;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class NewMessageNotification extends Notification
{
    use BroadcastsDatabaseNotifications;
    use Queueable;

    public function __construct(public Message $message) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $sender = $this->message->sender?->name ?? 'Someone';

        return new DatabaseMessage([
            'type' => 'message',
            'title' => 'New message',
            'message' => "You have a new message from {$sender}.",
            'url' => route('role.messages.show', DashboardRedirector::routeParametersFor($notifiable, [
                'message' => $this->message,
            ])),
            'message_id' => $this->message->id,
        ]);
    }
}
