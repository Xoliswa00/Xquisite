<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Confirmed</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Helvetica,Arial,sans-serif;color:#1e293b;">

<table width="100%" cellpadding="0" cellspacing="0" class="summary-on-mobile" style="background-color:#f1f5f9;padding:40px 16px;">
<tr><td align="center">

    <table width="100%" cellpadding="0" cellspacing="0" class="summary-on-mobile" style="max-width:560px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);">

        <!-- Header -->
        <tr>
            <td style="background:linear-gradient(135deg,#052e16,#14532d);padding:32px 40px;text-align:center;">
                <div style="width:56px;height:56px;background:rgba(255,255,255,0.15);border-radius:50%;margin:0 auto 12px;display:flex;align-items:center;justify-content:center;">
                    <span style="font-size:28px;">✓</span>
                </div>
                <h1 style="margin:0;font-size:22px;font-weight:700;color:#ffffff;">Appointment Confirmed</h1>
                <p style="margin:8px 0 0;font-size:14px;color:rgba(255,255,255,0.7);">{{ $appointment->tenant->name ?? config('app.name') }}</p>
            </td>
        </tr>

        <!-- Body -->
        <tr>
            <td style="padding:36px 40px;">

                <p style="margin:0 0 24px;font-size:15px;color:#475569;line-height:1.6;">
                    Hi <strong style="color:#0f172a;">{{ $appointment->customer->name }}</strong>,<br>
                    your appointment has been confirmed. Here are the details:
                </p>

                <!-- Details block -->
                <table width="100%" cellpadding="0" cellspacing="0" class="summary-on-mobile" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:28px;">
                    @foreach([
                        ['Service',   $appointment->services->pluck('name')->implode(', ')],
                        ['Date',      $appointment->scheduled_at->format('l, d F Y')],
                        ['Time',      $appointment->scheduled_at->format('H:i')],
                        ['Duration',  $appointment->duration_minutes . ' minutes'],
                        ['With',      $appointment->staff->name],
                    ] as [$label, $value])
                    <tr>
                        <td style="padding:12px 20px;border-bottom:1px solid #e2e8f0;width:35%;">
                            <span style="font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">{{ $label }}</span>
                        </td>
                        <td style="padding:12px 20px;border-bottom:1px solid #e2e8f0;">
                            <span style="font-size:14px;font-weight:600;color:#1e293b;">{{ $value }}</span>
                        </td>
                    </tr>
                    @endforeach
                    <tr>
                        <td style="padding:12px 20px;width:35%;">
                            <span style="font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;">Price</span>
                        </td>
                        <td style="padding:12px 20px;">
                            <span style="font-size:15px;font-weight:700;color:#059669;">R{{ number_format($appointment->services->sum(fn($s) => $s->pivot->price_at_booking ?? $s->price), 2) }}</span>
                        </td>
                    </tr>
                </table>

                @if($appointment->notes)
                <table width="100%" cellpadding="0" cellspacing="0" class="summary-on-mobile" style="background:#fefce8;border:1px solid #fde68a;border-radius:10px;margin-bottom:28px;">
                    <tr>
                        <td style="padding:14px 18px;">
                            <p style="margin:0 0 4px;font-size:12px;font-weight:600;color:#92400e;text-transform:uppercase;">Note</p>
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
