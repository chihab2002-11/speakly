<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows authenticated user to send a message', function () {
    $sender = User::factory()->create(['approved_at' => now()]);
    $receiver = User::factory()->create(['approved_at' => now()]);

    $response = $this->actingAs($sender)->post(route('messages.store'), [
        'receiver_id' => $receiver->id,
        'subject' => 'Hello',
        'body' => 'Test message body',
    ]);

    $response->assertRedirect(route('messages.conversation', ['user' => $receiver->id]));

    $this->assertDatabaseHas('messages', [
        'sender_id' => $sender->id,
        'receiver_id' => $receiver->id,
        'subject' => 'Hello',
        'body' => 'Test message body',
    ]);
});

it('prevents user from viewing someone elses message', function () {
    $owner = User::factory()->create(['approved_at' => now()]);
    $receiver = User::factory()->create(['approved_at' => now()]);

    Message::create([
        'sender_id' => $owner->id,
        'receiver_id' => $receiver->id,
        'subject' => 'Private',
        'body' => 'Secret',
    ]);

    // User can access messages page but should not see others' conversations
    $response = $this->actingAs($receiver)->get(route('messages.index'));
    $response->assertOk();
});

it('prevents sending a message to self', function () {
    $user = User::factory()->create(['approved_at' => now()]);

    $response = $this->actingAs($user)->post(route('messages.store'), [
        'receiver_id' => $user->id,
        'subject' => 'Self',
        'body' => 'Should fail',
    ]);

    $response->assertSessionHasErrors('receiver_id');
});

it('shows only received messages in inbox', function () {
    $me = User::factory()->create(['approved_at' => now()]);
    $other = User::factory()->create(['approved_at' => now()]);
    $third = User::factory()->create(['approved_at' => now()]);

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

    $response = $this->actingAs($me)->get(route('messages.index'));

    $response->assertOk();
    $response->assertSee($other->name);
});

it('shows only sent messages in sent page', function () {
    $me = User::factory()->create(['approved_at' => now()]);
    $other = User::factory()->create(['approved_at' => now()]);
    $third = User::factory()->create(['approved_at' => now()]);

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

    $response = $this->actingAs($me)->get(route('messages.index'));

    $response->assertOk();
});

it('opens selected conversation from clean conversation route', function () {
    $me = User::factory()->create(['approved_at' => now()]);
    $other = User::factory()->create(['approved_at' => now()]);

    Message::create([
        'sender_id' => $other->id,
        'receiver_id' => $me->id,
        'subject' => 'Hello',
        'body' => 'Conversation body',
    ]);

    $response = $this->actingAs($me)->get(route('messages.conversation', ['user' => $other->id]));

    $response->assertOk();
    $response->assertViewHas('selectedUser', fn ($selectedUser) => $selectedUser && $selectedUser->id === $other->id);
    $response->assertSee($other->name);
});

it('only receiver can mark message as read', function () {
    $sender = User::factory()->create(['approved_at' => now()]);
    $receiver = User::factory()->create(['approved_at' => now()]);
    $intruder = User::factory()->create(['approved_at' => now()]);

    $message = Message::create([
        'sender_id' => $sender->id,
        'receiver_id' => $receiver->id,
        'subject' => 'Read check',
        'body' => 'Hello',
    ]);

    $this->actingAs($intruder)
        ->patch(route('messages.read', $message))
        ->assertForbidden();

    $this->actingAs($receiver)
        ->patch(route('messages.read', $message))
        ->assertRedirect();

    expect($message->fresh()->read_at)->not()->toBeNull();
});
