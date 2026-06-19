<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kode OTP Reset Password</title>
</head>
<body style="margin:0;background:#fff3e8;color:#1f2937;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fff3e8;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:28px;overflow:hidden;border:1px solid #ffddbd;box-shadow:0 18px 45px rgba(255,122,0,.18);">
                    <tr>
                        <td style="background:#ff7a00;background:linear-gradient(135deg,#ff8a16 0%,#ff7a00 48%,#ff9f2f 100%);padding:30px 28px 78px;color:#ffffff;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="58" valign="middle">
                                        <div style="width:50px;height:50px;border-radius:15px;background:#ffffff;color:#ff7a00;font-size:24px;font-weight:800;line-height:50px;text-align:center;">DH</div>
                                    </td>
                                    <td valign="middle">
                                        <div style="font-size:24px;font-weight:800;line-height:1.1;">Dental Health</div>
                                        <div style="font-size:13px;line-height:1.4;opacity:.92;margin-top:3px;">Sehat Gigi, Senyum Percaya Diri</div>
                                    </td>
                                </tr>
                            </table>

                            <h1 style="margin:34px 0 10px;font-size:34px;line-height:1.1;font-weight:800;letter-spacing:-.4px;">Kode OTP<br>Reset Password</h1>
                            <p style="margin:0;max-width:360px;font-size:15px;line-height:1.55;color:#fff7ed;">
                                Gunakan kode berikut untuk mengatur ulang password admin Anda.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 28px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:-50px;background:#ffffff;border-radius:22px;border:1px solid #ffe0c2;box-shadow:0 12px 30px rgba(31,41,55,.08);">
                                <tr>
                                    <td style="padding:26px 22px;text-align:center;">
                                        <p style="margin:0 0 8px;font-size:13px;line-height:1.5;color:#6b7280;">Kode OTP Anda</p>
                                        <div style="display:inline-block;background:#fff3e8;border:1px solid #ffb46a;border-radius:16px;padding:18px 22px;margin:0 0 18px;">
                                            <span style="font-size:38px;line-height:1;letter-spacing:10px;font-weight:800;color:#ff7a00;">{{ $otp }}</span>
                                        </div>
                                        <p style="margin:0;font-size:14px;line-height:1.65;color:#4b5563;">
                                            Berlaku selama <strong>{{ $expiresInMinutes }} menit</strong>. Masukkan kode ini di endpoint reset password.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <div style="padding:24px 4px 0;">
                                <p style="margin:0 0 14px;font-size:15px;line-height:1.65;color:#374151;">Halo,</p>
                                <p style="margin:0 0 16px;font-size:15px;line-height:1.65;color:#374151;">
                                    Kami menerima permintaan reset password untuk akun admin:
                                    <strong>{{ $email }}</strong>.
                                </p>
                                <p style="margin:0 0 16px;font-size:14px;line-height:1.65;color:#6b7280;">
                                    Jangan bagikan kode ini kepada siapa pun. Tim Dental Health tidak akan pernah meminta OTP Anda.
                                </p>
                                <p style="margin:0;font-size:14px;line-height:1.65;color:#6b7280;">
                                    Jika Anda tidak meminta reset password, abaikan email ini.
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 26px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fff8f1;border-radius:18px;">
                                <tr>
                                    <td style="padding:16px 18px;color:#9a4b00;font-size:12px;line-height:1.6;text-align:center;">
                                        Email otomatis dari {{ $appName }}. Mohon tidak membalas email ini.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <p style="margin:18px 0 0;color:#c26a12;font-size:12px;line-height:1.6;">
                    Dental Health - Sehat Gigi, Senyum Percaya Diri
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
