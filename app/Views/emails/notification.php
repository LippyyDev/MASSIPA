<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($subject ?? 'Notifikasi MASSIPA') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body style="margin:0;padding:0;background:#f5f7fb;font-family:'Poppins',Arial,sans-serif;color:#1f2933;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" width="100%" style="background:#f5f7fb;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="560" style="background:#ffffff;border-radius:12px;box-shadow:0 8px 24px rgba(0,0,0,0.05);border:1px solid #e5e7eb;overflow:hidden;">
                    <tr>
                        <td style="padding:20px 24px 8px 24px;text-align:center;">
                            <img src="https://image2url.com/images/1765888884783-6cee7d44-4b71-4c6b-92c5-952557da5156.png" alt="MASSIPA" style="max-width:200px;height:auto;display:block;margin:0 auto 12px auto;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 8px 24px;">
                            <h1 style="margin:0;font-size:22px;font-weight:600;color:#111827;">Halo <?= esc($recipient ?? 'Pengguna'); ?>,</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 12px 24px;font-size:15px;font-weight:600;color:#111827;">
                            <?= esc($subject ?? 'Notifikasi MASSIPA') ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 20px 24px;font-size:14px;line-height:1.6;color:#4b5563;">
                            <?= nl2br(esc($message ?? '')) ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 24px 20px 24px;font-size:13px;color:#6b7280;">
                            Email ini dikirim otomatis dari MASSIPA untuk pemberitahuan akun Anda.
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

