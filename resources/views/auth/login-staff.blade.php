<!DOCTYPE html>
<html>
<head>
    <title>Login Staff</title>
</head>
<body>
    <h2>Login Staff</h2>

    @if ($errors->any())
        <div style="color: red;">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('staff.login') }}">
        @csrf
        <label for="nip">NIP:</label>
        <input type="text" name="nip" id="nip" required>
        <br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        <br>

        <button type="submit">Login</button>
    </form>
</body>
</html>
