<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 — Server Error</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center">
    <div class="text-center">
        <div class="w-16 h-16 bg-red-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <span class="text-white text-2xl font-black">!</span>
        </div>
        <p class="text-6xl font-black text-slate-900 tracking-tight">500</p>
        <p class="text-lg font-semibold text-slate-600 mt-2">Something went wrong</p>
        <p class="text-sm text-slate-400 mt-1">We're working on it. Please try again in a moment.</p>
        <a href="{{ url('/dashboard') }}"
           class="mt-6 inline-block px-6 py-2.5 bg-slate-900 text-white rounded-lg text-sm font-medium hover:bg-slate-700">
            Back to Dashboard
        </a>
    </div>
</body>
</html>
