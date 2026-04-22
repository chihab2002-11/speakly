<?php

namespace Tests\Feature\Messages;

use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeacherMessageAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['teacher', 'student', 'parent', 'admin', 'secretary'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    public static function allowedRecipientRolesProvider(): array
    {
        return [
            ['student'],
            ['parent'],
            ['teacher'],
            ['admin'],
        ];
    }

    /**
     * @dataProvider allowedRecipientRolesProvider
     */
    public function test_teacher_can_send_to_allowed_roles_only(string $recipientRole): void
    {
        $teacher = $this->createApprovedMessageUserWithRole('teacher');
        $recipient = $this->createApprovedMessageUserWithRole($recipientRole);

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
    }

    public function test_teacher_cannot_send_to_forbidden_roles(): void
    {
        $teacher = $this->createApprovedMessageUserWithRole('teacher');
        $secretary = $this->createApprovedMessageUserWithRole('secretary');

        $response = $this->actingAs($teacher)->post(route('role.messages.store', ['role' => 'teacher']), [
            'receiver_id' => $secretary->id,
            'subject' => 'Forbidden recipient',
            'body' => 'This should be blocked',
        ]);

        $response->assertSessionHasErrors('receiver_id');
        $this->assertSame(0, Message::query()->count());
    }

    public function test_teacher_cannot_send_to_unapproved_users(): void
    {
        $teacher = $this->createApprovedMessageUserWithRole('teacher');

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
        $this->assertSame(0, Message::query()->count());
    }

    public function test_invalid_recipient_requests_are_rejected_properly(): void
    {
        $teacher = $this->createApprovedMessageUserWithRole('teacher');

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
        $this->assertSame(0, Message::query()->count());
    }

    private function createApprovedMessageUserWithRole(string $role): User
    {
        $user = User::factory()->create([
            'approved_at' => now(),
        ]);

        $user->assignRole($role);

        return $user;
    }
}
