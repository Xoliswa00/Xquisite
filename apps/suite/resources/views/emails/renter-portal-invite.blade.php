<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Renter Portal Access</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f4f4f5; margin: 0; padding: 0; color: #18181b; }
        .wrap { max-width: 560px; margin: 32px auto; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e4e4e7; }
        .header { background: linear-gradient(135deg, #4f46e5, #7c3aed); padding: 36px 40px; color: #fff; }
        .header h1 { margin: 0 0 6px; font-size: 20px; font-weight: 700; }
        .header p  { margin: 0; opacity: .8; font-size: 14px; }
        .body { padding: 32px 40px; }
        .creds { background: #f4f4f5; border-radius: 10px; padding: 20px 24px; margin: 24px 0; font-size: 14px; }
        .creds p { margin: 0 0 8px; }
        .creds p:last-child { margin: 0; }
        .label { color: #71717a; font-size: 12px; text-transform: uppercase; letter-spacing: .05em; }
        .value { font-weight: 600; color: #18181b; font-size: 15px; font-family: monospace; }
        .btn { display: inline-block; background: #4f46e5; color: #fff !important; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 14px; }
        .footer { background: #fafafa; border-top: 1px solid #f4f4f5; padding: 20px 40px; font-size: 12px; color: #a1a1aa; text-align: center; }
        a { color: #4f46e5; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1>Your Renter Portal is Ready</h1>
        <p>Hi {{ $renter->name }} — you can now manage your lease, payments and maintenance online.</p>
    </div>

    <div class="body">
        <p style="color:#3f3f46;line-height:1.6;margin:0 0 16px;">
            Your landlord has set up portal access for you. Use the credentials below to log in.
            <strong>Please change your password after your first login.</strong>
        </p>

        <div class="creds">
            <p><span class="label">Email</span><br><span class="value">{{ $renter->email }}</span></p>
            <p><span class="label">Temporary Password</span><br><span class="value">{{ $password }}</span></p>
        </div>

        <div style="text-align:center;margin:28px 0;">
            <a href="{{ $loginUrl }}" class="btn">Log in to your portal →</a>
        </div>

        <p style="font-size:12px;color:#a1a1aa;text-align:center;">
            You can view your lease, payment history, and log maintenance requests from your portal.
        </p>
    </div>

    <div class="footer">
        © {{ date('Y') }} Xquisite Technologies (Pty) Ltd &mdash; One platform. Every operation.
    </div>
</div>
</body>
</html>
