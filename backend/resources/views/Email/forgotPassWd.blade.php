<!DOCTYPE html>
<html>
<head>
    <title>Reset Passwod</title>
</head>
<body>
<div  style="text-align: center;  background-color: #8d9ba024; padding: 20px">
    <div style=""><h3>Reset Password</h3></div>
    <div style="padding: 20px;">
        <p>Your registered email-id is {{$user['email']}} , Please click on the below button to reset password</p>

        {{--<a href="{{url('forum/user/reset', $user->pwResetUser->token)}}">--}}
            {{--<button style="background-color: #1ce4978c; border: 1px solid white;">--}}
                {{--reset password--}}
            {{--</button>--}}
        {{--</a>--}}
    </div>

</div>
</body>
</html>