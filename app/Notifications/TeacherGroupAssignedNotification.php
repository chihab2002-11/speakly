<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class TeacherGroupAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $groupId,
        public string $groupName,
        public string $courseName,
        public ?string $programName,
        public int $issuerId,
        public string $issuerName,
        public ?string $url = null,
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
        $programSuffix = $this->programName ? " Program: {$this->programName}." : '';

        return new DatabaseMessage([
            'type' => 'teacher_group_assigned',
            'title' => 'New group assignment',
            'message' => "You have been assigned to teach {$this->groupName} for {$this->courseName}.{$programSuffix}",
            'url' => $this->url,
            'action' => 'assigned',
            'group_id' => $this->groupId,
            'group_name' => $this->groupName,
            'course_name' => $this->courseName,
            'program_name' => $this->programName,
            'issuer_id' => $this->issuerId,
            'issuer_name' => $this->issuerName,
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
