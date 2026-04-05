<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;
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
        $selectedUserId = $request->query('user_id', $request->route('user'));

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

        return view('messages.index', [
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
        $data = $request->validate([
            'receiver_id' => [
                'required',
                'exists:users,id',
                Rule::notIn([$request->user()->id]),
            ],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $data['receiver_id'],
            'subject' => $data['subject'] ?? null,
            'body' => $data['body'],
        ]);

        $message->load(['sender', 'receiver']);
        $message->receiver->notify(new NewMessageNotification($message));

        return redirect()->route('messages.conversation', ['user' => $data['receiver_id']])
            ->with('success', 'Message sent successfully.');
    }

    // Keep legacy methods for backward compatibility (deprecated but functional)
    public function inbox(Request $request)
    {
        return redirect()->route('messages.index');
    }

    public function sent(Request $request)
    {
        return redirect()->route('messages.index');
    }

    public function create(Request $request)
    {
        return redirect()->route('messages.index');
    }

    public function show(Request $request, Message $message)
    {
        if (! in_array($request->user()->id, [$message->sender_id, $message->receiver_id], true)) {
            abort(403);
        }

        return redirect()->route('messages.conversation', ['user' => $message->sender_id === $request->user()->id ? $message->receiver_id : $message->sender_id]);
    }

    public function markAsRead(Request $request, Message $message)
    {
        if ($request->user()->id !== $message->receiver_id) {
            abort(403);
        }

        if (is_null($message->read_at)) {
            $message->update(['read_at' => now()]);
        }

        return back()->with('success', 'Message marked as read.');
    }

    public function conversation(Request $request, User $otherUser)
    {
        if ((int) $request->route('user') === (int) $request->user()->id) {
            return redirect()->route('messages.index');
        }

        return $this->index($request);
    }
}
