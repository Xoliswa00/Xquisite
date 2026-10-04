<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time to rebook</title>
</head>
@php
    $business = $appointment->tenant->name ?? config('app.name');
    $services = $appointment->services->pluck('name')->join(', ', ' & ') ?: 'appointment';
@endphp
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:Helvetica,Arial,sans-serif;color:#1e293b;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:40px 16px;">
<tr><td align="center">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
        <tr>
            <td style="background:#002B5B;padding:28px 40px;text-align:center;border-bottom:3px solid #D4AF37;">
                <h1 style="margin:0;font-size:22px;font-weight:700;color:#ffffff;">Time for your next {{ $services }}?</h1>
                <p style="margin:8px 0 0;font-size:14px;color:#cbd5e1;">{{ $business }}</p>
            </td>
        </tr>
        <tr>
            <td style="padding:32px 40px;">
                <p style="margin:0 0 20px;font-size:15px;color:#475569;line-height:1.6;">
                    Hi <strong style="color:#0f172a;">{{ $appointment->customer->name }}</strong>,
                    your last {{ $services }} with {{ $business }} was on {{ $appointment->scheduled_at->format('d F Y') }}.
                    @if($appointment->isLookSaved())
                        Your saved look is in My Bookings, so you can book the same look again and they'll see it before you arrive.
                    @else
                        If you'd like your next one, you can book it online in a minute.
                    @endif
                </p>

                <p style="margin:0 0 8px;text-align:center;">
                    <a href="{{ $bookUrl }}" style="display:inline-block;background:#0078D4;color:#ffffff;text-decoration:none;font-size:15px;font-weight:600;padding:12px 28px;border-radius:10px;">
                        {{ $appointment->isLookSaved() ? 'Book this look again' : 'Book your next visit' }}
                    </a>
                </p>
            </td>
        </tr>
        <tr>
            <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:18px 40px;text-align:center;">
                <p style="margin:0 0 6px;font-size:12px;color:#94a3b8;">© {{ date('Y') }} {{ $business }}</p>
                @if($optOutUrl)
                    <p style="margin:0;font-size:12px;color:#94a3b8;">
                        Don't want these? <a href="{{ $optOutUrl }}" style="color:#64748b;">Stop rebook reminders</a>
                    </p>
                @endif
            </td>
        </tr>
    </table>
</td></tr>
</table>
</body>
</html>
