<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .container { font-family: Arial, Helvetica, sans-serif; color: #333; padding: 20px; }
        .otp-box { display: inline-block; background: #f8f9fa; border: 1px dashed #ddd; padding: 12px 18px; margin: 12px 0; font-size: 28px; letter-spacing: 6px; font-weight: 700; font-family: 'Courier New', Courier, monospace; }
        .note { color: #6c757d; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <p>Hi,</p>

        <p>Use the OTP code below to complete your verification:</p>

        <div class="otp-box">{{ $otp }}</div>

        <p class="note">This code is valid for 10 minutes. Do not share this code with anyone.</p>

        <p>If you did not request this, please ignore this message.</p>

        <p>Regards,<br/>Application Team</p>
    </div>
</body>
</html>
