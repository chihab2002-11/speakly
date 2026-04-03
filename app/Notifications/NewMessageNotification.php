<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewMessageNotification extends Notification
{
    use Queueable;

    public function __construct(public Message $message) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $sender = $this->message->sender?->name ?? 'Someone';

        return new DatabaseMessage([
            'title' => 'New message',
            'message' => "You have a new message from {$sender}.",
            'url' => route('messages.show', $this->message),
            'message_id' => $this->message->id,
        ]);
    }
}
