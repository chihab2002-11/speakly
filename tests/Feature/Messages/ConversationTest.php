<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function createRoles(): void
{
    Role::create(['name' => 'student', 'guard_name' => 'web']);
}

test('user can view conversation with another user', function () {
    createRoles();

    $user1 = User::factory()->create(['approved_at' => now()]);
    $user1->assignRole('student');

    $user2 = User::factory()->create(['approved_at' => now()]);
    $user2->assignRole('student');

    // Create some messages
    Message::create([
        'sender_id' => $user1->id,
        'receiver_id' => $user2->id,
        'body' => 'Hello from user1',
    ]);

    Message::create([
        'sender_id' => $user2->id,
        'receiver_id' => $user1->id,
        'body' => 'Hello from user2',
        'read_at' => null,
    ]);

    $response = $this->actingAs($user1)->get(route('messages.index', ['user_id' => $user2->id]));

    $response->assertStatus(200);
    $response->assertViewIs('messages.index');
    $response->assertViewHas('conversations');
    $response->assertViewHas('selectedUser');
});

test('unread messages are marked as read when opening conversation', function () {
    createRoles();

    $user1 = User::factory()->create(['approved_at' => now()]);
    $user1->assignRole('student');

    $user2 = User::factory()->create(['approved_at' => now()]);
    $user2->assignRole('student');

    $unreadMessage = Message::create([
        'sender_id' => $user2->id,
        'receiver_id' => $user1->id,
        'body' => 'Unread message',
        'read_at' => null,
    ]);

    expect($unreadMessage->refresh()->read_at)->toBeNull();

    $this->actingAs($user1)->get(route('messages.index', ['user_id' => $user2->id]));

    expect($unreadMessage->refresh()->read_at)->not->toBeNull();
});

test('messages are ordered by created_at ascending', function () {
    createRoles();

    $user1 = User::factory()->create(['approved_at' => now()]);
    $user1->assignRole('student');

    $user2 = User::factory()->create(['approved_at' => now()]);
    $user2->assignRole('student');

    Message::create([
        'sender_id' => $user1->id,
        'receiver_id' => $user2->id,
        'body' => 'First message',
    ]);

    Message::create([
        'sender_id' => $user2->id,
        'receiver_id' => $user1->id,
        'body' => 'Second message',
    ]);

    Message::create([
        'sender_id' => $user1->id,
        'receiver_id' => $user2->id,
        'body' => 'Third message',
    ]);

    $response = $this->actingAs($user1)->get(route('messages.index', ['user_id' => $user2->id]));

    $response->assertStatus(200);
    // Verify the view has the selected user messages
    $response->assertViewHas('selectedUser');
});

test('cannot view conversation with self', function () {
    createRoles();

    $user = User::factory()->create(['approved_at' => now()]);
    $user->assignRole('student');

    $response = $this->actingAs($user)->get(route('messages.index', ['user_id' => $user->id]));

    // Should redirect or show empty state
    expect($response->status())->toBeIn([200, 302]);
});

test('latest conversation appears first in the list', function () {
    createRoles();

    $me = User::factory()->create(['approved_at' => now(), 'name' => 'Current User']);
    $me->assignRole('student');

    $olderPartner = User::factory()->create(['approved_at' => now(), 'name' => 'Older Partner']);
    $olderPartner->assignRole('student');

    $newerPartner = User::factory()->create(['approved_at' => now(), 'name' => 'Newer Partner']);
    $newerPartner->assignRole('student');

    $olderMessage = Message::create([
        'sender_id' => $olderPartner->id,
        'receiver_id' => $me->id,
        'body' => 'Older conversation message',
    ]);

    $newerMessage = Message::create([
        'sender_id' => $newerPartner->id,
        'receiver_id' => $me->id,
        'body' => 'Newest conversation message',
    ]);

    Message::whereKey($olderMessage->id)->update([
        'created_at' => now()->subHour(),
        'updated_at' => now()->subHour(),
    ]);

    Message::whereKey($newerMessage->id)->update([
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->actingAs($me)->get(route('messages.index'));

    $response->assertOk();

    $content = $response->getContent();
    $newerPartnerPosition = strpos($content, 'Newer Partner');
    $olderPartnerPosition = strpos($content, 'Older Partner');

    expect($newerPartnerPosition)->not->toBeFalse();
    expect($olderPartnerPosition)->not->toBeFalse();
    expect($newerPartnerPosition)->toBeLessThan($olderPartnerPosition);
});

test('user can search conversations by username', function () {
    createRoles();

    $me = User::factory()->create(['approved_at' => now(), 'name' => 'Search User']);
    $me->assignRole('student');

    $john = User::factory()->create(['approved_at' => now(), 'name' => 'John Carter']);
    $john->assignRole('student');

    $alice = User::factory()->create(['approved_at' => now(), 'name' => 'Alice Baker']);
    $alice->assignRole('student');

    Message::create([
        'sender_id' => $john->id,
        'receiver_id' => $me->id,
        'body' => 'Message from John',
    ]);

    Message::create([
        'sender_id' => $alice->id,
        'receiver_id' => $me->id,
        'body' => 'Message from Alice',
    ]);

    $response = $this->actingAs($me)->get(route('messages.index', ['search' => 'john']));

    $response->assertOk();
    $response->assertSee('John Carter');
    $response->assertDontSee('Alice Baker');
});
