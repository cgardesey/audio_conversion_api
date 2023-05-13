@component('mail::message')
# Email Verification

Welcome to School Direct student. Please confirm your email address by clicking the button below:

@component('mail::button', ['url' =>  url("email/$user->email/$user->confirmation_token")])

Confirm
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
