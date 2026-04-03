<h1>My Notifications</h1>

@forelse($notifications as $n)
    <div style="border:1px solid #ddd;padding:10px;margin:10px 0;{{ $n->read_at ? 'opacity:.7;' : '' }}">
        <strong>{{ $n->data['title'] ?? 'Notification' }}</strong>
        <p>{{ $n->data['message'] ?? '' }}</p>

        @if(!empty($n->data['url']))
            <p><a href="{{ $n->data['url'] }}">Open message</a></p>
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
    <p>No notifications yet.</p>
@endforelse
