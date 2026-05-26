<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Reminder</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Helvetica,Arial,sans-serif;color:#1e293b;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:40px 16px;">
<tr><td align="center">

    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);">

        <!-- Header -->
        <tr>
            <td style="background:linear-gradient(135deg,#1e1b4b,#312e81);padding:32px 40px;text-align:center;">
                <p style="margin:0 0 4px;font-size:12px;font-weight:600;color:rgba(199,210,254,0.8);text-transform:uppercase;letter-spacing:1px;">Reminder</p>
                <h1 style="margin:0;font-size:22px;font-weight:700;color:#ffffff;">
                    {{ $reminderType === '1h' ? 'Your appointment is in 1 hour' : 'Your appointment is tomorrow' }}
                </h1>
            </td>
        </tr>

        <!-- Body -->
        <tr>
            <td style="padding:36px 40px;">

                <p style="margin:0 0 24px;font-size:15px;color:#475569;line-height:1.6;">
                    Hi <strong style="color:#0f172a;">{{ $appointment->customer->name }}</strong>,
                    this is a friendly reminder about your upcoming appointment.
                </p>

                <!-- Details block -->
                <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:28px;">
                    @foreach([
                        ['Service',  $appointment->service->name],
                        ['Date',     $appointment->scheduled_at->format('l, d F Y')],
                        ['Time',     $appointment->scheduled_at->format('H:i')],
                        ['Duration', $appointment->duration_minutes . ' minutes'],
                        ['With',     $appointment->staff->name],
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
                </table>

                <p style="margin:0;font-size:14px;color:#64748b;line-height:1.6;">
                    We look forward to seeing you. If something has come up, please contact us to reschedule.
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
