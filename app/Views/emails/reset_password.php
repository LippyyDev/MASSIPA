<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password MASSIPA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body style="margin:0;padding:0;background:#f5f7fb;font-family:'Poppins',Arial,sans-serif;color:#1f2933;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="background:#f5f7fb;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="560" style="background:#ffffff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.05);border:1px solid #e5e7eb;overflow:hidden;">
                    <tr>
                        <td style="padding:24px 24px 8px 24px;text-align:center;">
                            <img src="<?= esc($logoUrl ?? '') ?>" alt="MASSIPA" style="max-width:200px;height:auto;display:block;margin:0 auto 12px auto;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 8px 24px;">
                            <h1 style="margin:0;font-size:22px;font-weight:600;color:#111827;">Halo <?= esc($recipient ?? 'Pengguna'); ?>,</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 16px 24px;font-size:14px;line-height:1.6;color:#4b5563;">
                            Kami menerima permintaan untuk mengatur ulang password akun MASSIPA Anda. Gunakan kode berikut untuk melanjutkan.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 16px 24px;text-align:center;">
                            <div style="display:inline-block;background:#f3f4f6;border:1px dashed #cbd5e1;border-radius:10px;padding:18px 24px;font-size:24px;letter-spacing:6px;font-weight:600;color:#111827;">
                                <?= esc($code) ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 16px 24px;font-size:14px;line-height:1.6;color:#4b5563;text-align:center;">
                            Kode dan tautan reset berlaku selama <strong>10 menit</strong>.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 24px 24px;text-align:center;">
                            <a href="<?= esc($resetLink) ?>" style="display:inline-block;background:#5f2eea;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:10px;font-weight:600;font-size:14px;">Buka Halaman Reset Password</a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 24px 18px 24px;font-size:12px;line-height:1.6;color:#6b7280;text-align:left;">
                            Jika Anda tidak mengenali massipa.ptamakassar@gmail.com, anda dapat mengabaikan email ini dengan aman.
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 24px 24px;font-size:13px;color:#9ca3af;text-align:center;">
                            © <?= date('Y'); ?> Pengadilan Tinggi Agama Makassar — MASSIPA
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

