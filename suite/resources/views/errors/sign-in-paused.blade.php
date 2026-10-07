@extends('errors.layout')

@section('title', 'Sign-in paused')
@section('message', $reason)

@section('actions')
    @if ($resetUrl)
        <a href="{{ $resetUrl }}" class="btn">Reset your password</a>
        <a href="{{ $signInUrl }}" class="btn btn-ghost">Back to sign in</a>
    @else
        <a href="{{ $signInUrl }}" class="btn">Back to sign in</a>
    @endif
@endsection
