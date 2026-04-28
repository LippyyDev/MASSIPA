<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi Login - MASSIPA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body style="margin:0;padding:0;background:#f5f7fb;font-family:'Poppins',Arial,sans-serif;color:#1f2933;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="background:#f5f7fb;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="560" style="background:#ffffff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.05);border:1px solid #e5e7eb;overflow:hidden;">

                    <!-- Logo -->
                    <tr>
                        <td style="padding:24px 24px 8px 24px;text-align:center;">
                            <img src="https://image2url.com/images/1765888884783-6cee7d44-4b71-4c6b-92c5-952557da5156.png" alt="MASSIPA" style="max-width:200px;height:auto;display:block;margin:0 auto 12px auto;">
                        </td>
                    </tr>

                    <!-- Greeting -->
                    <tr>
                        <td style="padding:0 24px 8px 24px;">
                            <h1 style="margin:0;font-size:22px;font-weight:600;color:#111827;">Halo <?= esc($recipient ?? 'Pengguna') ?>,</h1>
                        </td>
                    </tr>

                    <!-- Intro -->
                    <tr>
                        <td style="padding:0 24px 16px 24px;font-size:14px;line-height:1.6;color:#4b5563;">
                            Kami mendeteksi percobaan login ke akun MASSIPA Anda dari perangkat atau lokasi baru.
                            Gunakan kode berikut untuk menyelesaikan proses masuk.
                        </td>
                    </tr>

                    <!-- OTP Code -->
                    <tr>
                        <td style="padding:0 24px 16px 24px;text-align:center;">
                            <div style="display:inline-block;background:#f3f4f6;border:1px dashed #cbd5e1;border-radius:10px;padding:18px 24px;font-size:32px;letter-spacing:10px;font-weight:600;color:#111827;">
                                <?= esc($otp) ?>
                            </div>
                        </td>
                    </tr>

                    <!-- Expire -->
                    <tr>
                        <td style="padding:0 24px 16px 24px;font-size:14px;line-height:1.6;color:#4b5563;text-align:center;">
                            Kode ini berlaku selama <strong>5 menit</strong> sejak email ini dikirim.
                        </td>
                    </tr>

                    <!-- Warning -->
                    <tr>
                        <td style="padding:0 24px 16px 24px;font-size:12px;line-height:1.6;color:#6b7280;text-align:left;">
                            Jika Anda tidak mencoba masuk ke MASSIPA, abaikan email ini dan segera hubungi administrator.
                        </td>
                    </tr>

                    <!-- Footer copyright -->
                    <tr>
                        <td style="padding:0 24px 24px 24px;font-size:13px;color:#9ca3af;text-align:center;">
                            © <?= date('Y') ?> Pengadilan Tinggi Agama Makassar — MASSIPA
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
