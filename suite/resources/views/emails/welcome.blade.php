<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Xquisite Suite</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:'Figtree',Helvetica,Arial,sans-serif;color:#1e293b;">

<table width="100%" cellpadding="0" cellspacing="0" class="summary-on-mobile" style="background-color:#f1f5f9;padding:40px 16px;">
<tr><td align="center">

    <!-- Card -->
    <table width="100%" cellpadding="0" cellspacing="0" class="summary-on-mobile" style="max-width:580px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);">

        <!-- Header band -->
        <tr>
            <td style="background:linear-gradient(135deg,#002B5B,#003d7a);padding:36px 40px;text-align:center;border-bottom:3px solid #D4AF37;">
                <img src="{{ asset('img/android-icon-192x192.png') }}" alt="Xquisite" style="height:56px;width:auto;margin:0 auto 16px;display:block;">
                <p style="margin:0;font-size:20px;font-weight:700;color:#ffffff;letter-spacing:0.3px;">Xquisite Suite</p>
                <p style="margin:6px 0 0;color:rgba(255,255,255,0.70);font-size:14px;">Your business management platform</p>
            </td>
        </tr>

        <!-- Body -->
        <tr>
            <td style="padding:40px 40px 32px;">

                <h1 style="margin:0 0 8px;font-size:24px;font-weight:700;color:#0f172a;">
                    Welcome, {{ $user->name }}! 🎉
                </h1>
                <p style="margin:0 0 24px;font-size:15px;color:#64748b;line-height:1.6;">
                    Your <strong style="color:#1e293b;">{{ $tenant->name }}</strong> account is ready.
                    You're on a <strong style="color:#0078D4;">14-day free trial</strong> — no credit card required.
                </p>

                <!-- Trial callout -->
                <table width="100%" cellpadding="0" cellspacing="0" class="summary-on-mobile" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;margin-bottom:28px;">
                    <tr>
                        <td style="padding:16px 20px;">
                            <p style="margin:0 0 4px;font-size:13px;font-weight:600;color:#0078D4;text-transform:uppercase;letter-spacing:0.5px;">Free Trial Active</p>
                            <p style="margin:0;font-size:14px;color:#374151;">
                                Your trial runs until <strong>{{ $tenant->trial_ends_at->format('d F Y') }}</strong>.
                                Upgrade anytime to keep access.
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- What's included -->
                <p style="margin:0 0 16px;font-size:14px;font-weight:600;color:#0f172a;">What's included in your account:</p>
                <table width="100%" cellpadding="0" cellspacing="0" class="summary-on-mobile" style="margin-bottom:28px;">
                    @foreach([
                        ['Booking Engine', 'Appointments, staff scheduling & automated reminders'],
                        ['Point of Sale', 'Fast checkout for services and products'],
                        ['Inventory Control', 'Live stock levels with reorder alerts'],
                        ['Supplier Management', 'Purchase orders and vendor tracking'],
                        ['Analytics Dashboard', 'Revenue, bookings, and product insights'],
                    ] as [$title, $desc])
                    <tr>
                        <td style="padding:6px 0;vertical-align:top;">
                            <table cellpadding="0" cellspacing="0" class="summary-on-mobile">
                                <tr>
                                    <td style="width:24px;vertical-align:top;padding-top:2px;">
                                        <div style="width:18px;height:18px;background:#dbeafe;border-radius:4px;text-align:center;line-height:18px;font-size:11px;color:#0078D4;">✓</div>
                                    </td>
                                    <td style="padding-left:10px;">
                                        <p style="margin:0;font-size:14px;color:#1e293b;"><strong>{{ $title }}</strong> — <span style="color:#64748b;">{{ $desc }}</span></p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endforeach
                </table>

                <!-- CTA -->
                <table width="100%" cellpadding="0" cellspacing="0" class="summary-on-mobile" style="margin-bottom:28px;">
                    <tr>
                        <td align="center">
                            <a href="{{ url('/dashboard') }}"
                               style="display:inline-block;background:#0078D4;color:#ffffff;font-size:15px;font-weight:600;padding:14px 32px;border-radius:10px;text-decoration:none;letter-spacing:0.2px;">
                                Open Your Dashboard →
                            </a>
                        </td>
                    </tr>
                </table>

                <!-- Getting started tips -->
                <table width="100%" cellpadding="0" cellspacing="0" class="summary-on-mobile" style="background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;margin-bottom:8px;">
                    <tr>
                        <td style="padding:20px;">
                            <p style="margin:0 0 12px;font-size:13px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.5px;">Quick Start</p>
                            @foreach([
                                'Add your services under Booking → Services',
                                'Add your team under Booking → Staff',
                                'Create your first appointment',
                                'Add your products and set stock levels',
                            ] as $i => $step)
                            <p style="margin:0 0 8px;font-size:14px;color:#374151;">
                                <span style="display:inline-block;background:#dbeafe;color:#0078D4;font-size:11px;font-weight:700;border-radius:4px;padding:1px 6px;margin-right:8px;">{{ $i + 1 }}</span>
                                {{ $step }}
                            </p>
                            @endforeach
                        </td>
                    </tr>
                </table>

            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:24px 40px;text-align:center;">
                <p style="margin:0 0 4px;font-size:13px;color:#94a3b8;">
                    Questions? Reply to this email or contact support.
                </p>
                <p style="margin:0;font-size:12px;color:#cbd5e1;">
                    © {{ date('Y') }} Xquisite Technologies (Pty) Ltd &mdash; One platform. Every operation.
                </p>
            </td>
        </tr>

    </table>

</td></tr>
</table>

</body>
</html>
