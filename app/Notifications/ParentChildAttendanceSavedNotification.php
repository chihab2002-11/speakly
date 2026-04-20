<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class ParentChildAttendanceSavedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $childId,
        public string $childName,
        public string $className,
        public string $date,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'type' => 'attendance',
            'title' => 'Child attendance updated',
            'message' => sprintf(
                '%s attendance and evaluation were updated for %s on %s.',
                $this->childName,
                $this->className,
                $this->date,
            ),
            'url' => route('role.dashboard', ['role' => 'parent', 'child' => $this->childId]),
            'action' => 'updated',
            'child_id' => $this->childId,
            'child_name' => $this->childName,
            'class_name' => $this->className,
            'attendance_date' => $this->date,
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
