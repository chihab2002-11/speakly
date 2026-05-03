<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class AccountUnapprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $actorId,
        public string $actorName,
        public ?string $actorRole,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'type' => 'account_unapproved',
            'title' => 'Account unapproved',
            'message' => "Your account access was marked as unapproved by {$this->actorName}.",
            'url' => route('pending-approval'),
            'action' => 'unapproved',
            'actor_id' => $this->actorId,
            'actor_name' => $this->actorName,
            'actor_role' => $this->actorRole,
            'related_model' => User::class,
            'related_model_id' => $notifiable->id ?? null,
            'created_at' => now()->toIso8601String(),
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
