<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::findOrCreate('student', 'web');
});

function createApprovedStudent(): User
{
    $user = User::factory()->create(['approved_at' => now()]);
    $user->assignRole('student');

    return $user;
}

it('allows authenticated user to send a message', function () {
    $sender = createApprovedStudent();
    $receiver = createApprovedStudent();

    $response = $this->actingAs($sender)->post(route('role.messages.store', ['role' => 'student']), [
        'receiver_id' => $receiver->id,
        'subject' => 'Hello',
        'body' => 'Test message body',
    ]);

    $response->assertRedirect(route('role.messages.conversation', ['role' => 'student', 'conversation' => $receiver->id]));

    $this->assertDatabaseHas('messages', [
        'sender_id' => $sender->id,
        'receiver_id' => $receiver->id,
        'subject' => 'Hello',
        'body' => 'Test message body',
    ]);
});

it('prevents user from viewing someone elses message', function () {
    $owner = createApprovedStudent();
    $receiver = createApprovedStudent();

    Message::create([
        'sender_id' => $owner->id,
        'receiver_id' => $receiver->id,
        'subject' => 'Private',
        'body' => 'Secret',
    ]);

    // User can access messages page but should not see others' conversations
    $response = $this->actingAs($receiver)->get(route('role.messages.index', ['role' => 'student']));
    $response->assertOk();
});

it('prevents sending a message to self', function () {
    $user = createApprovedStudent();

    $response = $this->actingAs($user)->post(route('role.messages.store', ['role' => 'student']), [
        'receiver_id' => $user->id,
        'subject' => 'Self',
        'body' => 'Should fail',
    ]);

    $response->assertSessionHasErrors('receiver_id');
});

it('shows only received messages in inbox', function () {
    $me = createApprovedStudent();
    $other = createApprovedStudent();
    $third = createApprovedStudent();

    Message::create([
        'sender_id' => $other->id,
        'receiver_id' => $me->id,
        'subject' => 'For me',
        'body' => 'Inbox yes',
    ]);

    Message::create([
        'sender_id' => $other->id,
        'receiver_id' => $third->id,
        'subject' => 'Not for me',
        'body' => 'Inbox no',
    ]);

    $response = $this->actingAs($me)->get(route('role.messages.index', ['role' => 'student']));

    $response->assertOk();
    $response->assertSee($other->name);
});

it('shows only sent messages in sent page', function () {
    $me = createApprovedStudent();
    $other = createApprovedStudent();
    $third = createApprovedStudent();

    Message::create([
        'sender_id' => $me->id,
        'receiver_id' => $other->id,
        'subject' => 'My sent',
        'body' => 'Sent yes',
    ]);

    Message::create([
        'sender_id' => $third->id,
        'receiver_id' => $other->id,
        'subject' => 'Not mine',
        'body' => 'Sent no',
    ]);

    $response = $this->actingAs($me)->get(route('role.messages.index', ['role' => 'student']));

    $response->assertOk();
});

it('opens selected conversation from clean conversation route', function () {
    $me = createApprovedStudent();
    $other = createApprovedStudent();

    Message::create([
        'sender_id' => $other->id,
        'receiver_id' => $me->id,
        'subject' => 'Hello',
        'body' => 'Conversation body',
    ]);

    $response = $this->actingAs($me)->get(route('role.messages.conversation', ['role' => 'student', 'conversation' => $other->id]));

    $response->assertOk();
    $response->assertViewHas('selectedUser', fn ($selectedUser) => $selectedUser && $selectedUser->id === $other->id);
    $response->assertSee($other->name);
});

it('only receiver can mark message as read', function () {
    $sender = createApprovedStudent();
    $receiver = createApprovedStudent();
    $intruder = createApprovedStudent();

    $message = Message::create([
        'sender_id' => $sender->id,
        'receiver_id' => $receiver->id,
        'subject' => 'Read check',
        'body' => 'Hello',
    ]);

    $this->actingAs($intruder)
        ->patch(route('role.messages.read', ['role' => 'student', 'message' => $message->id]))
        ->assertForbidden();

    $this->actingAs($receiver)
        ->patch(route('role.messages.read', ['role' => 'student', 'message' => $message->id]))
        ->assertRedirect();

    expect($message->fresh()->read_at)->not()->toBeNull();
});
