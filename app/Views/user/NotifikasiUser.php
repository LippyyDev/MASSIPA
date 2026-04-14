<!--
    Author: MUHAMMAD ALIF QADRI 2025
    Licensed to: PTA MAKASSAR DAN SELURUH JAJARANNYA
    Copyright (c) 2025
-->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - MASSIPA</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/user/NotifikasiUser.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/realtime_notifications.css') ?>">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <meta name="color-scheme" content="dark light">
    <script>
        (function () {
            try {
                var mode = localStorage.getItem('theme-mode');
                if (
                    mode === 'dark' ||
                    (!mode && window.matchMedia('(prefers-color-scheme: dark)').matches)
                ) {
                    document.documentElement.classList.add('dark-mode');
                }
            } catch (e) { }
        })();
    </script>
</head>

<body>
    <?php include(APPPATH . 'Views/components/sidebar_user.php'); ?>

    <?php include(APPPATH . 'Views/components/navbar_user.php'); ?>

    <div class="main-content">
        <div class="container-fluid px-0">
            <?php if (session()->getFlashdata("msg")): ?>
                <div class="alert alert-<?= esc(session()->getFlashdata("msg_type")) ?> alert-dismissible fade show"
                    role="alert">
                    <?= esc(session()->getFlashdata("msg")) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($notifikasi_list)): ?>
                <button type="button" class="btn btn-danger btn-sm" id="btnHapusSemua">
                    <i class="fas fa-trash me-1"></i>Hapus Semua
                </button>
            <?php endif; ?>
            <div class="card-body p-0" style="border:none; box-shadow:none; padding:0;">
                <?php if (!empty($notifikasi_list)): ?>
                    <div class="list-group list-group-flush" id="notifikasiList">
                        <?php foreach ($notifikasi_list as $row):
                            $link = base_url("user/beranda_user");
                            $jenis = isset($row["jenis"]) ? $row["jenis"] : "sistem";
                            $id_referensi = isset($row["referensi_id"]) ? $row["referensi_id"] : null;
                    
                            if (($jenis == "status" || $jenis == "feedback" || $jenis == "laporan") && !empty($id_referensi)) {
                                $link = base_url("user/kirimlaporan") . "#file-" . $id_referensi;
                            } elseif ($jenis == "sistem") {
                                $link = base_url("user/beranda_user");
                            }

                            $created_at = new DateTime($row["created_at"]);
                            $now = new DateTime();
                            $diff = $now->diff($created_at);

                            if ($diff->days > 0) {
                                $time_display = $created_at->format('d M Y H:i');
                            } elseif ($diff->h > 0) {
                                $time_display = $diff->h . ' jam yang lalu';
                            } elseif ($diff->i > 0) {
                                $time_display = $diff->i . ' menit yang lalu';
                            } else {
                                $time_display = 'Baru saja';
                            }
                            ?>
                            <a href="<?= $link; ?>"
                                class="list-group-item list-group-item-action notification-item <?= $row["is_read"] == 0 ? "unread" : ""; ?>"
                                data-notif-id="<?= $row["id"]; ?>">
                                <?php
                                $status_class = 'status-system';
                                $status_text = 'Sistem';

                                if (isset($row["jenis"])) {
                                    switch ($row["jenis"]) {
                                        case 'laporan':
                                            $status_class = 'status-uploaded';
                                            $status_text = 'Laporan Masuk';
                                            break;
                                        case 'status':
                                            if (strpos(strtolower($row["judul"]), 'dilihat') !== false) {
                                                $status_class = 'status-viewed';
                                                $status_text = 'Dilihat';
                                            } elseif (strpos(strtolower($row["judul"]), 'diterima') !== false || strpos(strtolower($row["judul"]), 'disetujui') !== false) {
                                                $status_class = 'status-approved';
                                                $status_text = 'Diterima';
                                            } elseif (strpos(strtolower($row["judul"]), 'ditolak') !== false) {
                                                $status_class = 'status-rejected';
                                                $status_text = 'Ditolak';
                                            }
                                            break;
                                        case 'feedback':
                                            $status_class = 'status-approved';
                                            $status_text = 'Feedback';
                                            break;
                                    }
                                }
                                ?>
                                <h5 class="mb-1 notification-title">
                                    <?= esc($row["judul"]); ?>
                                </h5>
                                <small class="notification-time notification-time-top"><?= $time_display; ?></small>
                                <p class="mb-1 notification-message"><?= esc($row["pesan"]); ?></p>
                                <small class="notification-time"><?= $created_at->format('d M Y H:i:s'); ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center p-3" id="notifikasiKosong">
                        <p>Tidak ada notifikasi</p>
                        <p>Anda belum memiliki notifikasi apapun saat ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        const BASE_URL = "<?= base_url() ?>";
        const CSRF_HASH = "<?= csrf_hash() ?>";
        const CSRF_TOKEN_NAME = "<?= csrf_token() ?>";
    </script>
    <script src="<?= base_url('assets/js/user/NotifikasiUser.js') ?>"></script>
    <?php include(APPPATH . 'Views/components/bottom_nav_user.php'); ?>
</body>

</html>