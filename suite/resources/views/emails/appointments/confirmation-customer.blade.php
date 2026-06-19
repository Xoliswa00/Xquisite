<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Confirmed</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Helvetica,Arial,sans-serif;color:#1e293b;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:40px 16px;">
<tr><td align="center">

    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);">

        <!-- Header -->
        <tr>
            <td style="background:linear-gradient(135deg,#002B5B,#003d7a);padding:32px 40px;text-align:center;border-bottom:3px solid #D4AF37;">
                <p style="margin:0 0 12px;font-size:32px;">✓</p>
                <h1 style="margin:0;font-size:22px;font-weight:700;color:#ffffff;">Your Appointment is Confirmed</h1>
                <p style="margin:8px 0 0;font-size:14px;color:rgba(255,255,255,0.7);">{{ $appointment->tenant->name ?? config('app.name') }}</p>
            </td>
        </tr>

        <!-- Body -->
        <tr>
            <td style="padding:36px 40px;">

                <p style="margin:0 0 24px;font-size:15px;color:#475569;line-height:1.6;">
                    Hi <strong style="color:#0f172a;">{{ $appointment->customer->full_name ?? $appointment->customer->name }}</strong>,<br>
                    your appointment has been confirmed. Here are the details:
                </p>

                <!-- Details -->
                <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:28px;">
                    <tr>
                        <td style="padding:12px 20px;border-bottom:1px solid #e2e8f0;width:38%;">
                            <span style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Service</span>
                        </td>
                        <td style="padding:12px 20px;border-bottom:1px solid #e2e8f0;">
                            <span style="font-size:14px;font-weight:600;color:#1e293b;">{{ $appointment->services->pluck('name')->implode(', ') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:12px 20px;border-bottom:1px solid #e2e8f0;">
                            <span style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Date</span>
                        </td>
                        <td style="padding:12px 20px;border-bottom:1px solid #e2e8f0;">
                            <span style="font-size:14px;font-weight:600;color:#1e293b;">{{ $appointment->scheduled_at->format('l, d F Y') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:12px 20px;border-bottom:1px solid #e2e8f0;">
                            <span style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Time</span>
                        </td>
                        <td style="padding:12px 20px;border-bottom:1px solid #e2e8f0;">
                            <span style="font-size:14px;font-weight:600;color:#1e293b;">{{ $appointment->scheduled_at->format('H:i') }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:12px 20px;border-bottom:1px solid #e2e8f0;">
                            <span style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Duration</span>
                        </td>
                        <td style="padding:12px 20px;border-bottom:1px solid #e2e8f0;">
                            <span style="font-size:14px;font-weight:600;color:#1e293b;">{{ $appointment->duration_minutes }} minutes</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:12px 20px;border-bottom:1px solid #e2e8f0;">
                            <span style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">With</span>
                        </td>
                        <td style="padding:12px 20px;border-bottom:1px solid #e2e8f0;">
                            <span style="font-size:14px;font-weight:600;color:#1e293b;">{{ $appointment->staff->name }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:12px 20px;">
                            <span style="font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Price</span>
                        </td>
                        <td style="padding:12px 20px;">
                            <span style="font-size:15px;font-weight:700;color:#059669;">R{{ number_format($appointment->services->sum(fn($s) => $s->pivot->price_at_booking ?? $s->price), 2) }}</span>
                        </td>
                    </tr>
                </table>

                @if($appointment->notes)
                <table width="100%" cellpadding="0" cellspacing="0" style="background:#fefce8;border:1px solid #fde68a;border-radius:10px;margin-bottom:28px;">
                    <tr>
                        <td style="padding:14px 18px;">
                            <p style="margin:0 0 4px;font-size:11px;font-weight:600;color:#92400e;text-transform:uppercase;">Note</p>
                            <p style="margin:0;font-size:14px;color:#78350f;">{{ $appointment->notes }}</p>
                        </td>
                    </tr>
                </table>
                @endif

                <p style="margin:0;font-size:14px;color:#64748b;line-height:1.6;">
                    If you need to reschedule or cancel, please contact us as soon as possible.
                </p>

            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:20px 40px;text-align:center;">
                <p style="margin:0;font-size:12px;color:#94a3b8;">
                    © {{ date('Y') }} {{ $appointment->tenant->name ?? config('app.name') }}
                </p>
            </td>
        </tr>

    </table>

</td></tr>
</table>

</body>
</html>
