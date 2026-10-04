@extends('layouts.booking')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-2xl border border-slate-200 p-8 text-center space-y-3">
    <h1 class="text-xl font-bold text-slate-900">Rebook reminders are off</h1>
    <p class="text-sm text-slate-500">
        {{ $tenant->name }} won't send you "time to rebook" messages any more. You'll still get reminders for appointments you book.
    </p>
    <a href="{{ route('book.index', $slug) }}"
       class="inline-flex px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-xl">
        Back to {{ $tenant->name }}
    </a>
</div>
@endsection
