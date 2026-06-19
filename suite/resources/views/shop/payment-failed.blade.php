<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment problem — {{ $tenant->name ?? 'Shop' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Figtree', sans-serif; background: #f9fafb; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1.5rem; }
        .card { background: white; border-radius: 1.25rem; border: 1px solid #e5e7eb; padding: 2.5rem; text-align: center; max-width: 420px; width: 100%; }
        .icon { width: 3.5rem; height: 3.5rem; border-radius: 9999px; background: #fef2f2; color: #dc2626; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem; }
        .icon svg { width: 1.75rem; height: 1.75rem; }
        h1 { font-size: 1.25rem; font-weight: 600; color: #111827; margin-bottom: 0.625rem; }
        p { font-size: 0.9rem; color: #6b7280; line-height: 1.5; }
        .actions { display: flex; flex-direction: column; gap: 0.625rem; margin-top: 1.75rem; }
        a.btn { display: inline-flex; align-items: center; justify-content: center; padding: 0.75rem 1.25rem; border-radius: 0.75rem; font-weight: 600; font-size: 0.875rem; text-decoration: none; transition: background .15s; }
        .btn-primary { background: #4f46e5; color: white; }
        .btn-primary:hover { background: #4338ca; }
        .btn-ghost { color: #4b5563; border: 1px solid #e5e7eb; }
        .btn-ghost:hover { background: #f9fafb; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1>Something went wrong</h1>
        <p>{{ $message ?? 'We could not complete your payment. No money has been taken.' }}</p>
        <div class="actions">
            <a class="btn btn-primary" href="{{ route('shop.checkout', $tenant->slug) }}">Try again</a>
            <a class="btn btn-ghost" href="{{ route('shop.index', $tenant->slug) }}">Back to shop</a>
        </div>
    </div>
</body>
</html>
