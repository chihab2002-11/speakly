<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class StudentAttendanceSavedNotification extends Notification
{
    use BroadcastsDatabaseNotifications;
    use Queueable;

    public function __construct(
        public string $className,
        public string $date,
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
        return new DatabaseMessage([
            'type' => 'attendance',
            'title' => 'Attendance Updated',
            'message' => sprintf(
                'Your attendance and evaluation were updated for %s on %s.',
                $this->className,
                $this->date,
            ),
            'url' => route('student.academic').'#academic-bottom',
            'action' => 'updated',
            'class_name' => $this->className,
            'attendance_date' => $this->date,
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
