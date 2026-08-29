<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Contractor Portal Access</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f4f4f5; margin: 0; padding: 0; color: #18181b; }
        .wrap { max-width: 560px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e4e4e7; }
        .header { background: linear-gradient(135deg, #002B5B, #0078D4); padding: 36px 40px; color: #fff; }
        .header h1 { margin: 0 0 6px; font-size: 20px; font-weight: 700; }
        .header p  { margin: 0; opacity: .8; font-size: 14px; }
        .body { padding: 32px 40px; }
        .creds { background: #f4f4f5; border-radius: 10px; padding: 20px 24px; margin: 24px 0; font-size: 14px; }
        .creds p { margin: 0 0 8px; }
        .creds p:last-child { margin: 0; }
        .label { color: #71717a; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; }
        .value { font-weight: 600; color: #18181b; font-size: 15px; font-family: monospace; }
        .btn { display: inline-block; background: #0078D4; color: #fff !important; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .footer { background: #fafafa; border-top: 1px solid #f4f4f5; padding: 20px 40px; font-size: 12px; color: #a1a1aa; text-align: center; }
        a { color: #0078D4; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1>Your Contractor Portal is Ready</h1>
        <p>Hi {{ $contractor->name }} — you can now view assigned jobs, submit quotes, and track payment online.</p>
    </div>

    <div class="body">
        <p style="color:#3f3f46;line-height:1.6;margin:0 0 16px;">
            You&rsquo;ve been added as a contractor. Use the credentials below to log in.
            <strong>Please change your password after your first login.</strong>
        </p>

        <div class="creds">
            <p><span class="label">Email</span><br><span class="value">{{ $contractor->email }}</span></p>
            <p><span class="label">Temporary Password</span><br><span class="value">{{ $password }}</span></p>
        </div>

        <div style="text-align:center;margin:28px 0;">
            <a href="{{ $loginUrl }}" class="btn">Log in to your portal &rarr;</a>
        </div>

        <p style="font-size:12px;color:#a1a1aa;text-align:center;">
            From your portal you can see jobs assigned to you, submit a quote, upload photos, and mark a job complete once approved.
        </p>
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} Xquisite Technologies (Pty) Ltd &mdash; One platform. Every operation.
        <br>
        <span style="display:inline-flex;align-items:center;gap:6px;margin-top:6px;">
            <img src="{{ asset('img/android-icon-96x96.png') }}" alt="Xquisite Creations" style="height:16px;width:16px;border-radius:4px;">
            Powered by Xquisite Creations
        </span>
    </div>
</div>
</body>
</html>
