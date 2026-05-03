<?php

use App\Models\Message;
use App\Models\User;
use App\Notifications\AccountApprovedNotification;
use App\Notifications\AccountRejectedNotification;
use App\Notifications\AccountUnapprovedNotification;
use App\Notifications\ClassResourceUploadedNotification;
use App\Notifications\EmployeePaymentRecordedNotification;
use App\Notifications\NewMessageNotification;
use App\Notifications\ParentChildAttendanceSavedNotification;
use App\Notifications\ScheduleChangedNotification;
use App\Notifications\SecretaryAnnouncementNotification;
use App\Notifications\StudentAttendanceSavedNotification;
use App\Notifications\StudentGroupEnrollmentChangedNotification;
use App\Notifications\TeacherAttendanceSavedNotification;
use App\Notifications\TeacherGroupAssignedNotification;
use App\Notifications\TeacherResourceActionNotification;
use App\Notifications\TuitionPaymentRecordedNotification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Tests\TestCase;

function liveNotificationInstances(User $notifiable): array
{
    $sender = createApprovedUserWithRole('secretary');
    $message = Message::factory()->create([
        'sender_id' => $sender->id,
        'receiver_id' => $notifiable->id,
    ]);

    return [
        AccountApprovedNotification::class => new AccountApprovedNotification,
        AccountRejectedNotification::class => new AccountRejectedNotification('Incomplete registration documents.'),
        AccountUnapprovedNotification::class => new AccountUnapprovedNotification(1, 'Admin Demo', 'admin'),
        ClassResourceUploadedNotification::class => new ClassResourceUploadedNotification(1, 1, 'student', 'English A1', 'Group #1', 'homework', '2026-05-10', null, null, route('student.materials')),
        EmployeePaymentRecordedNotification::class => new EmployeePaymentRecordedNotification(5000, 0, 5000, 'paid', route('teacher.my-payments')),
        NewMessageNotification::class => new NewMessageNotification($message),
        ParentChildAttendanceSavedNotification::class => new ParentChildAttendanceSavedNotification(1, 'Lina Demo', 'English A1', '2026-05-03'),
        ScheduleChangedNotification::class => new ScheduleChangedNotification('updated', 1, 1, 'Group #1', 'English A1', 'monday', '09:00', '10:00', 'Room A', 1, 'Admin Demo', 'admin', 'student', null, null, route('timetable.index')),
        SecretaryAnnouncementNotification::class => new SecretaryAnnouncementNotification('Academy update', 'A new academy announcement is available.', route('notifications.index'), 1, 'Secretary Demo'),
        StudentAttendanceSavedNotification::class => new StudentAttendanceSavedNotification('English A1', '2026-05-03'),
        StudentGroupEnrollmentChangedNotification::class => new StudentGroupEnrollmentChangedNotification('enrolled', 1, 'Group #1', 'English A1', 'English', 1, 'Secretary Demo', 'secretary', 'student', null, null, route('student.academic')),
        TeacherAttendanceSavedNotification::class => new TeacherAttendanceSavedNotification(1, 'English A1', '2026-05-03', 3),
        TeacherGroupAssignedNotification::class => new TeacherGroupAssignedNotification(1, 'Group #1', 'English A1', 'English', 1, 'Admin Demo', route('timetable.teacher'), 'assigned', 'admin'),
        TeacherResourceActionNotification::class => new TeacherResourceActionNotification('uploaded', 'Homework 1', 1, 'English A1'),
        TuitionPaymentRecordedNotification::class => new TuitionPaymentRecordedNotification(1, 1000, '2026-05-03', 'cash', 1, 'Secretary Demo', 'secretary', 'student', null, null, route('student.financial')),
    ];
}

it('broadcasts every database notification class', function () {
    /** @var TestCase $this */
    seedAuthorizationFixtures();

    $notifiable = createApprovedUserWithRole('student');

    foreach (liveNotificationInstances($notifiable) as $class => $notification) {
        expect(in_array('database', $notification->via($notifiable), true))->toBeTrue("Expected {$class} to keep database notifications.");
        expect(in_array('broadcast', $notification->via($notifiable), true))->toBeTrue("Expected {$class} to broadcast live notifications.");
    }
});

it('uses a live-renderable broadcast payload for every notification class', function () {
    /** @var TestCase $this */
    seedAuthorizationFixtures();

    $notifiable = createApprovedUserWithRole('student');

    foreach (liveNotificationInstances($notifiable) as $class => $notification) {
        $message = $notification->toBroadcast($notifiable);

        expect($message)->toBeInstanceOf(BroadcastMessage::class);

        $payload = $message->data;

        foreach (['id', 'title', 'message', 'body', 'text', 'type', 'url', 'action_url', 'created_at', 'read_at', 'data'] as $key) {
            expect(array_key_exists($key, $payload))->toBeTrue("Expected {$class} broadcast payload to contain {$key}.");
        }

        expect($payload['title'])->not->toBe('');
        expect($payload['type'])->not->toBe('');
    }
});

it('authorizes users for their own private notification channel', function () {
    /** @var TestCase $this */
    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.key' => 'test-key',
        'broadcasting.connections.reverb.secret' => 'test-secret',
        'broadcasting.connections.reverb.app_id' => 'test-app',
    ]);
    require base_path('routes/channels.php');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/broadcasting/auth', [
            'channel_name' => "private-App.Models.User.{$user->id}",
            'socket_id' => '123.456',
        ])
        ->assertOk();
});

it('rejects users from another users private notification channel', function () {
    /** @var TestCase $this */
    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.key' => 'test-key',
        'broadcasting.connections.reverb.secret' => 'test-secret',
        'broadcasting.connections.reverb.app_id' => 'test-app',
    ]);
    require base_path('routes/channels.php');

    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/broadcasting/auth', [
            'channel_name' => "private-App.Models.User.{$otherUser->id}",
            'socket_id' => '123.456',
        ])
        ->assertForbidden();
});
