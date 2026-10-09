<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your quote</title>
</head>
@php
    $business = $appointment->tenant->name ?? config('app.name');
    $minutes  = (int) $appointment->quoted_duration_minutes;
    $length   = \App\Services\Notifications\BookingNotificationService::humanMinutes($minutes);
@endphp
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Helvetica,Arial,sans-serif;color:#1e293b;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:40px 16px;">
<tr><td align="center">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
        <tr>
            <td style="background:#002B5B;padding:28px 40px;text-align:center;border-bottom:3px solid #D4AF37;">
                <h1 style="margin:0;font-size:22px;font-weight:700;color:#ffffff;">Your quote is ready</h1>
                <p style="margin:8px 0 0;font-size:14px;color:#cbd5e1;">{{ $business }}</p>
            </td>
        </tr>
        <tr>
            <td style="padding:32px 40px;">
                <p style="margin:0 0 20px;font-size:15px;color:#475569;line-height:1.6;">
                    Hi <strong style="color:#0f172a;">{{ $appointment->customer->name }}</strong>,
                    {{ $business }} looked at your photos and worked out the price and time for your booking on
                    <strong style="color:#0f172a;">{{ $appointment->scheduled_at->format('l, d F Y \a\t H:i') }}</strong>.
                </p>

                <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;margin-bottom:20px;">
                    <tr>
                        <td style="padding:14px 20px;border-bottom:1px solid #e2e8f0;width:38%;font-size:14px;color:#64748b;">Price</td>
                        <td style="padding:14px 20px;border-bottom:1px solid #e2e8f0;font-size:18px;font-weight:700;color:#002B5B;">R{{ number_format((float) $appointment->quoted_price, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding:14px 20px;font-size:14px;color:#64748b;">Time needed</td>
                        <td style="padding:14px 20px;font-size:15px;font-weight:600;color:#1e293b;">About {{ $length }}</td>
                    </tr>
                </table>

                @if($appointment->quote_note)
                    <p style="margin:0 0 20px;font-size:14px;color:#475569;line-height:1.6;white-space:pre-line;">{{ $appointment->quote_note }}</p>
                @endif

                <p style="margin:0 0 24px;font-size:14px;color:#475569;line-height:1.6;">
                    @if($appointment->quote_expires_at)
                        Your slot is held until <strong style="color:#0f172a;">{{ $appointment->quote_expires_at->format('l, d F, H:i') }}</strong>.
                    @else
                        Your slot is held while you decide.
                    @endif
                    Accept to confirm the new price and time, or decline to cancel with nothing to pay.
                </p>

                <p style="margin:0;text-align:center;">
                    <a href="{{ $myBookingsUrl }}" style="display:inline-block;background:#0078D4;color:#ffffff;text-decoration:none;font-size:15px;font-weight:600;padding:12px 28px;border-radius:10px;">
                        View and accept your quote
                    </a>
                </p>
            </td>
        </tr>
        <tr>
            <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:18px 40px;text-align:center;">
                <p style="margin:0;font-size:12px;color:#94a3b8;">© {{ date('Y') }} {{ $business }}</p>
            </td>
        </tr>
    </table>
</td></tr>
</table>
</body>
</html>
