<!DOCTYPE html>
<html>
<head>
    <title>Verify Email</title>
</head>
<body>
<div  style="text-align: center;  background-color: #8d9ba024; padding: 20px">
    <div style=""><h3>Thanks for joining PreMatchTalk</h3></div>
    <div style="padding: 20px;">
        <p>Your registered email-id is {{$user['email']}} , Please click on the below button to verify your email account</p>

        <a href="{{url('forum/user/verify', $user->verifyUser->token)}}">
            <button style="background-color: #1ce4978c; border: 1px solid white;">
                Login to your new account
            </button>
        </a>
    </div>

</div>
<div  style="text-align: center; padding: 20px">
    <div style="float: left">
        <p>
            Have you got any questions about the Prematch Talk? Contact us to info@prematchtalk.com
        </p>
    </div>



</div>

</body>
</html>