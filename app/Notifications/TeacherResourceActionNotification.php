<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class TeacherResourceActionNotification extends Notification
{
    use BroadcastsDatabaseNotifications;
    use Queueable;

    public function __construct(
        public string $action,
        public string $resourceName,
        public ?int $resourceId = null,
        public ?string $className = null,
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
        $isDelete = $this->action === 'deleted';
        $classSuffix = $this->className ? " for {$this->className}" : '';

        return new DatabaseMessage([
            'type' => 'resource',
            'title' => $isDelete ? 'Resource deleted' : 'Resource uploaded',
            'message' => $isDelete
                ? "You deleted \"{$this->resourceName}\"{$classSuffix}."
                : "You uploaded \"{$this->resourceName}\"{$classSuffix}.",
            'url' => route('teacher.resources'),
            'action' => $isDelete ? 'deleted' : 'uploaded',
            'resource_id' => $this->resourceId,
            'resource_name' => $this->resourceName,
            'class_name' => $this->className,
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
