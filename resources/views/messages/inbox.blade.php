<x-layouts::app :title="'Inbox'">
    <div class="max-w-5xl mx-auto p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold">Inbox</h1>
            <a href="{{ route('messages.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">New Message</a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow rounded overflow-hidden">
            @forelse ($messages as $message)
                <a href="{{ route('messages.show', $message) }}" class="block border-b p-4 hover:bg-gray-50">
                    <div class="flex justify-between">
                        <p class="font-semibold">
                            From: {{ $message->sender->name ?? 'Unknown' }}
                        </p>
                        <p class="text-sm text-gray-500">{{ $message->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                    <p class="text-sm mt-1 {{ is_null($message->read_at) ? 'font-bold' : 'text-gray-600' }}">
                        {{ $message->subject ?: '(No subject)' }}
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ \Illuminate\Support\Str::limit($message->body, 80) }}
                    </p>
                </a>
            @empty
                <p class="p-4 text-gray-600">No messages in inbox.</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $messages->links() }}
        </div>
    </div>
</x-layouts::app>
