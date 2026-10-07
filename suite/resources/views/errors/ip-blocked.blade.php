@extends('errors.layout')

@section('title', 'Access blocked')
@section('message', $reason)

{{-- Every link on this site returns the same page while blocked, so the only
     useful action is a way to reach a person. --}}
@section('actions')
    <p class="message" style="margin-bottom:0;">
        If you think this is a mistake, email
        <a href="mailto:{{ $supportEmail }}?subject={{ rawurlencode('Blocked access, reference ' . $ip) }}" style="color:#e2e8f0;font-weight:600;">{{ $supportEmail }}</a>
        and give this reference: <strong style="color:#e2e8f0;">{{ $ip }}</strong>
    </p>
@endsection
