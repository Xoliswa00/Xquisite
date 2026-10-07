@extends('errors.layout')

@section('code', '429')
@section('title', 'Too Many Requests')
@section('message', in_array($exception->getMessage(), ['', 'Too Many Attempts.'], true) ? 'You\'ve made too many requests in a short period. Please wait a moment and try again.' : $exception->getMessage())
