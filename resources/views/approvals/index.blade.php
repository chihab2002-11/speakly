<h1>Pending Approvals</h1>

@if (session('success'))
    <p style="color: green">{{ session('success') }}</p>
@endif

@if (session('error'))
    <p style="color: red">{{ session('error') }}</p>
@endif

<table border="1" cellpadding="8">
    <thead>
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Requested role</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($pendingUsers as $u)
        <tr>
            <td>{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td>{{ $u->requested_role }}</td>
            <td>
                <form method="POST" action="{{ route('approvals.approve', $u) }}">
                    @csrf
                    <button type="submit">Approve</button>
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
