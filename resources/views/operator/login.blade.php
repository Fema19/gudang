<!DOCTYPE html>
<html>
<head>
    <title>Login Operator</title>
</head>
<body>
    <h2>Login Operator</h2>

    @if ($errors->any())
        <div style="color: red;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('operator.login.post') }}">
        @csrf
        <label>Email</label>
        <input type="email" name="email" required><br>

        <label>Password</label>
        <input type="password" name="password" required><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>
