
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pending Approval</title>
    <style>
body {
    font-family: Arial, sans-serif;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
    background: white;
    padding: 30px;
            border-radius: 10px;
            text-align: center;
            width: 350px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        h2 {
    color: #333;
}

        p {
    color: #666;
    margin: 15px 0;
        }

        .btn {
    display: inline-block;
    padding: 10px 20px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }

        .btn:hover {
    background: #c82333;
}
    </style>
</head>
<body>

<div class="card">
    <h2>⏳ Account Pending</h2>
    <p>Your account is waiting for admin approval.</p>
    <p>Please check back later.</p>

    <a href="{{ route('logout') }}"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
       class="btn">
Logout
    </a>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
@csrf
</form>
</div>

</body>
</html>
