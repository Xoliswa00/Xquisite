<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Helvetica,Arial,sans-serif;color:#1e293b;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:40px 16px;">
<tr><td align="center">

    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);">

        <!-- Header -->
        <tr>
            <td style="background:linear-gradient(135deg,#002B5B,#003d7a);padding:32px 40px;text-align:center;border-bottom:3px solid #D4AF37;">
                <img src="{{ asset('img/android-icon-192x192.png') }}" alt="{{ config('app.name') }}" style="height:56px;width:auto;margin:0 auto 16px;display:block;">
                <h1 style="margin:0;font-size:22px;font-weight:700;color:#ffffff;">Welcome to the team!</h1>
                <p style="margin:8px 0 0;font-size:14px;color:rgba(255,255,255,0.7);">Your staff account is ready</p>
            </td>
        </tr>

        <!-- Body -->
        <tr>
            <td style="padding:36px 40px;">

                <p style="margin:0 0 24px;font-size:15px;color:#475569;line-height:1.6;">
                    Hi <strong style="color:#0f172a;">{{ $staff->name }}</strong>,<br>
                    your staff account has been created. Here are your login details:
                </p>

                <!-- Login details -->
                <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:28px;">
                    <tr>
                        <td style="padding:14px 20px;border-bottom:1px solid #e2e8f0;width:38%;">
                            <span style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Email</span>
                        </td>
                        <td style="padding:14px 20px;border-bottom:1px solid #e2e8f0;">
                            <span style="font-size:14px;font-weight:600;color:#1e293b;">{{ $staff->email }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 20px;">
                            <span style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Password</span>
                        </td>
                        <td style="padding:14px 20px;">
                            <span style="font-size:14px;color:#475569;">Set by your administrator — you'll be prompted to change it on first login.</span>
                        </td>
                    </tr>
                </table>

                <!-- CTA -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                    <tr>
                        <td align="center">
                            <a href="{{ $loginUrl }}" style="display:inline-block;background:#0078D4;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;padding:14px 32px;border-radius:10px;">
                                Log In Now
                            </a>
                        </td>
                    </tr>
                </table>

                <!-- Steps -->
                <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:12px;margin-bottom:28px;">
                    <tr>
                        <td style="padding:18px 20px;">
                            <p style="margin:0 0 10px;font-size:12px;font-weight:600;color:#0369a1;text-transform:uppercase;letter-spacing:0.5px;">Next Steps</p>
                            <p style="margin:0 0 6px;font-size:14px;color:#0f172a;">1. Click the button above to go to the login page</p>
                            <p style="margin:0 0 6px;font-size:14px;color:#0f172a;">2. Sign in with your email address</p>
                            <p style="margin:0 0 6px;font-size:14px;color:#0f172a;">3. Use the temporary password provided by your administrator</p>
                            <p style="margin:0;font-size:14px;color:#0f172a;">4. You'll be asked to set a new password immediately</p>
                        </td>
                    </tr>
                </table>

                <p style="margin:0;font-size:14px;color:#64748b;line-height:1.6;">
                    If you have any questions, contact your administrator directly.
                </p>

            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:20px 40px;text-align:center;">
                <p style="margin:0;font-size:12px;color:#94a3b8;">
                    © {{ date('Y') }} {{ config('app.name') }}
                </p>
            </td>
        </tr>

    </table>

</td></tr>
</table>

</body>
</html>
