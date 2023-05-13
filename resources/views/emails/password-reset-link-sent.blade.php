@component('mail::message')
# Password Reset

Reset your password by clicking the button below:

@component('mail::button', ['url' =>  url("/password/reset/$password_reset->token")])

Reset password
@endcomponent

Please ignore this message if you did not request password reset.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
