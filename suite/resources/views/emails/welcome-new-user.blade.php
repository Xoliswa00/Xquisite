<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Xquisite</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f4f4f5; margin: 0; padding: 0; color: #18181b; }
        .wrap { max-width: 600px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e4e4e7; }
        .header { background: linear-gradient(135deg, #002B5B, #003d7a); padding: 40px 40px 32px; color: #fff; border-bottom: 3px solid #D4AF37; }
        .header h1 { margin: 0 0 8px; font-size: 24px; font-weight: 700; }
        .header p { margin: 0; opacity: .85; font-size: 15px; }
        .body { padding: 32px 40px; }
        .section-title { font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #71717a; margin: 28px 0 12px; }
        .module-row { display: flex; align-items: flex-start; gap: 14px; padding: 14px 0; border-bottom: 1px solid #f4f4f5; }
        .module-row:last-child { border-bottom: none; }
        .module-dot { width: 10px; height: 10px; border-radius: 50%; margin-top: 5px; flex-shrink: 0; }
        .dot-active { background: #22c55e; }
        .dot-beta   { background: #f59e0b; }
        .module-name { font-weight: 600; font-size: 14px; margin: 0 0 3px; }
        .module-desc { font-size: 13px; color: #71717a; margin: 0; line-height: 1.5; }
        .module-price { font-size: 12px; color: #a1a1aa; margin-top: 4px; }
        .cta-block { background: #f4f4f5; border-radius: 10px; padding: 24px 28px; margin: 28px 0 0; text-align: center; }
        .cta-block p { margin: 0 0 16px; font-size: 15px; color: #3f3f46; }
        .btn { display: inline-block; background: #0078D4; color: #fff !important; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .btn-wa { display: inline-block; background: #25D366; color: #fff !important; text-decoration: none; padding: 10px 22px; border-radius: 8px; font-weight: 600; font-size: 13px; margin-top: 12px; }
        .footer { padding: 20px 40px; background: #fafafa; border-top: 1px solid #f4f4f5; font-size: 12px; color: #a1a1aa; text-align: center; }
        a { color: #0078D4; }
    </style>
</head>
<body>
<div class="wrap">

    <div class="header">
        <img src="{{ asset('img/android-icon-192x192.png') }}" alt="Xquisite" style="height:52px;width:auto;margin:0 auto 16px;display:block;">
        <h1>Welcome to Xquisite, {{ $user->name }}!</h1>
        <p>Your account is set up and ready. Here's what you can activate right now.</p>
    </div>

    <div class="body">

        <p style="color:#3f3f46;line-height:1.6;margin:0 0 8px;">
            Xquisite is a modular business platform — you choose what fits your operation and activate only what you need.
            No bloat, no locked features you never use.
        </p>

        {{-- Live modules --}}
        @if ($activeModules->isNotEmpty())
        <div class="section-title">Live — Available now</div>
        @foreach ($activeModules as $module)
        <div class="module-row">
            <div class="module-dot dot-active"></div>
            <div>
                <p class="module-name">{{ $module->name }}</p>
                <p class="module-desc">{{ $module->description }}</p>
                <p class="module-price">From R{{ number_format($module->price, 0) }}/month</p>
            </div>
        </div>
        @endforeach
        @endif

        {{-- Beta modules --}}
        @if ($betaModules->isNotEmpty())
        <div class="section-title">In Testing — launching soon</div>
        @foreach ($betaModules as $module)
        <div class="module-row">
            <div class="module-dot dot-beta"></div>
            <div>
                <p class="module-name">{{ $module->name }}</p>
                <p class="module-desc">{{ $module->description }}</p>
                <p class="module-price">From R{{ number_format($module->price, 0) }}/month</p>
            </div>
        </div>
        @endforeach
        @endif

        @if ($activeModules->isEmpty() && $betaModules->isEmpty())
        <p style="color:#71717a;font-size:13px;line-height:1.6;margin:16px 0;">
            We're finishing up the module catalogue — check your dashboard to see what's available and activate what fits your business.
        </p>
        @endif

        <div class="cta-block">
            <p>Ready to pick your modules and get started?</p>
            <a href="{{ $modulesUrl }}" class="btn">Choose my modules →</a>
            <br>
            <a href="{{ $whatsappUrl }}" class="btn-wa" target="_blank">💬 Chat with us on WhatsApp</a>
        </div>

    </div>

    <div class="footer">
        You're receiving this because you created an account on Xquisite.<br>
        <a href="{{ $loginUrl }}">Log in to your account</a> &nbsp;·&nbsp;
        <a href="mailto:{{ config('contact.support_email') }}">Contact support</a>
    </div>

</div>
</body>
</html>
