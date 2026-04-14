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

    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/NotifikasiAdmin.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/realtime_notifications.css') ?>">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <meta name="color-scheme" content="dark light">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script src="<?= base_url("assets/js/admin/NotifikasiAdmin.js") ?>"></script>
    <script>
        window.baseUrl = '<?= base_url() ?>';
        window.csrfToken = '<?= csrf_token() ?>';
        window.csrfHash = '<?= csrf_hash() ?>';
        window.BASE_URL = '<?= base_url() ?>';
        window.CSRF_HASH = '<?= csrf_hash() ?>';
    </script>
</head>

<body>

    <?php include(APPPATH . 'Views/components/sidebar_admin.php'); ?>

    <?php include(APPPATH . 'Views/components/navbar_admin.php'); ?>

    <div class="main-content" id="mainContent">
        <div class="overlay"></div>

        <div class="container-fluid px-0">
            <?php if (session()->getFlashdata("msg")): ?>
                <div class="alert alert-<?= esc(session()->getFlashdata("msg_type")) ?> alert-dismissible fade show"
                    role="alert">
                    <?= esc(session()->getFlashdata("msg")) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($notifikasi)): ?>
                <button type="button" class="btn btn-danger btn-sm" id="btnHapusSemua">
                    <i class="fas fa-trash me-1"></i>Hapus Semua
                </button>
            <?php endif; ?>
            <div class="card">
                <div class="card-body p-0" style="border:none; box-shadow:none; padding:0;">
                    <?php if (!empty($notifikasi)): ?>
                        <div class="list-group list-group-flush" id="notifikasiList">
                            <?php foreach ($notifikasi as $row):
                                $link = base_url("admin/dashboard");
                                
                                if ($row["jenis"] == "laporan" && !empty($row["referensi_id"])) {
                                    $judul_lower = strtolower($row["judul"]);
                                    if (strpos($judul_lower, 'pengajuan hukuman') !== false) {
                                        $link = base_url("admin/kelola_hukuman_disiplin?highlight=" . $row["referensi_id"]);
                                    } else {
                                        $link = base_url("admin/kelola_laporan?highlight=" . $row["referensi_id"]);
                                    }
                                } elseif ($row["jenis"] == "status" && !empty($row["referensi_id"])) {
                                    $judul_lower = strtolower($row["judul"]);
                                    if (strpos($judul_lower, 'laporan') !== false) {
                                        $link = base_url("admin/kelola_laporan?highlight=" . $row["referensi_id"]);
                                    } else {
                                        $link = base_url("admin/kelola_hukuman_disiplin?highlight=" . $row["referensi_id"]);
                                    }
                                } elseif ($row["jenis"] == "feedback" && !empty($row["referensi_id"])) {
                                    $link = base_url("admin/kelola_laporan?highlight=" . $row["referensi_id"]);
                                } elseif ($row["jenis"] == "sistem") {
                                    $link = base_url("admin/dashboard");
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
                                $status_class = 'status-system';
                                $status_text = 'Sistem';
                                if (isset($row["jenis"])) {
                                    if ($row["jenis"] == 'status') {
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
                                    } elseif ($row["jenis"] == 'laporan') {
                                        $judul_lower = strtolower($row["judul"]);
                                        if (strpos($judul_lower, 'pengajuan hukuman') !== false) {
                                            $status_class = 'status-hukuman';
                                            $status_text = 'Laporan Hukuman Masuk';
                                        } else {
                                            $status_class = 'status-uploaded';
                                            $status_text = 'Laporan Kedisiplinan Masuk';
                                        }
                                    }
                                }
                                ?>
                                <a href="<?= $link; ?>"
                                    class="list-group-item list-group-item-action notification-item <?= $row["is_read"] == 0 ? "unread" : ""; ?>"
                                    data-notif-id="<?= $row["id"] ?>">
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
    </div>

    <?php include(APPPATH . 'Views/components/bottom_nav_admin.php'); ?>
</body>

</html>