<?php

namespace App\Notifications;

use App\Models\CourseClass;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class StudentGroupEnrollmentChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $action,
        public int $groupId,
        public string $groupName,
        public string $courseName,
        public ?string $programName,
        public int $actorId,
        public string $actorName,
        public ?string $actorRole,
        public string $recipientType,
        public ?int $childId = null,
        public ?string $childName = null,
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
        $isEnrolled = $this->action === 'enrolled';
        $title = $isEnrolled ? 'Enrolled in group' : 'Removed from group';
        $actorLabel = trim($this->actorName.($this->actorRole ? " ({$this->actorRole})" : ''));
        $programSuffix = $this->programName ? " in {$this->programName}" : '';

        $message = $this->recipientType === 'parent'
            ? sprintf(
                'Your child %s was %s %s for %s%s by %s.',
                $this->childName ?? 'your child',
                $isEnrolled ? 'enrolled in' : 'removed from',
                $this->groupName,
                $this->courseName,
                $programSuffix,
                $actorLabel,
            )
            : sprintf(
                'You were %s %s for %s%s by %s.',
                $isEnrolled ? 'enrolled in' : 'removed from',
                $this->groupName,
                $this->courseName,
                $programSuffix,
                $actorLabel,
            );

        return new DatabaseMessage([
            'type' => 'student_group_enrollment_changed',
            'title' => $title,
            'message' => $message,
            'url' => $this->url,
            'action' => $this->action,
            'actor_id' => $this->actorId,
            'actor_name' => $this->actorName,
            'actor_role' => $this->actorRole,
            'issuer_id' => $this->actorId,
            'issuer_name' => $this->actorName,
            'related_model' => CourseClass::class,
            'related_model_id' => $this->groupId,
            'created_at' => now()->toIso8601String(),
            'group_id' => $this->groupId,
            'group_name' => $this->groupName,
            'course_name' => $this->courseName,
            'program_name' => $this->programName,
            'recipient_type' => $this->recipientType,
            'child_id' => $this->childId,
            'child_name' => $this->childName,
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
