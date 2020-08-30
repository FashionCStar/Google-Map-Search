@component('mail::message')
# Change your password

The body of your message.

@component('mail::button', ['url' => 'http://localhost:4200/#/auth/reset-password?token='.$token])
Change Password
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
