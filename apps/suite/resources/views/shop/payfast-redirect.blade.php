<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Redirecting to PayFast…</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet"/>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Figtree', sans-serif; background: #f9fafb; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1.5rem; }
        .card { background: white; border-radius: 1.25rem; border: 1px solid #e5e7eb; padding: 2.5rem; text-align: center; max-width: 360px; width: 100%; }
        .spinner { width: 3rem; height: 3rem; border: 3px solid #e0e7ff; border-top-color: #4f46e5; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1.25rem; }
        @keyframes spin { to { transform: rotate(360deg); } }
        h1 { font-size: 1.125rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem; }
        p { font-size: 0.875rem; color: #6b7280; }
        .badge { display: inline-flex; align-items: center; gap: 0.375rem; margin-top: 1.25rem; font-size: 0.75rem; color: #4b5563; background: #f3f4f6; border-radius: 9999px; padding: 0.375rem 0.875rem; }
        .badge svg { width: 1rem; height: 1rem; color: #4f46e5; }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner"></div>
        <h1>Redirecting to PayFast</h1>
        <p>Please wait while we redirect you to the secure payment page…</p>
        <div class="badge">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Secured by PayFast
        </div>
    </div>

    <form id="payfast-form" action="{{ $paymentUrl }}" method="POST" style="display:none">
        @foreach($paymentData as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () {
                document.getElementById('payfast-form').submit();
            }, 1200);
        });
    </script>
</body>
</html>
