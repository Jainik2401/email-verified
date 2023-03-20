<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Verification Mail</title>
</head>

<body>
    <h1>Hii {{ $data['name'] }},welcome to Referral system</h1>
    <span>Please <a href="{{ url($data['url']) }}">Click here</a>to verified your account!!!</span>
    <h5>Thank you !!</h5>
</body>

</html>
