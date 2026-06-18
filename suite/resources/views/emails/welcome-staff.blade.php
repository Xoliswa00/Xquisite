@component('mail::message')
# Welcome to {{ config('app.name') }}

Hi {{ $staff->name }},

Your staff account has been created! Here are your login details:

**Email:** {{ $staff->email }}

Your temporary password has been set by your administrator. When you first log in, you'll be required to change it to a password of your choice.

@component('mail::button', ['url' => $loginUrl])
Log In
@endcomponent

**Next Steps:**
1. Click the button above to log in
2. Use your email as the username
3. Use the temporary password provided by your administrator
4. You'll be prompted to set a new password on your first login

If you have any questions, please contact your administrator.

Thanks,<br>
{{ config('app.name') }} Team
@endcomponent
