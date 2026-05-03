<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use App\Support\DashboardRedirector;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MessageController extends Controller
{
    /**
     * Display unified messages interface with conversations list and thread.
     * Optionally show a specific conversation via ?user_id=X query parameter.
     */
    public function index(Request $request)
    {
        $currentUserId = $request->user()->id;
        $search = $request->query('search', '');
        $selectedUserId = $request->query('user_id', $request->route('conversation'));

        // Get all unique users the current user has messaged with (sorted by most recent message)
        $conversationPartners = User::whereIn('id', function ($query) use ($currentUserId) {
            $query->selectRaw('DISTINCT CASE
                    WHEN sender_id = ? THEN receiver_id
                    ELSE sender_id
                END as user_id', [$currentUserId])
                ->from('messages')
                ->where(function ($q) use ($currentUserId) {
                    $q->where('sender_id', $currentUserId)
                        ->orWhere('receiver_id', $currentUserId);
                });
        })
            ->when($search, function ($query) use ($search) {
                return $query->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($search).'%']);
            })
            ->orderBy('name')
            ->get();

        // Get conversation threads with last message for each partner
        $conversations = [];
        foreach ($conversationPartners as $partner) {
            $lastMessage = Message::whereRaw(
                '(sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)',
                [$currentUserId, $partner->id, $partner->id, $currentUserId]
            )
                ->orderBy('created_at', 'desc')
                ->first();

            $unreadCount = Message::where('sender_id', $partner->id)
                ->where('receiver_id', $currentUserId)
                ->whereNull('read_at')
                ->count();

            $conversations[] = [
                'user' => $partner,
                'lastMessage' => $lastMessage,
                'unreadCount' => $unreadCount,
            ];
        }

        // Sort conversations by most recent message
        usort($conversations, function ($a, $b) {
            $timeA = $a['lastMessage']?->created_at ?? now()->subYears(1);
            $timeB = $b['lastMessage']?->created_at ?? now()->subYears(1);

            return $timeB <=> $timeA;
        });

        // If a user is selected, get the full conversation and mark messages as read
        $selectedUser = null;
        $selectedConversation = [];

        if ($selectedUserId) {
            $selectedUser = User::find($selectedUserId);

            if ($selectedUser && $selectedUser->id !== $currentUserId) {
                // Get all messages in the conversation
                $selectedConversation = Message::whereRaw(
                    '(sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)',
                    [$currentUserId, $selectedUser->id, $selectedUser->id, $currentUserId]
                )
                    ->with(['sender', 'receiver'])
                    ->orderBy('created_at', 'asc')
                    ->get();

                // Mark unread messages from selectedUser as read
                Message::where('sender_id', $selectedUser->id)
                    ->where('receiver_id', $currentUserId)
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
            }
        }

        // Determine view based on user role
        $role = DashboardRedirector::roleFor($request->user());
        $viewName = in_array($role, ['student', 'parent', 'admin', 'teacher', 'secretary']) ? "{$role}.messages" : 'messages.index';

        $children = [];
        if ($role === 'parent') {
            $children = User::query()
                ->where('parent_id', $currentUserId)
                ->whereNotNull('approved_at')
                ->whereHas('roles', fn ($query) => $query->where('name', 'student'))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->values()
                ->map(function (User $child, int $index): array {
                    $theme = $index % 2 === 0
                        ? ['color' => 'var(--lumina-child-1)', 'textColor' => 'var(--lumina-child-1-text)']
                        : ['color' => 'var(--lumina-child-2)', 'textColor' => 'var(--lumina-child-2-text)'];

                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'initials' => $child->initials(),
                        'grade' => 'Student',
                        'color' => $theme['color'],
                        'textColor' => $theme['textColor'],
                    ];
                })
                ->all();
        }

        return view($viewName, [
            'user' => $request->user(),
            'children' => $children,
            'conversations' => $conversations,
            'selectedUser' => $selectedUser,
            'selectedConversation' => $selectedConversation,
            'search' => $search,
        ]);
    }

    /**
     * Store a new message and redirect to messages page with selected user.
     */
    public function store(Request $request)
    {
        $sender = $request->user();
        $currentRole = (string) ($request->route('role') ?: DashboardRedirector::roleFor($sender));

        $data = $request->validate([
            'receiver_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->whereNotNull('approved_at')),
                Rule::notIn([$sender->id]),
                function (string $attribute, mixed $value, Closure $fail) use ($currentRole): void {
                    if ($currentRole !== 'teacher') {
                        return;
                    }

                    $recipient = User::query()->find($value);

                    if (! $recipient) {
                        return;
                    }

                    if (! $recipient->hasAnyRole(['student', 'parent', 'teacher', 'admin'])) {
                        $fail('Teachers can only message approved students, parents, teachers, or admins.');
                    }
                },
            ],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = Message::create([
            'sender_id' => $sender->id,
            'receiver_id' => $data['receiver_id'],
            'subject' => $data['subject'] ?? null,
            'body' => $data['body'],
        ]);

        $message->load(['sender', 'receiver']);
        $message->receiver->notify(new NewMessageNotification($message));

        return redirect()->route('role.messages.conversation', $this->routeParameters($request, [
            'conversation' => $data['receiver_id'],
        ]))
            ->with('success', 'Message sent successfully.');
    }

    // Keep legacy methods for backward compatibility (deprecated but functional)
    public function inbox(Request $request)
    {
        return redirect()->route('role.messages.index', $this->routeParameters($request));
    }

    public function sent(Request $request)
    {
        return redirect()->route('role.messages.index', $this->routeParameters($request));
    }

    public function create(Request $request)
    {
        return redirect()->route('role.messages.index', $this->routeParameters($request));
    }

    public function show(Request $request, string $role, string $message)
    {
        $message = Message::query()->findOrFail($this->extractId($message));

        if (! in_array($request->user()->id, [$message->sender_id, $message->receiver_id], true)) {
            abort(403);
        }

        return redirect()->route('role.messages.conversation', $this->routeParameters($request, [
            'conversation' => $message->sender_id === $request->user()->id ? $message->receiver_id : $message->sender_id,
        ]));
    }

    public function markAsRead(Request $request, string $role, string $message)
    {
        $message = Message::query()->findOrFail($this->extractId($message));

        if ($request->user()->id !== $message->receiver_id) {
            abort(403);
        }

        if (is_null($message->read_at)) {
            $message->update(['read_at' => now()]);
        }

        return back()->with('success', 'Message marked as read.');
    }

    public function conversation(Request $request, string $role, string $conversation)
    {
        if ((int) $conversation === (int) $request->user()->id) {
            return redirect()->route('role.messages.index', $this->routeParameters($request));
        }

        return $this->index($request);
    }

    public function live(Request $request, string $role): JsonResponse
    {
        return response()->json($this->livePayload($request));
    }

    /**
     * @return array<string, mixed>
     */
    public function livePayload(Request $request): array
    {
        $currentUserId = (int) $request->user()->id;
        $search = (string) $request->query('search', '');
        $selectedUserId = (int) $request->query('user_id', 0);

        $selectedUser = $selectedUserId > 0
            ? User::query()
                ->whereKey($selectedUserId)
                ->where('id', '!=', $currentUserId)
                ->first(['id', 'name', 'email'])
            : null;

        $messages = [];

        if ($selectedUser) {
            Message::where('sender_id', $selectedUser->id)
                ->where('receiver_id', $currentUserId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            $messages = Message::query()
                ->whereRaw(
                    '(sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)',
                    [$currentUserId, $selectedUser->id, $selectedUser->id, $currentUserId]
                )
                ->with(['sender:id,name', 'receiver:id,name'])
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn (Message $message): array => [
                    'id' => (int) $message->id,
                    'sender_id' => (int) $message->sender_id,
                    'subject' => $message->subject,
                    'body' => ltrim((string) $message->body),
                    'is_mine' => (int) $message->sender_id === $currentUserId,
                    'author_name' => (int) $message->sender_id === $currentUserId
                        ? 'You'
                        : (string) ($message->sender?->name ?? 'User'),
                    'author_initials' => (int) $message->sender_id === $currentUserId
                        ? $request->user()->initials()
                        : ($message->sender?->initials() ?: strtoupper(substr((string) ($message->sender?->name ?? '?'), 0, 1))),
                    'created_at' => $message->created_at?->format('M j, H:i'),
                ])
                ->all();
        }

        return [
            'conversations' => $this->liveConversations($currentUserId, $search),
            'selected_user' => $selectedUser ? [
                'id' => (int) $selectedUser->id,
                'name' => $selectedUser->name,
                'email' => $selectedUser->email,
                'initials' => $selectedUser->initials(),
            ] : null,
            'messages' => $messages,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function liveConversations(int $currentUserId, string $search = ''): array
    {
        $conversationPartners = User::whereIn('id', function ($query) use ($currentUserId) {
            $query->selectRaw('DISTINCT CASE
                    WHEN sender_id = ? THEN receiver_id
                    ELSE sender_id
                END as user_id', [$currentUserId])
                ->from('messages')
                ->where(function ($q) use ($currentUserId) {
                    $q->where('sender_id', $currentUserId)
                        ->orWhere('receiver_id', $currentUserId);
                });
        })
            ->when($search, function ($query) use ($search) {
                return $query->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($search).'%']);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $conversations = [];

        foreach ($conversationPartners as $partner) {
            $lastMessage = Message::whereRaw(
                '(sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)',
                [$currentUserId, $partner->id, $partner->id, $currentUserId]
            )
                ->orderBy('created_at', 'desc')
                ->first();

            $unreadCount = Message::where('sender_id', $partner->id)
                ->where('receiver_id', $currentUserId)
                ->whereNull('read_at')
                ->count();

            $conversations[] = [
                'user' => [
                    'id' => (int) $partner->id,
                    'name' => $partner->name,
                    'email' => $partner->email,
                    'initials' => $partner->initials(),
                ],
                'last_message' => $lastMessage ? [
                    'sender_id' => (int) $lastMessage->sender_id,
                    'body' => ltrim((string) $lastMessage->body),
                    'created_at' => $lastMessage->created_at?->format('M j, H:i'),
                    'sort_at' => $lastMessage->created_at?->timestamp ?? 0,
                ] : null,
                'unread_count' => $unreadCount,
            ];
        }

        usort($conversations, fn ($a, $b) => ($b['last_message']['sort_at'] ?? 0) <=> ($a['last_message']['sort_at'] ?? 0));

        return $conversations;
    }

    private function extractId(string $value): int
    {
        if (ctype_digit($value)) {
            return (int) $value;
        }

        if (preg_match('/"id"\s*:\s*(\d+)/', $value, $matches) === 1) {
            return (int) $matches[1];
        }

        if (preg_match('/\d+/', $value, $matches) === 1) {
            return (int) $matches[0];
        }

        abort(404);
    }

    /**
     * @param  array<string, mixed>  $parameters
     * @return array<string, mixed>
     */
    private function routeParameters(Request $request, array $parameters = []): array
    {
        $role = (string) ($request->route('role') ?: DashboardRedirector::roleFor($request->user()));

        return ['role' => $role, ...$parameters];
    }
}
