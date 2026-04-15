<?php
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= base_url('assets/css/components/navbar_styles.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/components/realtime_notifications.css') ?>">
<nav class="navbar navbar-expand navbar-light shadow-sm mb-3 px-3 sticky-top floating-navbar glass-navbar"
    style="min-height: 48px; z-index: 1050; border-radius: 12px; top:12px;">
    <div class="container-fluid justify-content-between align-items-center">
        <span id="greetingText" class="d-none d-lg-block fw-semibold text-dark" style="font-size:1.08rem;"></span>
        <button class="btn d-lg-none p-0 border-0 bg-transparent me-2" id="sidebarToggle" type="button"
            aria-label="Toggle sidebar">
            <i class="bi bi-list" style="font-size:1.8rem;"></i>
        </button>
        <div class="d-flex align-items-center gap-3 ms-auto">
            <a href="<?= base_url('user/notifikasiuser') ?>" class="position-relative text-decoration-none me-2">
                <i class="bi bi-bell fs-5" style="color:#222;"></i>
                <?php if (isset($notif_count) && $notif_count > 0): ?>
                    <span
                        class="position-absolute badge bg-danger rounded-pill d-flex align-items-center justify-content-center"
                        style="top:2px; left:85%; min-width:16px; height:16px; font-size:10px; padding:0 4px; color:#fff; line-height:16px;">
                        <?= $notif_count; ?>
                    </span>
                <?php endif; ?>
            </a>
            <button class="btn p-0 border-0 bg-transparent shadow-none" id="toggleDarkMode" title="Dark/Light Mode"
                style="outline:none;">
                <span class="toggle-switch">
                    <i class="bi bi-moon-stars icon-moon"></i>
                    <i class="bi bi-brightness-high icon-sun"></i>
                </span>
            </button>
            <div class="dropdown">
                <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" id="dropdownUserMenu"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <?php
                        $foto_profil_user = session()->get('foto_profil');
                        $foto_path_user   = $foto_profil_user ? 'uploads/profil/' . $foto_profil_user : null;
                    ?>
                    <?php if ($foto_path_user && file_exists(FCPATH . $foto_path_user)): ?>
                        <img src="<?= base_url($foto_path_user) ?>" alt="Foto Profil"
                            class="rounded-circle" style="width:36px; height:36px; object-fit:cover;"
                            onerror="this.onerror=null; this.src='<?= base_url('assets/img/avatar_placeholder.svg') ?>';">
                    <?php else: ?>
                        <img src="<?= base_url('assets/img/avatar_placeholder.svg') ?>" alt="Foto Profil"
                            class="rounded-circle" style="width:36px; height:36px; object-fit:cover;">
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUserMenu">
                    <li><a class="dropdown-item" href="<?= base_url('user/profil_user') ?>"><i class="bi bi-person me-2"></i> Profil</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="<?= base_url('logout') ?>"><i
                                class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
<script>
    window.namaLengkap = "<?= addslashes(session()->get('nama_lengkap')) ?>";
    window.BASE_URL = "<?= base_url() ?>";
    window.CSRF_HASH = "<?= csrf_hash() ?>";
    window.CSRF_TOKEN_NAME = "<?= csrf_token() ?>";

    // Global CSRF token injection — tunggu jQuery tersedia
    (function setupCsrf() {
        function initAjaxCsrf() {
            $.ajaxSetup({
                beforeSend: function(xhr, settings) {
                    if (settings.type && settings.type.toUpperCase() === 'POST') {
                        if (settings.data instanceof FormData) {
                            settings.data.append(window.CSRF_TOKEN_NAME, window.CSRF_HASH);
                        } else if (typeof settings.data === 'string') {
                            settings.data += '&' + encodeURIComponent(window.CSRF_TOKEN_NAME) + '=' + encodeURIComponent(window.CSRF_HASH);
                        } else {
                            if (!settings.data) settings.data = {};
                            if (typeof settings.data === 'object' && !(settings.data instanceof FormData)) {
                                settings.data[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
                            }
                        }
                    }
                }
            });
        }
        if (typeof jQuery !== 'undefined') {
            initAjaxCsrf();
        } else {
            var _csrfInterval = setInterval(function() {
                if (typeof jQuery !== 'undefined') {
                    clearInterval(_csrfInterval);
                    initAjaxCsrf();
                }
            }, 50);
        }
    })();
</script>
<script src="<?= base_url('assets/js/components/navbar_user_script.js') ?>"></script>
<script src="<?= base_url('assets/js/components/realtime_notifications.js') ?>"></script>