<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['teacher', 'student', 'parent', 'admin', 'secretary'] as $role) {
        Role::findOrCreate($role, 'web');
    }
});

it('teacher can send to allowed roles only', function (string $recipientRole) {
    $teacher = createApprovedUserWithRole('teacher');
    $recipient = createApprovedUserWithRole($recipientRole);

    $response = $this->actingAs($teacher)->post(route('role.messages.store', ['role' => 'teacher']), [
        'receiver_id' => $recipient->id,
        'subject' => 'Allowed recipient',
        'body' => 'Teacher to allowed role message',
    ]);

    $response->assertRedirect(route('role.messages.conversation', ['role' => 'teacher', 'conversation' => $recipient->id]));

    $this->assertDatabaseHas('messages', [
        'sender_id' => $teacher->id,
        'receiver_id' => $recipient->id,
        'subject' => 'Allowed recipient',
    ]);
})->with(['student', 'parent', 'teacher', 'admin']);

it('teacher cannot send to forbidden roles', function () {
    $teacher = createApprovedUserWithRole('teacher');
    $secretary = createApprovedUserWithRole('secretary');

    $response = $this->actingAs($teacher)->post(route('role.messages.store', ['role' => 'teacher']), [
        'receiver_id' => $secretary->id,
        'subject' => 'Forbidden recipient',
        'body' => 'This should be blocked',
    ]);

    $response->assertSessionHasErrors('receiver_id');
    expect(Message::query()->count())->toBe(0);
});

it('teacher cannot send to unapproved users', function () {
    $teacher = createApprovedUserWithRole('teacher');

    $unapprovedStudent = User::factory()->create([
        'approved_at' => null,
    ]);
    $unapprovedStudent->assignRole('student');

    $response = $this->actingAs($teacher)->post(route('role.messages.store', ['role' => 'teacher']), [
        'receiver_id' => $unapprovedStudent->id,
        'subject' => 'Unapproved recipient',
        'body' => 'This should be blocked',
    ]);

    $response->assertSessionHasErrors('receiver_id');
    expect(Message::query()->count())->toBe(0);
});

it('invalid recipient requests are rejected properly', function () {
    $teacher = createApprovedUserWithRole('teacher');

    $nonExistentRecipientResponse = $this->actingAs($teacher)->post(route('role.messages.store', ['role' => 'teacher']), [
        'receiver_id' => 999999,
        'subject' => 'Invalid recipient',
        'body' => 'Invalid receiver should fail',
    ]);

    $nonExistentRecipientResponse->assertSessionHasErrors('receiver_id');

    $selfRecipientResponse = $this->actingAs($teacher)->post(route('role.messages.store', ['role' => 'teacher']), [
        'receiver_id' => $teacher->id,
        'subject' => 'Self message',
        'body' => 'Self should fail',
    ]);

    $selfRecipientResponse->assertSessionHasErrors('receiver_id');
    expect(Message::query()->count())->toBe(0);
});
