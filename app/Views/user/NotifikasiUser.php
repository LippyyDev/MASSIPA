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

            <!-- Tombol Hapus Semua (disembunyikan dulu, akan muncul jika ada data) -->
            <button type="button" class="btn btn-danger btn-sm d-none" id="btnHapusSemua">
                <i class="fas fa-trash me-1"></i>Hapus Semua
            </button>

            <div class="card-body p-0" style="border:none; box-shadow:none; padding:0;">

                <!-- Skeleton Loader -->
                <div id="notifikasiSkeleton">
                    <?php for ($i = 0; $i < 4; $i++): ?>
                    <div class="list-group-item notification-item" style="padding:16px 20px;">
                        <div class="skeleton-line" style="height:16px; width:60%; border-radius:6px; background:var(--skeleton-bg, #e0e0e0); margin-bottom:8px; animation: skeletonPulse 1.4s ease-in-out infinite;"></div>
                        <div class="skeleton-line" style="height:12px; width:35%; border-radius:6px; background:var(--skeleton-bg, #e0e0e0); margin-bottom:8px; animation: skeletonPulse 1.4s ease-in-out infinite 0.2s;"></div>
                        <div class="skeleton-line" style="height:13px; width:85%; border-radius:6px; background:var(--skeleton-bg, #e0e0e0); animation: skeletonPulse 1.4s ease-in-out infinite 0.1s;"></div>
                    </div>
                    <?php endfor; ?>
                </div>

                <!-- Container hasil AJAX -->
                <div class="list-group list-group-flush d-none" id="notifikasiList"></div>

                <!-- Pesan kosong -->
                <div class="text-center p-3 d-none" id="notifikasiKosong">
                    <p>Tidak ada notifikasi</p>
                    <p>Anda belum memiliki notifikasi apapun saat ini.</p>
                </div>

            </div>
        </div>
    </div>

    <style>
        @keyframes skeletonPulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.4; }
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        const BASE_URL        = "<?= base_url() ?>";
        const CSRF_HASH       = "<?= csrf_hash() ?>";
        const CSRF_TOKEN_NAME = "<?= csrf_token() ?>";
    </script>
    <script src="<?= base_url('assets/js/user/NotifikasiUser.js') ?>"></script>
    <?php include(APPPATH . 'Views/components/bottom_nav_user.php'); ?>
</body>

</html>