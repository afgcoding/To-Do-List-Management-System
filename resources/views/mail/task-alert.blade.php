<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;padding:32px;">
                    <tr>
                        <td>
                            <p style="margin:0 0 8px;font-size:13px;font-weight:600;color:{{ $brandColor }};letter-spacing:.04em;text-transform:uppercase;">{{ $companyName }}</p>
                            <h1 style="margin:0 0 12px;font-size:22px;line-height:1.3;">{{ $title }}</h1>
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#475569;">Hi {{ $recipientName }},</p>
                            <p style="margin:0 0 16px;font-size:15px;line-height:1.6;color:#475569;">{{ $alertMessage }}</p>
                            <p style="margin:0 0 24px;font-size:14px;color:#64748b;">Task: <strong style="color:#0f172a;">{{ $taskTitle }}</strong></p>
                            <p style="margin:0;">
                                <a href="{{ $actionUrl }}" style="display:inline-block;background:{{ $brandColor }};color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;padding:12px 18px;border-radius:10px;">Open task</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
