<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class ClassResourceUploadedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $resourceId,
        public int $classId,
        public string $recipientType,
        public string $courseName,
        public string $groupName,
        public string $resourceCategory,
        public ?string $deadline = null,
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
        $isHomework = $this->resourceCategory === 'homework';
        $type = $isHomework ? 'homework_uploaded' : 'class_resource_uploaded';
        $classLabel = "{$this->courseName} - {$this->groupName}";
        $deadlineSuffix = $isHomework && $this->deadline ? " Deadline: {$this->deadline}." : '';

        if ($this->recipientType === 'parent') {
            $title = $isHomework ? 'New homework for your child' : 'New course resource for your child';
            $message = $isHomework
                ? "A homework was uploaded for your child {$this->childName} in {$classLabel}.{$deadlineSuffix}"
                : "A new course resource was uploaded for your child {$this->childName} in {$classLabel}.";
        } else {
            $title = $isHomework ? 'New homework uploaded' : 'New course resource uploaded';
            $message = $isHomework
                ? "Your teacher uploaded homework for {$classLabel}.{$deadlineSuffix}"
                : "A new course resource was uploaded for {$classLabel}.";
        }

        return new DatabaseMessage([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'url' => $this->url,
            'action' => 'uploaded',
            'resource_id' => $this->resourceId,
            'class_id' => $this->classId,
            'recipient_type' => $this->recipientType,
            'course_name' => $this->courseName,
            'group_name' => $this->groupName,
            'category' => $this->resourceCategory,
            'deadline' => $this->deadline,
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
