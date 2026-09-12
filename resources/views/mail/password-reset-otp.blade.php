<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password reset code</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;padding:32px;">
                    <tr>
                        <td>
                            <p style="margin:0 0 8px;font-size:13px;font-weight:600;color:#4f46e5;letter-spacing:.04em;text-transform:uppercase;">{{ $companyName }}</p>
                            <h1 style="margin:0 0 12px;font-size:22px;line-height:1.3;">Your reset code</h1>
                            <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#475569;">
                                Hi {{ $recipientName }}, use this 6-digit code to reset your password. It expires in 10 minutes.
                            </p>
                            <p style="margin:0 0 24px;text-align:center;font-size:32px;letter-spacing:10px;font-weight:700;color:#0f172a;">
                                {{ $code }}
                            </p>
                            <p style="margin:0;font-size:13px;line-height:1.6;color:#64748b;">
                                If you did not request this, you can ignore this email. Someone else may have typed your address by mistake.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
