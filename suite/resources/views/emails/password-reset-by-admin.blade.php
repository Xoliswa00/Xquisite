<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><style>body{font-family:Inter,sans-serif;background:#f5f7fa;margin:0;padding:24px}
.card{background:#fff;max-width:520px;margin:0 auto;border-radius:16px;padding:32px;border:1px solid #e8ebf0}
h2{color:#002B5B;font-size:20px;margin:0 0 8px}.sub{color:#718096;font-size:14px;margin:0 0 24px}
.pw{background:#f5f7fa;border:1px solid #e8ebf0;border-radius:8px;padding:12px 16px;font-family:monospace;font-size:18px;font-weight:700;color:#0078D4;letter-spacing:2px;text-align:center;margin:16px 0}
.btn{display:inline-block;background:#0078D4;color:#fff;text-decoration:none;padding:12px 24px;border-radius:10px;font-weight:600;font-size:14px}
.note{color:#718096;font-size:12px;margin-top:24px;padding-top:16px;border-top:1px solid #e8ebf0}
</style></head>
<body>
<div class="card">
    <h2>Password Reset</h2>
    <p class="sub">Hi {{ $user->name }}, your Xquisite account password has been reset by an administrator.</p>

    <p style="color:#2D3748;font-size:14px;margin:0 0 4px">Your temporary password:</p>
    <div class="pw">{{ $tempPassword }}</div>

    <p style="color:#718096;font-size:13px;margin:0 0 20px">You will be required to change this password immediately after logging in.</p>

    <a href="{{ $loginUrl }}" class="btn">Sign In Now</a>

    <p class="note">If you did not expect this email, please contact your administrator immediately at <a href="mailto:support@xquisite.brightfinance-x.co.za" style="color:#0078D4">support@xquisite.brightfinance-x.co.za</a>.</p>
</div>
</body>
</html>
