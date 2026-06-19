<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — Xquisite Creation</title>
    <link rel="apple-touch-icon" sizes="57x57" href="/img/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/img/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/img/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/img/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/img/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/img/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/img/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/img/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/img/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/img/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon-16x16.png">
    <link rel="manifest" href="/img/manifest.json">
    <meta name="msapplication-TileColor" content="#002B5B">
    <meta name="msapplication-TileImage" content="/img/ms-icon-144x144.png">
    <meta name="theme-color" content="#002B5B">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #001A3A;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .card {
            background: #0f2744;
            border: 1px solid #1e3a5f;
            border-radius: 1.25rem;
            padding: 3rem 2.5rem;
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .logo {
            font-size: 1.1rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #D4AF37;
            margin-bottom: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .logo svg { width: 28px; height: 28px; }
        .code {
            font-size: 5rem;
            font-weight: 800;
            line-height: 1;
            color: #D4AF37;
            margin-bottom: 0.75rem;
        }
        .title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: 0.75rem;
        }
        .message {
            font-size: 0.9rem;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .divider {
            width: 3rem;
            height: 2px;
            background: #D4AF37;
            border-radius: 2px;
            margin: 1.5rem auto;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: #0078D4;
            color: #fff;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.65rem 1.5rem;
            border-radius: 0.625rem;
            transition: background 0.15s;
        }
        .btn:hover { background: #005fa3; }
        .btn-ghost {
            background: transparent;
            border: 1px solid #1e3a5f;
            color: #94a3b8;
            margin-left: 0.75rem;
        }
        .btn-ghost:hover { background: #1e3a5f; color: #f1f5f9; }
        footer {
            margin-top: 3rem;
            font-size: 0.75rem;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">
            <svg fill="none" stroke="#D4AF37" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
            </svg>
            Xquisite Creation
        </div>

        <div class="code">@yield('code')</div>
        <div class="divider"></div>
        <h1 class="title">@yield('title')</h1>
        <p class="message">@yield('message')</p>

        <div>
            <a href="{{ auth()->check() ? route('dashboard') : '/' }}" class="btn">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Go Home
            </a>
            <a href="javascript:history.back()" class="btn btn-ghost">Go Back</a>
        </div>
    </div>
    <footer>Xquisite Creations &copy; {{ date('Y') }}</footer>
</body>
</html>
