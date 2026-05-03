<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class TeacherAttendanceSavedNotification extends Notification
{
    use BroadcastsDatabaseNotifications;
    use Queueable;

    public function __construct(
        public int $classId,
        public string $className,
        public string $date,
        public int $recordsCount,
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
            'title' => 'Attendance saved',
            'message' => sprintf(
                'You saved attendance for %d student%s in %s on %s.',
                $this->recordsCount,
                $this->recordsCount === 1 ? '' : 's',
                $this->className,
                $this->date,
            ),
            'url' => route('teacher.attendance', [
                'class_id' => $this->classId,
                'date' => $this->date,
            ]),
            'action' => 'saved',
            'class_id' => $this->classId,
            'class_name' => $this->className,
            'attendance_date' => $this->date,
            'records_count' => $this->recordsCount,
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
