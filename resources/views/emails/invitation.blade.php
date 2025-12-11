<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Invitation</title>
</head>

<body>
    <h1>Hi, {{ $user->name }}</h1>
    <h4>You have been invited to join us on this URL {{ route('home') }}</h4>
    <p>Your Email: {{ $user->email }}</p>
    <p>Your Password: {{ $password }}</p>

    <p>Thanks</p>
</body>

</html>
