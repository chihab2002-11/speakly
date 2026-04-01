<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MessageController extends Controller
{
    public function inbox(Request $request)
    {
        $messages = Message::with('sender')
            ->where('receiver_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('messages.inbox', compact('messages'));
    }

    public function sent(Request $request)
    {
        $messages = Message::with('receiver')
            ->where('sender_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('messages.sent', compact('messages'));
    }

    public function create()
    {
        $users = User::query()->select('id', 'name', 'email')->orderBy('name')->get();

        return view('messages.create', compact('users'));
    }

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

        Message::create([
            'sender_id' => $request->user()->id,
            'receiver_id' => $data['receiver_id'],
            'subject' => $data['subject'] ?? null,
            'body' => $data['body'],
        ]);

        return redirect()->route('messages.sent')->with('success', 'Message sent successfully.');
    }

    public function show(Request $request, Message $message)
    {
        if ($request->user()->id !== $message->sender_id && $request->user()->id !== $message->receiver_id) {
            abort(403);
        }

        if ($request->user()->id === $message->receiver_id && is_null($message->read_at)) {
            $message->update(['read_at' => now()]);
        }

        $message->load(['sender', 'receiver']);

        return view('messages.show', compact('message'));
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
}
