<?php

namespace App\Http\Controllers;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Message;
use App\Models\TeacherResource;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ParentChildPortalController extends Controller
{
    use ProfileValidationRules;
    use PasswordValidationRules;

    public function dashboard(Request $request, User $child): View
    {
        [$parent, $child] = $this->resolveParentAndChild($request, $child);

        $studentRequest = $this->buildChildRequest($request, $child);
        $studentView = app(StudentDashboardController::class)->index($studentRequest);

        $data = $studentView->getData();
        $data['mentors'] = collect($data['mentors'] ?? [])
            ->map(function (array $mentor) use ($child): array {
                $mentorId = (int) ($mentor['id'] ?? 0);

                if ($mentorId > 0) {
                    $mentor['messageUrl'] = route('parent.child.messages.conversation', [
                        'child' => $child->id,
                        'conversation' => $mentorId,
                    ]);
                }

                return $mentor;
            })
            ->values()
            ->all();

        return view('student.dashboard', $this->portalViewData($parent, $child, 'dashboard', array_merge(
            $data,
            [
                'dashboardMessageCenterRouteName' => 'parent.child.messages',
                'dashboardMessageCenterRouteParams' => ['child' => $child->id],
            ]
        )));
    }

    public function academic(Request $request, User $child): View
    {
        [$parent, $child] = $this->resolveParentAndChild($request, $child);

        $studentRequest = $this->buildChildRequest($request, $child);
        $studentView = app(StudentAcademicController::class)->index($studentRequest);

        return view('student.academic', $this->portalViewData($parent, $child, 'academic', $studentView->getData()));
    }

    public function materials(Request $request, User $child): View
    {
        [$parent, $child] = $this->resolveParentAndChild($request, $child);

        $studentRequest = $this->buildChildRequest($request, $child);
        $studentView = app(StudentMaterialsController::class)->index($studentRequest);

        $data = $studentView->getData();
        $materials = collect($data['materials'] ?? [])
            ->map(function (array $material) use ($child): array {
                $resourceId = (int) ($material['id'] ?? 0);

                if ($resourceId > 0) {
                    $material['downloadUrl'] = route('parent.child.materials.download', [
                        'child' => $child->id,
                        'resource' => $resourceId,
                    ]);

                    $material['printUrl'] = route('parent.child.materials.print', [
                        'child' => $child->id,
                        'resource' => $resourceId,
                    ]);
                }

                return $material;
            })
            ->values()
            ->all();

        $data['materials'] = $materials;

        return view('student.materials', $this->portalViewData($parent, $child, 'materials', $data));
    }

    public function downloadMaterial(Request $request, User $child, TeacherResource $resource): StreamedResponse
    {
        [, $child] = $this->resolveParentAndChild($request, $child);

        $studentRequest = $this->buildChildRequest($request, $child);

        return app(StudentMaterialsController::class)->download($studentRequest, $resource);
    }

    public function printMaterial(Request $request, User $child, TeacherResource $resource): StreamedResponse
    {
        [, $child] = $this->resolveParentAndChild($request, $child);

        $studentRequest = $this->buildChildRequest($request, $child);

        return app(StudentMaterialsController::class)->print($studentRequest, $resource);
    }

    public function messages(Request $request, User $child, ?int $conversation = null): View
    {
        [$parent, $child] = $this->resolveParentAndChild($request, $child);

        $studentRequest = $this->buildChildRequest($request, $child);
        if ($conversation !== null) {
            $studentRequest->query->set('user_id', (string) $conversation);
        }

        $studentView = app(MessageController::class)->index($studentRequest);

        return view('student.messages', $this->portalViewData($parent, $child, 'messages', array_merge(
            $studentView->getData(),
            [
                'messageActor' => $child,
                'messageIndexRouteName' => 'parent.child.messages',
                'messageIndexRouteParams' => ['child' => $child->id],
                'messageConversationRouteName' => 'parent.child.messages.conversation',
                'messageConversationRouteParams' => ['child' => $child->id],
                'messageStoreRouteName' => 'parent.child.messages.store',
                'messageStoreRouteParams' => ['child' => $child->id],
            ]
        )));
    }

    public function storeMessage(Request $request, User $child): RedirectResponse
    {
        [, $child] = $this->resolveParentAndChild($request, $child);

        $validated = $request->validate([
            'receiver_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->whereNotNull('approved_at')),
                Rule::notIn([$child->id]),
            ],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = Message::create([
            'sender_id' => $child->id,
            'receiver_id' => (int) $validated['receiver_id'],
            'subject' => $validated['subject'] ?? null,
            'body' => $validated['body'],
        ]);

        $message->load(['sender', 'receiver']);
        $message->receiver->notify(new NewMessageNotification($message));

        return redirect()
            ->route('parent.child.messages.conversation', [
                'child' => $child->id,
                'conversation' => (int) $validated['receiver_id'],
            ])
            ->with('success', 'Message sent successfully.');
    }

    public function settings(Request $request, User $child): View
    {
        [$parent, $child] = $this->resolveParentAndChild($request, $child);

        $studentRequest = $this->buildChildRequest($request, $child);
        $studentView = app(StudentSettingsController::class)->edit($studentRequest);

        return view('student.settings', $this->portalViewData($parent, $child, 'settings', array_merge(
            $studentView->getData(),
            [
                'settingsUpdateRouteName' => 'parent.child.settings.update',
                'settingsUpdateRouteParams' => ['child' => $child->id],
                'passwordRouteName' => 'parent.child.password',
                'passwordRouteParams' => ['child' => $child->id],
            ]
        )));
    }

    public function updateSettings(Request $request, User $child): RedirectResponse
    {
        [, $child] = $this->resolveParentAndChild($request, $child);

        $validated = $request->validate([
            ...$this->profileRules($child->id),
            'phone' => ['nullable', 'string', 'max:50'],
            'preferred_language' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        $emailChanged = $validated['email'] !== $child->email;

        $child->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
            'preferred_language' => $validated['preferred_language'] ?: null,
            'date_of_birth' => $validated['date_of_birth'] ?: null,
            'bio' => $validated['bio'] ?: null,
        ]);

        if ($emailChanged) {
            $child->email_verified_at = null;
        }

        $child->save();

        return redirect()
            ->route('parent.child.settings', ['child' => $child->id])
            ->with('success', 'Personal details updated successfully.');
    }

    public function password(Request $request, User $child): View
    {
        [$parent, $child] = $this->resolveParentAndChild($request, $child);

        return view('student.password', $this->portalViewData($parent, $child, 'password', [
            'user' => $child,
            'passwordUpdateRouteName' => 'parent.child.password.update',
            'passwordUpdateRouteParams' => ['child' => $child->id],
            'settingsRouteName' => 'parent.child.settings',
            'settingsRouteParams' => ['child' => $child->id],
        ]));
    }

    public function updatePassword(Request $request, User $child): RedirectResponse
    {
        [, $child] = $this->resolveParentAndChild($request, $child);

        $validated = $request->validate([
            'current_password' => [
                ...$this->currentPasswordRules(),
                function (string $attribute, mixed $value, \Closure $fail) use ($child): void {
                    if (! Hash::check((string) $value, (string) $child->password)) {
                        $fail('The current password is incorrect.');
                    }
                },
            ],
            'password' => [...$this->passwordRules(), 'different:current_password'],
        ]);

        $child->forceFill([
            'password' => Hash::make((string) $validated['password']),
            'password_changed_at' => now(),
        ])->save();

        return redirect()
            ->route('parent.child.settings', ['child' => $child->id])
            ->with('success', 'Password updated successfully.');
    }

    public function notifications(Request $request, User $child): View
    {
        [$parent, $child] = $this->resolveParentAndChild($request, $child);

        $notifications = $child->notifications()->latest()->get();

        $childPrefix = '/parent/children/'.$child->id;

        $notifications->each(function ($notification) use ($childPrefix, $child): void {
            $data = (array) ($notification->data ?? []);
            $url = (string) ($data['url'] ?? '');

            if ($url !== '') {
                $url = str_replace('/student/messages', $childPrefix.'/messages', $url);
                $url = str_replace('/student/academic', $childPrefix.'/academic', $url);
                $url = str_replace('/student/materials', $childPrefix.'/materials', $url);
                $url = str_replace('/student/notifications', $childPrefix.'/notifications', $url);
                $url = str_replace('/student/settings', $childPrefix.'/settings', $url);
                $data['url'] = $url;
            }

            $notification->setAttribute('data', $data);
        });

        return view('student.notifications', $this->portalViewData($parent, $child, 'notifications', [
            'user' => $child,
            'notifications' => $notifications,
            'notificationsReadAllRouteName' => 'parent.child.notifications.read-all',
            'notificationsReadAllRouteParams' => ['child' => $child->id],
            'notificationsReadRouteName' => 'parent.child.notifications.read',
            'notificationsReadRouteParams' => ['child' => $child->id],
        ]));
    }

    public function markNotificationAsRead(Request $request, User $child, string $id): RedirectResponse
    {
        [, $child] = $this->resolveParentAndChild($request, $child);

        $notification = $child->notifications()->where('id', $id)->firstOrFail();
        $notification->markAsRead();

        return back()->with('success', 'Notification marked as read');
    }

    public function markAllNotificationsAsRead(Request $request, User $child): RedirectResponse
    {
        [, $child] = $this->resolveParentAndChild($request, $child);
        $child->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read');
    }

    /**
     * @return array{0:User,1:User}
     */
    private function resolveParentAndChild(Request $request, User $child): array
    {
        $parent = $request->user();

        abort_unless((int) $child->parent_id === (int) $parent->id, 403);
        abort_unless($child->hasRole('student'), 403);

        return [$parent, $child];
    }

    private function buildChildRequest(Request $request, User $child): Request
    {
        $studentRequest = Request::createFrom($request);
        $studentRequest->setUserResolver(fn () => $child);

        return $studentRequest;
    }

    /**
     * @param  array<string, mixed>  $studentViewData
     * @return array<string, mixed>
     */
    private function portalViewData(User $parent, User $child, string $currentRoute, array $studentViewData): array
    {
        $children = User::query()
            ->where('parent_id', $parent->id)
            ->whereNotNull('approved_at')
            ->whereHas('roles', fn ($query) => $query->where('name', 'student'))
            ->orderBy('name')
            ->get(['id', 'name']);

        $childCards = $children
            ->values()
            ->map(function (User $student, int $index): array {
                $theme = $index % 2 === 0
                    ? ['color' => 'var(--lumina-child-1)', 'textColor' => 'var(--lumina-child-1-text)']
                    : ['color' => 'var(--lumina-child-2)', 'textColor' => 'var(--lumina-child-2-text)'];

                return [
                    'id' => (int) $student->id,
                    'name' => (string) $student->name,
                    'initials' => $student->initials(),
                    'grade' => 'Student',
                    'color' => $theme['color'],
                    'textColor' => $theme['textColor'],
                ];
            })
            ->all();

        return array_merge($studentViewData, [
            'layoutComponent' => 'layouts.parent-child',
            'currentRoute' => $currentRoute,
            'portalParent' => $parent,
            'portalChildren' => $childCards,
            'portalSelectedChild' => [
                'id' => (int) $child->id,
                'name' => (string) $child->name,
                'initials' => $child->initials(),
                'grade' => 'Student',
            ],
        ]);
    }
}
