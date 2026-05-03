<h1>My Notifications</h1>

<div data-live-notification-list data-live-notification-read-route-template="{{ route('notifications.read', ['id' => '__ID__']) }}">
@forelse($notifications as $n)
    @php
        $data = (array) $n->data;
        $notificationTitle = $data['title'] ?? $data['type'] ?? 'Notification';
        $notificationMessage = $data['message'] ?? $data['body'] ?? $data['text'] ?? '';
        $notificationUrl = $data['url'] ?? $data['action_url'] ?? null;
    @endphp
    <div style="border:1px solid #ddd;padding:10px;margin:10px 0;{{ $n->read_at ? 'opacity:.7;' : '' }}" data-live-notification-item data-live-notification-id="{{ $n->id }}">
        <strong>{{ $notificationTitle }}</strong>
        <p>{{ $notificationMessage }}</p>

        @if(!empty($notificationUrl))
            <p><a href="{{ $notificationUrl }}">Open message</a></p>
        @endif

        <small>{{ $n->created_at->diffForHumans() }}</small>

        @if(!$n->read_at)
            <form method="POST" action="{{ route('notifications.read', $n->id) }}">
                @csrf
                <button type="submit">Mark as read</button>
            </form>
        @endif
    </div>
@empty
    <p data-live-notification-empty>No notifications yet.</p>
@endforelse
</div>
