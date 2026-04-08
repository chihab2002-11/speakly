<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approvals</title>
    <style>
        :root {
            --bg: #f6f8fb;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #e5e7eb;
            --approve: #16a34a;
            --approve-hover: #15803d;
            --reject: #dc2626;
            --reject-hover: #b91c1c;
            --shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        .container {
            max-width: 1080px;
            margin: 40px auto;
            padding: 0 16px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header h1 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .subtitle {
            margin: 0;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .alerts {
            padding: 14px 20px 0;
        }

        .alert {
            padding: 10px 12px;
            border-radius: 10px;
            font-size: 0.92rem;
            margin-bottom: 10px;
        }

        .alert-success {
            background: #ecfdf3;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            text-align: left;
            font-size: 0.86rem;
            color: var(--muted);
            font-weight: 600;
            letter-spacing: .02em;
            background: #fafafa;
            border-bottom: 1px solid var(--border);
            padding: 12px 16px;
        }

        tbody td {
            border-bottom: 1px solid var(--border);
            padding: 14px 16px;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: #fcfcfd;
        }

        .role-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            background: #eef2ff;
            color: #3730a3;
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 0.84rem;
            font-weight: 600;
            cursor: pointer;
            color: #fff;
            transition: 0.15s ease;
        }

        .btn-approve {
            background: var(--approve);
        }

        .btn-approve:hover {
            background: var(--approve-hover);
        }

        .btn-reject {
            background: var(--reject);
        }

        .btn-reject:hover {
            background: var(--reject-hover);
        }

        .reason-input {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 0.84rem;
            min-width: 180px;
        }

        .reason-input:focus {
            outline: none;
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .15);
        }

        .empty {
            padding: 24px 16px;
            text-align: center;
            color: var(--muted);
        }

        @media (max-width: 860px) {
            .actions {
                flex-direction: column;
                align-items: stretch;
            }

            .reason-input {
                min-width: 100%;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="header">
            <div>
                <h1>Pending Approvals</h1>
                <p class="subtitle">Review and approve or reject newly registered accounts</p>
            </div>
        </div>

        <div class="alerts">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
        </div>

        @if($pendingUsers->isEmpty())
            <div class="empty">No pending users right now 🎉</div>
        @else
            <table>
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
                        <td><span class="role-badge">{{ $u->requested_role }}</span></td>
                        <td>
                            <div class="actions">
                                <form method="POST" action="{{ route('approvals.approve', ['role' => $currentRole, 'user' => $u]) }}">
                                    @csrf
                                    <button class="btn btn-approve" type="submit">Approve</button>
                                </form>

                                <form method="POST" action="{{ route('approvals.reject', ['role' => $currentRole, 'user' => $u]) }}">
                                    @csrf
                                    <input class="reason-input" type="text" name="reason" placeholder="Reason (optional)">
                                    <button class="btn btn-reject" type="submit">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
</body>
</html>
