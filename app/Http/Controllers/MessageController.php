<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use App\Support\DashboardRedirector;
use Closure;
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
        $viewName = in_array($role, ['student', 'parent', 'admin', 'teacher']) ? "{$role}.messages" : 'messages.index';

        return view($viewName, [
            'user' => $request->user(),
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
