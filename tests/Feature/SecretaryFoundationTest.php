<?php

use App\Models\Message;
use App\Models\User;
use App\Notifications\TeacherResourceActionNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['secretary', 'student', 'parent', 'teacher', 'admin'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

function createApprovedSecretaryUserWithRole(string $role): User
{
    $user = User::factory()->create([
        'approved_at' => now(),
    ]);

    $user->assignRole($role);

    return $user;
}

it('renders secretary role messages page with secretary-specific view', function () {
    $secretary = createApprovedSecretaryUserWithRole('secretary');
    $student = createApprovedSecretaryUserWithRole('student');

    Message::query()->create([
        'sender_id' => $student->id,
        'receiver_id' => $secretary->id,
        'subject' => 'Student request',
        'body' => 'Can you help me with registration?',
    ]);

    $this->actingAs($secretary)
        ->get(route('role.messages.index', ['role' => 'secretary']))
        ->assertOk()
        ->assertViewIs('secretary.messages')
        ->assertSee('Conversations');

    $this->actingAs($secretary)
        ->get(route('role.messages.conversation', ['role' => 'secretary', 'conversation' => $student->id]))
        ->assertOk()
        ->assertViewIs('secretary.messages')
        ->assertViewHas('selectedUser', fn ($selectedUser) => $selectedUser && $selectedUser->id === $student->id);
});

it('returns secretary recipients excluding self and unapproved users', function () {
    $secretary = createApprovedSecretaryUserWithRole('secretary');
    $student = createApprovedSecretaryUserWithRole('student');
    $teacher = createApprovedSecretaryUserWithRole('teacher');

    $unapprovedParent = User::factory()->create([
        'approved_at' => null,
    ]);
    $unapprovedParent->assignRole('parent');

    $response = $this->actingAs($secretary)->get(route('secretary.messages.recipients'));

    $response->assertOk();

    $ids = collect($response->json('users'))->pluck('id')->all();

    expect($ids)->toContain($student->id);
    expect($ids)->toContain($teacher->id);
    expect($ids)->not->toContain($secretary->id);
    expect($ids)->not->toContain($unapprovedParent->id);
});

it('keeps secretary notification read endpoints working', function () {
    $secretary = createApprovedSecretaryUserWithRole('secretary');

    $secretary->notify(new TeacherResourceActionNotification(
        action: 'uploaded',
        resourceName: 'Monthly Report'
    ));

    $secretary->notify(new TeacherResourceActionNotification(
        action: 'uploaded',
        resourceName: 'Attendance Summary'
    ));

    $this->actingAs($secretary)
        ->get(route('secretary.notifications'))
        ->assertOk()
        ->assertViewIs('secretary.notifications')
        ->assertSee('Notifications');

    $notificationId = $secretary->fresh()->unreadNotifications()->latest()->firstOrFail()->id;

    $this->actingAs($secretary)
        ->post(route('secretary.notifications.read', $notificationId))
        ->assertRedirect();

    expect($secretary->fresh()->unreadNotifications()->count())->toBe(1);

    $this->actingAs($secretary)
        ->post(route('secretary.notifications.read-all'))
        ->assertRedirect();

    expect($secretary->fresh()->unreadNotifications()->count())->toBe(0);
});
