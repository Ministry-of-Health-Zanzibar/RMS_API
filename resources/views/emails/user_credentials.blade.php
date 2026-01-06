<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>MOHZ External Referral System Login Credentials</title>
</head>
<body>
    <h2>Hello {{ $user->first_name }},</h2>

    <p>Welcome to MOHZ External Referral System! Here are your login credentials:</p>

    <ul>
        <li><strong>Email:</strong> {{ $user->email }}</li>
        <li><strong>Password:</strong> {{ $password }}</li>
    </ul>

    <p>Please <a href="{{ url('/login') }}">click here to login</a> and change your password after your first login.</p>

    <p>Thanks,<br>MOHZ ICT UNIT</p>
</body>
</html>
