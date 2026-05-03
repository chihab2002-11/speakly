<?php

namespace App\Notifications;

use App\Models\CourseClass;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class TeacherGroupAssignedNotification extends Notification
{
    use BroadcastsDatabaseNotifications;
    use Queueable;

    public function __construct(
        public int $groupId,
        public string $groupName,
        public string $courseName,
        public ?string $programName,
        public int $issuerId,
        public string $issuerName,
        public ?string $url = null,
        public string $action = 'assigned',
        public ?string $actorRole = null,
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
        $programSuffix = $this->programName ? " Program: {$this->programName}." : '';
        $isRemoved = $this->action === 'removed';

        return new DatabaseMessage([
            'type' => $isRemoved ? 'teacher_group_removed' : 'teacher_group_assigned',
            'title' => $isRemoved ? 'Group assignment removed' : 'New group assignment',
            'message' => $isRemoved
                ? "You are no longer assigned to teach {$this->groupName} for {$this->courseName}.{$programSuffix}"
                : "You have been assigned to teach {$this->groupName} for {$this->courseName}.{$programSuffix}",
            'url' => $this->url,
            'action' => $this->action,
            'actor_id' => $this->issuerId,
            'actor_name' => $this->issuerName,
            'actor_role' => $this->actorRole,
            'related_model' => CourseClass::class,
            'related_model_id' => $this->groupId,
            'created_at' => now()->toIso8601String(),
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
