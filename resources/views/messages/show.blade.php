<x-layouts::app :title="'Message'">
    <div class="max-w-3xl mx-auto p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold">Message</h1>
            <div class="flex gap-2">
                <a href="{{ route('messages.inbox') }}" class="px-3 py-2 bg-gray-200 rounded">Inbox</a>
                <a href="{{ route('messages.sent') }}" class="px-3 py-2 bg-gray-200 rounded">Sent</a>
            </div>
        </div>

        <div class="bg-white shadow rounded p-5 space-y-3">
            <p><span class="font-semibold">From:</span> {{ $message->sender->name }} ({{ $message->sender->email }})</p>
            <p><span class="font-semibold">To:</span> {{ $message->receiver->name }} ({{ $message->receiver->email }})</p>
            <p><span class="font-semibold">Date:</span> {{ $message->created_at->format('Y-m-d H:i') }}</p>
            <p><span class="font-semibold">Subject:</span> {{ $message->subject ?: '(No subject)' }}</p>
            <hr>
            <p class="whitespace-pre-line">{{ $message->body }}</p>
        </div>

        @if (auth()->id() === $message->receiver_id && is_null($message->read_at))
            <form action="{{ route('messages.read', $message) }}" method="POST" class="mt-4">
                @csrf
                @method('PATCH')
                <button class="px-4 py-2 bg-green-600 text-white rounded">Mark as read</button>
            </form>
        @endif
    </div>
</x-layouts::app>
