<?php

namespace App\Notifications;

use App\Models\Schedule;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class ScheduleChangedNotification extends Notification
{
    use BroadcastsDatabaseNotifications;
    use Queueable;

    public function __construct(
        public string $action,
        public int $scheduleId,
        public int $groupId,
        public string $groupName,
        public string $courseName,
        public string $dayOfWeek,
        public string $startTime,
        public string $endTime,
        public ?string $roomName,
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
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): DatabaseMessage
    {
        $title = match ($this->action) {
            'created' => 'Schedule created',
            'updated' => 'Schedule updated',
            'cancelled' => 'Schedule cancelled',
            default => 'Schedule changed',
        };
        $timeLabel = "{$this->dayOfWeek} {$this->startTime}-{$this->endTime}";
        $roomSuffix = $this->roomName ? " in {$this->roomName}" : '';
        $target = $this->recipientType === 'parent'
            ? "your child {$this->childName}"
            : ($this->recipientType === 'teacher' ? 'your teaching group' : 'your class');

        return new DatabaseMessage([
            'type' => 'schedule_changed',
            'title' => $title,
            'message' => "The schedule for {$target} {$this->courseName} ({$this->groupName}) was {$this->action}: {$timeLabel}{$roomSuffix}.",
            'url' => $this->url,
            'action' => $this->action,
            'actor_id' => $this->actorId,
            'actor_name' => $this->actorName,
            'actor_role' => $this->actorRole,
            'related_model' => Schedule::class,
            'related_model_id' => $this->scheduleId,
            'created_at' => now()->toIso8601String(),
            'schedule_id' => $this->scheduleId,
            'group_id' => $this->groupId,
            'group_name' => $this->groupName,
            'course_name' => $this->courseName,
            'day_of_week' => $this->dayOfWeek,
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'room_name' => $this->roomName,
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
