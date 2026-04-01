<x-layouts::app :title="'New Message'">
    <div class="max-w-3xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-4">New Message</h1>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
                <ul class="list-disc ml-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('messages.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">To</label>
                <select name="receiver_id" class="w-full border rounded p-2" required>
                    <option value="">Select user</option>
                    @foreach ($users as $user)
                        @if ($user->id !== auth()->id())
                            <option value="{{ $user->id }}" @selected(old('receiver_id') == $user->id)>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Subject (optional)</label>
                <input type="text" name="subject" value="{{ old('subject') }}" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Message</label>
                <textarea name="body" rows="6" class="w-full border rounded p-2" required>{{ old('body') }}</textarea>
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Send</button>
                <a href="{{ route('messages.inbox') }}" class="px-4 py-2 bg-gray-200 rounded">Cancel</a>
            </div>
        </form>
    </div>
</x-layouts::app>
