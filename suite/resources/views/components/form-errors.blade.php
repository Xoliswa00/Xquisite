@if($errors->any())
<div class="rounded-xl border border-red-300 bg-red-50 dark:border-red-700/60 dark:bg-red-900/20 px-4 py-3 space-y-1">
    <p class="text-sm font-semibold text-red-700 dark:text-red-400">Please fix the following errors:</p>
    <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $error)
            <li class="text-sm text-red-600 dark:text-red-300">{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
