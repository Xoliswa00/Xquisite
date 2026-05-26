@component('mail::message')
# Client Portal Invitation

Hello {{ $invitation['name'] }},

You have been invited to access your client portal. Please click the button below to set up your account:

@component('mail::button', ['url' => $invitation['link']])
Access Portal
@endcomponent

This link will expire on {{ $invitation['expires_at']->format('d M Y H:i') }}.

Thanks,<br>
{{ config('app.name') }}
@endcomponent