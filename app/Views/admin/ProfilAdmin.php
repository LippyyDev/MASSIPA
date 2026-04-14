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
    <meta name="color-scheme" content="dark light">
    <title>Pengaturan Profil - MASSIPA</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/ProfilAdmin.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <?php include(APPPATH . 'Views/components/sidebar_admin.php'); ?>

    <?php include(APPPATH . 'Views/components/navbar_admin.php'); ?>
    <div class="main-content profile-page">
        <?php if (session()->getFlashdata('msg')): ?>
            <script>
                window.flashMsgType = <?= json_encode(session()->getFlashdata('msg_type')) ?>;
                window.flashMsg = <?= json_encode(session()->getFlashdata('msg')) ?>;
            </script>
        <?php endif; ?>

        <div class="container-fluid px-0">
            <div class="row">
                <div class="col-12">
                    <div class="card mb-3">
                        <div class="card-body text-center">
                            <form method="POST" action="<?= base_url('admin/profil/update_foto') ?>"
                                enctype="multipart/form-data" id="fotoFormAdmin">
                                <?= csrf_field() ?>
                                <div class="profile-image-wrapper">
                                    <?php
                                        $foto_admin      = $user['foto_profil'] ?? null;
                                        $foto_path_admin = $foto_admin ? 'uploads/profil/' . $foto_admin : null;
                                        $placeholder_url = base_url('assets/img/avatar_placeholder.svg');
                                        $foto_src_admin  = ($foto_path_admin && file_exists(FCPATH . $foto_path_admin))
                                            ? base_url($foto_path_admin)
                                            : $placeholder_url;
                                    ?>
                                    <img src="<?= $foto_src_admin ?>"
                                        alt="Profile Image" class="profile-image" id="profileImageAdmin"
                                        onerror="this.onerror=null; this.src='<?= $placeholder_url ?>';">
                                    <div class="profile-image-overlay">
                                        <i class="bi bi-camera-fill"></i>
                                    </div>
                                </div>
                                <input type="file" class="d-none" id="foto_profil" name="foto_profil"
                                    accept="image/jpeg,image/jpg,image/png,image/webp,image/bmp,image/svg+xml,image/tiff,image/x-icon,image/x-ico">
                            </form>
                            <h5><?= $user['nama_lengkap'] ?? '-' ?></h5>
                            <p class="text-muted"><?= $user['email'] ?? '-' ?></p>
                            <p class="text-muted">
                                <span class="badge bg-danger">Admin</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-tabs mb-4" id="profileTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab"
                                        data-bs-target="#profile" type="button" role="tab" aria-controls="profile"
                                        aria-selected="true">
                                        <i class="bi bi-person me-1"></i> Profil
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="password-tab" data-bs-toggle="tab"
                                        data-bs-target="#password" type="button" role="tab" aria-controls="password"
                                        aria-selected="false">
                                        <i class="bi bi-key me-1"></i> Password
                                    </button>
                                </li>
                            </ul>
                            <div class="tab-content" id="profileTabContent">
                                <div class="tab-pane fade show active" id="profile" role="tabpanel"
                                    aria-labelledby="profile-tab">
                                    <form method="POST" action="<?= base_url('admin/profil/update') ?>">
                                        <?= csrf_field() ?>
                                        <div class="mb-3">
                                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                            <input type="text" class="form-control" id="nama_lengkap"
                                                name="nama_lengkap" value="<?= $user["nama_lengkap"] ?>"
                                                autocomplete="name" oninput="this.value = this.value.replace(/[0-9]/g, '')" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                value="<?= $user['email'] ?? '' ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" name="username"
                                                value="<?= $user["username"] ?>" autocomplete="username" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                                        </button>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
                                    <form method="POST" action="<?= base_url('admin/profil/update_password') ?>"
                                        id="passwordForm" novalidate>
                                        <?= csrf_field() ?>
                                        <input type="text" name="username" value="<?= $user['username'] ?>"
                                            autocomplete="username" style="display:none;">
                                        <div class="mb-3">
                                            <label for="password_lama" class="form-label">Password Lama</label>
                                            <div style="position:relative;">
                                                <input type="password" class="form-control" id="password_lama"
                                                    name="password_lama" autocomplete="current-password" required
                                                    style="padding-right:2.5rem;background-image:none;">
                                                <button type="button" onclick="togglePassword('password_lama', this)"
                                                    style="position:absolute;top:8px;right:0.5rem;background:none;border:none;cursor:pointer;color:#6c757d;line-height:1;">
                                                    <i class="bi bi-eye-slash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="password_baru" class="form-label">Password Baru</label>
                                            <div style="position:relative;">
                                                <input type="password" class="form-control" id="password_baru"
                                                    name="password_baru" autocomplete="new-password" required
                                                    style="padding-right:2.5rem;background-image:none;">
                                                <button type="button" onclick="togglePassword('password_baru', this)"
                                                    style="position:absolute;top:8px;right:0.5rem;background:none;border:none;cursor:pointer;color:#6c757d;line-height:1;">
                                                    <i class="bi bi-eye-slash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="konfirmasi_password" class="form-label">Konfirmasi Password
                                                Baru</label>
                                            <div style="position:relative;">
                                                <input type="password" class="form-control" id="konfirmasi_password"
                                                    name="konfirmasi_password" autocomplete="new-password" required
                                                    style="padding-right:2.5rem;background-image:none;">
                                                <button type="button" onclick="togglePassword('konfirmasi_password', this)"
                                                    style="position:absolute;top:8px;right:0.5rem;background:none;border:none;cursor:pointer;color:#6c757d;line-height:1;">
                                                    <i class="bi bi-eye-slash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary" id="submitPassword">
                                            <i class="bi bi-key me-1"></i> Ubah Password
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="<?= base_url('assets/js/admin/ProfilAdmin.js') ?>"></script>
    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            } else {
                input.type = "password";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            }
        }
    </script>
    <?php include(APPPATH . 'Views/components/bottom_nav_admin.php'); ?>
</body>

</html>