<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Login MASSIPA</title>
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
                            <?php if (!empty($is_admin_report)): ?>
                                Terdapat aktivitas login baru ke sistem MASSIPA yang perlu Anda ketahui sebagai administrator.
                            <?php else: ?>
                                Akun Anda baru saja digunakan untuk masuk ke sistem MASSIPA. Jika ini bukan Anda, segera hubungi administrator.
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Detail Login -->
                    <tr>
                        <td style="padding:0 24px 16px 24px;">
                            <table cellspacing="0" cellpadding="0" border="0" width="100%" style="background:#f3f4f6;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
                                <tr>
                                    <td style="padding:12px 16px;border-bottom:1px solid #e5e7eb;">
                                        <span style="font-size:13px;font-weight:600;color:#111827;display:block;">Detail Login</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 16px;">
                                        <table cellspacing="0" cellpadding="0" border="0" width="100%">

                                            <?php if (!empty($logged_username)): ?>
                                            <tr>
                                                <td style="font-size:13px;color:#6b7280;padding:4px 0;width:40%;vertical-align:top;">Username</td>
                                                <td style="font-size:13px;color:#111827;font-weight:600;padding:4px 0;"><?= esc($logged_username) ?></td>
                                            </tr>
                                            <?php endif; ?>

                                            <?php if (!empty($logged_role)): ?>
                                            <tr>
                                                <td style="font-size:13px;color:#6b7280;padding:4px 0;vertical-align:top;">Role</td>
                                                <td style="font-size:13px;color:#111827;font-weight:600;padding:4px 0;">
                                                    <?php
                                                        echo match(strtolower($logged_role ?? '')) {
                                                            'admin' => 'Administrator',
                                                            'user'  => 'Pengguna',
                                                            default => esc($logged_role),
                                                        };
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php endif; ?>

                                            <tr>
                                                <td style="font-size:13px;color:#6b7280;padding:4px 0;vertical-align:top;">Waktu Login</td>
                                                <td style="font-size:13px;color:#111827;font-weight:600;padding:4px 0;"><?= esc($login_time ?? date('d M Y, H:i') . ' WIB') ?></td>
                                            </tr>

                                            <?php if (!empty($ip_address)): ?>
                                            <tr>
                                                <td style="font-size:13px;color:#6b7280;padding:4px 0;vertical-align:top;">Alamat IP</td>
                                                <td style="font-size:13px;color:#111827;font-weight:600;padding:4px 0;"><?= esc($ip_address) ?></td>
                                            </tr>
                                            <?php endif; ?>

                                            <?php if (!empty($device_type)): ?>
                                            <tr>
                                                <td style="font-size:13px;color:#6b7280;padding:4px 0;vertical-align:top;">Perangkat</td>
                                                <td style="font-size:13px;color:#111827;font-weight:600;padding:4px 0;"><?= esc($device_type) ?></td>
                                            </tr>
                                            <?php endif; ?>

                                            <?php if (!empty($device_os)): ?>
                                            <tr>
                                                <td style="font-size:13px;color:#6b7280;padding:4px 0;vertical-align:top;">Sistem Operasi</td>
                                                <td style="font-size:13px;color:#111827;font-weight:600;padding:4px 0;"><?= esc($device_os) ?></td>
                                            </tr>
                                            <?php endif; ?>

                                            <?php if (!empty($browser)): ?>
                                            <tr>
                                                <td style="font-size:13px;color:#6b7280;padding:4px 0;vertical-align:top;">Browser</td>
                                                <td style="font-size:13px;color:#111827;font-weight:600;padding:4px 0;"><?= esc($browser) ?></td>
                                            </tr>
                                            <?php endif; ?>

                                            <?php if (!empty($location)): ?>
                                            <tr>
                                                <td style="font-size:13px;color:#6b7280;padding:4px 0;vertical-align:top;">Lokasi</td>
                                                <td style="font-size:13px;color:#111827;font-weight:600;padding:4px 0;"><?= esc($location) ?></td>
                                            </tr>
                                            <?php endif; ?>

                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Peringatan (hanya untuk user biasa) -->
                    <?php if (empty($is_admin_report)): ?>
                    <tr>
                        <td style="padding:0 24px 16px 24px;font-size:12px;line-height:1.6;color:#6b7280;text-align:left;">
                            Jika Anda tidak mengenali aktivitas ini, segera hubungi administrator MASSIPA dan ganti password akun Anda.
                        </td>
                    </tr>
                    <?php endif; ?>

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
