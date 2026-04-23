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
    <title>Pengaturan - MASSIPA</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">

    <meta name="color-scheme" content="dark light">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/Pengaturan.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>

    <script src="<?= base_url('assets/js/admin/Pengaturan.js') ?>"></script>
    
    <script>
        window.flashMessage = <?= json_encode(session()->getFlashdata('msg')) ?>;
        window.flashMessageType = <?= json_encode(session()->getFlashdata('msg_type')) ?>;
    </script>
</head>

<body>
    <?php include(APPPATH . 'Views/components/sidebar_admin.php'); ?>
    <?php include(APPPATH . 'Views/components/navbar_admin.php'); ?>
    <div class="main-content">
        <div class="container-fluid px-0">
            <div class="row mb-0">
                <div class="col-12">
                    <?php
                    $isiPetunjuk = '<ul class="mb-0 ps-3" style="font-size:0.98em;">
                        <li><i class="bi bi-key"></i> Kelola API Key untuk akses API eksternal.</li>
                        <li><i class="bi bi-globe"></i> Kelola domain CORS agar API hanya bisa diakses dari domain tertentu.</li>
                        <li><i class="bi bi-toggle-on"></i> Gunakan tombol Aktif/Nonaktif untuk mengatur status API Key atau domain.</li>
                        <li><i class="bi bi-trash"></i> Gunakan tombol Hapus untuk menghapus data.</li>
                    </ul>';
                    echo view('components/petunjuk', ['isiPetunjuk' => $isiPetunjuk]);
                    ?>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between pengaturan-card-header" style="cursor: pointer; user-select: none;">
                    <span><i class="bi bi-key me-2"></i>Kelola API Key</span>
                    <i class="bi bi-chevron-up chevron-icon opacity-50" style="transition: transform 0.2s; transform: rotate(180deg);"></i>
                </div>
                <div class="card-body pengaturan-card-body" style="display: none;">
                    <form action="<?= base_url('admin/api_keys/add') ?>" method="post" class="row g-2 mb-4">
                        <?= csrf_field() ?>
                        <div class="col-md-8">
                            <input type="text" name="label" class="form-control"
                                placeholder="Label/Nama Pengguna API Key" required>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary w-100" type="submit">Generate API Key</button>
                        </div>
                    </form>
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover table-borderless align-middle modern-table" id="apiKeyTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Label</th>
                                    <th>API Key</th>
                                    <th>Status</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($keys)):
                                    $no = 1;
                                    foreach ($keys as $key): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= esc($key['label']) ?></td>
                                            <td><code><?= esc($key['api_key']) ?></code> <button
                                                    class="btn btn-outline-secondary btn-sm btn-copy-key ms-2"
                                                    data-key="<?= esc($key['api_key']) ?>" title="Salin API Key"><i
                                                        class="fas fa-copy"></i></button></td>
                                            <td>
                                                <?php if ($key['is_active']): ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Nonaktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d-m-Y H:i', strtotime($key['created_at'])) ?></td>
                                            <td>
                                                <?php if ($key['is_active']): ?>
                                                    <a href="<?= base_url('admin/api_keys/toggle/' . $key['id']) ?>"
                                                        class="btn btn-outline-danger btn-sm aksi-btn" title="Nonaktifkan"><i class="bi bi-x-circle"></i></a>
                                                <?php else: ?>
                                                    <a href="<?= base_url('admin/api_keys/toggle/' . $key['id']) ?>"
                                                        class="btn btn-outline-primary btn-sm aksi-btn" title="Aktifkan"><i class="bi bi-check-circle"></i></a>
                                                <?php endif; ?>
                                                <button class="btn btn-danger btn-sm aksi-btn btn-delete-key"
                                                    data-url="<?= base_url('admin/api_keys/delete/' . $key['id']) ?>" title="Hapus"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                        <?php if (empty($keys)): ?>
                            <div class="text-center opacity-75 my-2">Belum ada API Key</div>
                        <?php endif; ?>
                    </div>
                    <div class="d-block d-md-none">
                        <?php if (!empty($keys)):
                            $no = 1;
                            foreach ($keys as $key): ?>
                                <div class="border rounded mb-3 p-3 shadow-sm pengaturan-card-mobile" data-type="apikey">
                                    <div class="fw-bold mb-1 position-relative">No. <?= $no++ ?> - <?= esc($key['label']) ?>
                                        <?php if ($key['is_active']): ?><span
                                                class="badge bg-success position-absolute top-0 end-0">Aktif</span><?php else: ?><span
                                                class="badge bg-secondary position-absolute top-0 end-0">Nonaktif</span><?php endif; ?>
                                    </div>
                                    <div><b>API Key:</b> <code><?= esc($key['api_key']) ?></code> <button
                                            class="btn btn-outline-secondary btn-sm btn-copy-key ms-2"
                                            data-key="<?= esc($key['api_key']) ?>" title="Salin API Key"><i
                                                class="fas fa-copy"></i></button></div>
                                    <div><b>Dibuat:</b> <?= date('d-m-Y H:i', strtotime($key['created_at'])) ?></div>
                                    <div class="mt-2 d-flex gap-2 flex-wrap">
                                        <?php if ($key['is_active']): ?>
                                            <a href="<?= base_url('admin/api_keys/toggle/' . $key['id']) ?>"
                                                class="btn btn-outline-danger btn-sm aksi-btn" title="Nonaktifkan"><i class="bi bi-x-circle"></i></a>
                                        <?php else: ?>
                                            <a href="<?= base_url('admin/api_keys/toggle/' . $key['id']) ?>"
                                                class="btn btn-outline-primary btn-sm aksi-btn" title="Aktifkan"><i class="bi bi-check-circle"></i></a>
                                        <?php endif; ?>
                                        <button class="btn btn-danger btn-sm aksi-btn btn-delete-key"
                                            data-url="<?= base_url('admin/api_keys/delete/' . $key['id']) ?>" title="Hapus"><i class="fas fa-trash"></i></button>
                                    </div>
                                </div>
                            <?php endforeach; else: ?>
                            <div class="text-center">Belum ada API Key</div>
                        <?php endif; ?>
                        <div id="mobilePaginationApiKey" style="display:none;">
                            <ul class="pagination mobile-pagination-purple mb-0" style="justify-content: center; gap: 8px;">
                                <li class="page-item">
                                    <button class="page-link" id="mobilePrevApiKey" type="button">&lt;</button>
                                </li>
                                <span id="mobilePageNumbersApiKey"></span>
                                <li class="page-item">
                                    <button class="page-link" id="mobileNextApiKey" type="button">&gt;</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between pengaturan-card-header" style="cursor: pointer; user-select: none;">
                    <span><i class="bi bi-globe me-2"></i>Kelola Domain CORS (Allowed Origins)</span>
                    <i class="bi bi-chevron-up chevron-icon opacity-50" style="transition: transform 0.2s; transform: rotate(180deg);"></i>
                </div>
                <div class="card-body pengaturan-card-body" style="display: none;">
                    <form action="<?= base_url('admin/pengaturan/add_origin') ?>" method="post" class="row g-2 mb-4">
                        <?= csrf_field() ?>
                        <div class="col-md-8">
                            <input type="text" name="origin" class="form-control" placeholder="https://namadomain.com"
                                required>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary w-100" type="submit">Tambah Domain</button>
                        </div>
                    </form>
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover table-borderless align-middle modern-table" id="originTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Domain</th>
                                    <th>Status</th>
                                    <th>Ditambah</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($origins)):
                                    $no = 1;
                                    foreach ($origins as $origin): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><?= esc($origin['origin']) ?></td>
                                            <td>
                                                <?php if ($origin['is_active']): ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Nonaktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= date('d-m-Y H:i', strtotime($origin['created_at'])) ?></td>
                                            <td>
                                                <?php if ($origin['is_active']): ?>
                                                    <a href="<?= base_url('admin/pengaturan/toggle_origin/' . $origin['id']) ?>"
                                                        class="btn btn-outline-danger btn-sm aksi-btn" title="Nonaktifkan"><i class="bi bi-x-circle"></i></a>
                                                <?php else: ?>
                                                    <a href="<?= base_url('admin/pengaturan/toggle_origin/' . $origin['id']) ?>"
                                                        class="btn btn-outline-primary btn-sm aksi-btn" title="Aktifkan"><i class="bi bi-check-circle"></i></a>
                                                <?php endif; ?>
                                                <a href="<?= base_url('admin/pengaturan/delete_origin/' . $origin['id']) ?>"
                                                    class="btn btn-danger btn-sm aksi-btn btn-delete-origin" title="Hapus"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                        <?php if (empty($origins)): ?>
                            <div class="text-center opacity-75 my-2">Belum ada domain diizinkan</div>
                        <?php endif; ?>
                    </div>
                    <div class="d-block d-md-none">
                        <?php if (!empty($origins)):
                            $no = 1;
                            foreach ($origins as $origin): ?>
                                <div class="border rounded mb-3 p-3 shadow-sm pengaturan-card-mobile" data-type="origin">
                                    <div class="fw-bold mb-1 position-relative">No. <?= $no++ ?> - <?= esc($origin['origin']) ?>
                                        <?php if ($origin['is_active']): ?><span
                                                class="badge bg-success position-absolute top-0 end-0">Aktif</span><?php else: ?><span
                                                class="badge bg-secondary position-absolute top-0 end-0">Nonaktif</span><?php endif; ?>
                                    </div>
                                    <div><b>Ditambah:</b> <?= date('d-m-Y H:i', strtotime($origin['created_at'])) ?></div>
                                    <div class="mt-2 d-flex gap-2 flex-wrap">
                                        <?php if ($origin['is_active']): ?>
                                            <a href="<?= base_url('admin/pengaturan/toggle_origin/' . $origin['id']) ?>"
                                                class="btn btn-outline-danger btn-sm aksi-btn" title="Nonaktifkan"><i class="bi bi-x-circle"></i></a>
                                        <?php else: ?>
                                            <a href="<?= base_url('admin/pengaturan/toggle_origin/' . $origin['id']) ?>"
                                                class="btn btn-outline-primary btn-sm aksi-btn" title="Aktifkan"><i class="bi bi-check-circle"></i></a>
                                        <?php endif; ?>
                                        <a href="<?= base_url('admin/pengaturan/delete_origin/' . $origin['id']) ?>"
                                            class="btn btn-danger btn-sm aksi-btn btn-delete-origin" title="Hapus"><i class="fas fa-trash"></i></a>
                                    </div>
                                </div>
                            <?php endforeach; else: ?>
                            <div class="text-center">Belum ada domain diizinkan</div>
                        <?php endif; ?>
                        <div id="mobilePaginationOrigin" style="display:none;">
                            <ul class="pagination mobile-pagination-purple mb-0" style="justify-content: center; gap: 8px;">
                                <li class="page-item">
                                    <button class="page-link" id="mobilePrevOrigin" type="button">&lt;</button>
                                </li>
                                <span id="mobilePageNumbersOrigin"></span>
                                <li class="page-item">
                                    <button class="page-link" id="mobileNextOrigin" type="button">&gt;</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <!-- Card Riwayat Perangkat Login (Semua User) -->
            <div class="card mb-3" id="riwayat-perangkat">
                <div class="card-header d-flex align-items-center justify-content-between pengaturan-card-header" style="cursor: pointer; user-select: none;">
                    <span><i class="bi bi-clock-history me-2"></i>Riwayat Perangkat Login (Semua User)</span>
                    <i class="bi bi-chevron-up chevron-icon opacity-50" style="transition: transform 0.2s; transform: rotate(180deg);"></i>
                </div>
                <div class="card-body pengaturan-card-body" style="display: none;">
                    <!-- Skeleton loader -->
                    <div id="riwayatSkeleton" class="py-3 text-center opacity-75">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        Memuat riwayat...
                    </div>

                    <!-- Desktop Table -->
                    <div class="table-responsive d-none d-md-block" id="riwayatAdminTableWrap" style="display:none!important;">
                        <table class="table table-hover table-borderless align-middle modern-table" id="riwayatAdminTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>User</th>
                                    <th>Perangkat</th>
                                    <th>OS &amp; Browser</th>
                                    <th>IP Address</th>
                                    <th>Lokasi</th>
                                    <th>Waktu Login</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="d-block d-md-none" id="riwayatAdminMobileWrap" style="display:none!important;">
                        <div id="riwayatAdminMobileCards"></div>
                    </div>

                    <!-- Empty state -->
                    <div id="riwayatAdminEmpty" class="text-center opacity-75 py-4" style="display:none;">
                        <i class="bi bi-clock-history fs-2 mb-2 d-block text-primary opacity-50"></i>
                        Belum ada riwayat login tercatat.
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════ -->
            <!-- Card Pengaturan 2FA                                        -->
            <!-- ══════════════════════════════════════════════════════════ -->
            <div class="card mb-3" id="pengaturan-2fa">
                <div class="card-header d-flex align-items-center justify-content-between pengaturan-card-header" style="cursor: pointer; user-select: none;">
                    <span><i class="bi bi-shield-lock me-2"></i>Pengaturan Two-Factor Authentication (2FA)</span>
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($is2FaEnabled): ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Nonaktif</span>
                        <?php endif; ?>
                        <i class="bi bi-chevron-up chevron-icon opacity-50" style="transition: transform 0.2s; transform: rotate(180deg);"></i>
                    </div>
                </div>
                <div class="card-body pengaturan-card-body" style="display: none;">
                    <p class="opacity-75 small mb-3">
                        Jika 2FA diaktifkan, setiap user yang login dari perangkat baru (IP berbeda) akan diminta kode OTP yang dikirim ke Gmail terdaftar.
                        Perangkat yang sudah terverifikasi akan masuk whitelist selama <strong>7 hari</strong>.
                    </p>

                    <!-- Toggle 2FA Global -->
                    <div class="d-flex align-items-center justify-content-between p-3 border rounded shadow-sm mb-4 theme-card-inner">
                        <div class="d-flex flex-column">
                            <strong class="mb-1">Status 2FA Global</strong>
                            <span class="opacity-75" style="font-size:0.85em;">
                                <?php if ($is2FaEnabled): ?>
                                    Aktif (Semua user wajib OTP untuk perangkat baru)
                                <?php else: ?>
                                    Nonaktif (User dapat login tanpa OTP)
                                <?php endif; ?>
                            </span>
                        </div>
                        <form action="<?= base_url('admin/pengaturan/toggle_2fa') ?>" method="POST" id="formToggle2FaGlobal" class="m-0">
                            <?= csrf_field() ?>
                            <div class="form-check form-switch ms-3" style="font-size: 1.25rem; margin-bottom: 0;">
                                <input class="form-check-input btn-toggle-2fa-global" type="checkbox" id="btnToggle2FaGlobalSwitch" style="cursor: pointer;" <?= $is2FaEnabled ? 'checked' : '' ?> data-status="<?= $is2FaEnabled ? '1' : '0' ?>">
                            </div>
                        </form>
                    </div>

                    <!-- Tabel Exempt per User -->
                    <h6 class="fw-semibold mt-2 mb-2">Kelola Pengecualian 2FA per User</h6>
                    <p class="opacity-75 small mb-2">User yang dikecualikan tidak akan diminta OTP meskipun 2FA global aktif.</p>

                    <!-- Banner saat 2FA Global OFF -->
                    <div id="exemptDisabledBanner" class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-3 <?= $is2FaEnabled ? 'd-none' : '' ?>" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>2FA Global sedang <strong>nonaktif</strong>. Pengaturan di bawah tidak berpengaruh hingga 2FA Global diaktifkan.</span>
                    </div>

                    <div id="exemptSectionWrapper" class="position-relative">
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover table-borderless align-middle modern-table" id="exemptUserTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Lengkap</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Status 2FA</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users)): $no = 1; foreach ($users as $u): ?>
                                    <tr id="exempt-row-<?= $u['id'] ?>">
                                        <td><?= $no++ ?></td>
                                        <td><?= esc($u['nama_lengkap'] ?? '-') ?></td>
                                        <td><?= esc($u['username']) ?></td>
                                        <td>
                                            <?php if ($u['role'] === 'admin'): ?>
                                                <span class="badge bg-danger">Admin</span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">User</span>
                                            <?php endif; ?>
                                        </td>
                                        <td id="exempt-badge-<?= $u['id'] ?>">
                                            <?php $exempt = $exemptMap[$u['id']] ?? false; ?>
                                            <?php if ($exempt): ?>
                                                <span class="badge bg-warning text-dark">Dikecualikan</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Wajib OTP</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch m-0 d-inline-block" style="font-size: 1.15rem;">
                                                <input class="form-check-input btn-toggle-exempt-switch" type="checkbox" data-userid="<?= $u['id'] ?>" data-url="<?= base_url('admin/pengaturan/toggle_2fa_exempt/' . $u['id']) ?>" style="cursor: pointer;" <?= !$exempt ? 'checked' : '' ?>>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile view Exempt Table -->
                    <div class="d-block d-md-none mb-4">
                        <?php if (!empty($users)): $no = 1; foreach ($users as $u): ?>
                            <?php $exempt = $exemptMap[$u['id']] ?? false; ?>
                            <div class="border rounded mb-3 p-3 shadow-sm pengaturan-card-mobile" id="exempt-row-mobile-<?= $u['id'] ?>">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold fs-6">No. <?= $no++ ?> - <?= esc($u['nama_lengkap'] ?? '-') ?></span>
                                    <span id="exempt-badge-mobile-<?= $u['id'] ?>">
                                        <?php if ($exempt): ?>
                                            <span class="badge bg-warning text-dark">Dikecualikan</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Wajib OTP</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div style="font-size: 0.95em;" class="mb-1"><b>Username:</b> <?= esc($u['username']) ?></div>
                                <div style="font-size: 0.95em;" class="mb-2"><b>Role:</b> 
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="badge bg-danger">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-primary">User</span>
                                    <?php endif; ?>
                                </div>
                                <hr class="my-2 opacity-50">
                                <div class="d-flex justify-content-end align-items-center mt-2">
                                    <span class="opacity-75 small me-2">Status Wajib OTP:</span>
                                    <div class="form-check form-switch m-0" style="font-size: 1.15rem;">
                                        <input class="form-check-input btn-toggle-exempt-switch" type="checkbox" data-userid="<?= $u['id'] ?>" data-url="<?= base_url('admin/pengaturan/toggle_2fa_exempt/' . $u['id']) ?>" style="cursor: pointer;" <?= !$exempt ? 'checked' : '' ?>>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; else: ?>
                             <div class="text-center opacity-75">Belum ada user</div>
                        <?php endif; ?>
                    </div>
                    </div> <!-- /exemptSectionWrapper -->
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════ -->
            <!-- Card Whitelist Perangkat 2FA                               -->
            <!-- ══════════════════════════════════════════════════════════ -->
            <div class="card mb-3" id="whitelist-2fa">
                <div class="card-header d-flex align-items-center justify-content-between pengaturan-card-header" style="cursor: pointer; user-select: none;">
                    <span><i class="bi bi-shield-check me-2"></i>Perangkat Tepercaya (Whitelist IP)</span>
                    <i class="bi bi-chevron-up chevron-icon opacity-50" style="transition: transform 0.2s; transform: rotate(180deg);"></i>
                </div>
                <div class="card-body pengaturan-card-body" style="display: none;">
                    <p class="opacity-75 small mb-3">Daftar IP yang sudah terverifikasi OTP. Hapus untuk memaksa user verifikasi ulang.</p>
                    <div id="whitelistSkeleton" class="py-2 opacity-75 text-center">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Memuat...
                    </div>
                    <div class="table-responsive d-none d-md-block" id="whitelistTableWrap" style="display:none!important;">
                        <table class="table table-hover table-borderless align-middle modern-table" id="whitelistAdminTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>User</th>
                                    <th>IP Address</th>
                                    <th>Ditambahkan</th>
                                    <th>Berlaku Hingga</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="whitelistAdminBody"></tbody>
                        </table>
                    </div>
                    
                    <!-- Mobile view Whitelist -->
                    <div class="d-block d-md-none" id="whitelistAdminMobileWrap" style="display:none!important;">
                        <div id="whitelistAdminMobileCards"></div>
                        <div id="whitelistAdminMobilePagination" class="mt-3" style="display:none;">
                            <ul class="pagination mobile-pagination-purple border-0 mb-0" style="justify-content: center; gap: 8px;">
                                <li class="page-item"><button class="page-link" id="wlAdminMobilePrev" type="button">&lt;</button></li>
                                <span id="wlAdminMobilePageNumbers"></span>
                                <li class="page-item"><button class="page-link" id="wlAdminMobileNext" type="button">&gt;</button></li>
                            </ul>
                        </div>
                    </div>
                    <div id="whitelistAdminEmpty" class="text-center opacity-75 py-3" style="display:none;">
                        <i class="bi bi-shield fs-2 d-block mb-1 text-success opacity-50"></i>
                        Belum ada perangkat tepercaya.
                    </div>
                </div>
            </div>

        </div>
    </div>


    <?php include(APPPATH . 'Views/components/bottom_nav_admin.php'); ?>

    <script>
    $(document).ready(function () {
        const deleteBaseUrl = "<?= base_url('admin/pengaturan/riwayat/delete/') ?>";
        const riwayatAjaxUrl = "<?= base_url('admin/pengaturan/riwayat_ajax') ?>";
        const csrfToken = "<?= csrf_token() ?>";
        const csrfHash  = "<?= csrf_hash() ?>";

        // ── Render helpers ────────────────────────────────────────────────
        function deviceIcon(type) {
            if (type === 'Mobile') return '<i class="bi bi-phone-fill text-primary me-1"></i>';
            if (type === 'Tablet') return '<i class="bi bi-tablet-fill text-success me-1"></i>';
            return '<i class="bi bi-laptop-fill text-secondary me-1"></i>';
        }

        function renderLokasi(row) {
            const c = row.location_country || '', r = row.location_region || '', ci = row.location_city || '';
            if (c === 'Jaringan Lokal') return '<span class="badge bg-secondary">Jaringan Lokal</span>';
            const parts = [ci, r, c].filter(Boolean);
            if (parts.length) return parts.join(', ');
            return '<span class="opacity-75">Tidak tersedia</span>';
        }

        function formatDate(d) {
            if (!d) return '-';
            const dt = new Date(d.replace(' ', 'T'));
            const pad = n => String(n).padStart(2, '0');
            return pad(dt.getDate()) + '/' + pad(dt.getMonth()+1) + '/' + dt.getFullYear()
                 + ' ' + pad(dt.getHours()) + ':' + pad(dt.getMinutes());
        }

        // ── Load riwayat via AJAX POST ────────────────────────────────────
        $.ajax({
            url: riwayatAjaxUrl,
            type: 'POST',
            data: { [csrfToken]: csrfHash },
            dataType: 'json',
            success: function(res) {
                $('#riwayatSkeleton').hide();
                const rows = res.data || [];

                if (rows.length === 0) {
                    $('#riwayatAdminEmpty').show();
                    return;
                }

                // ── Desktop DataTable ─────────────────────────────────
                const tbody = $('#riwayatAdminTable tbody');
                $.each(rows, function(i, row) {
                    const deleteUrl = deleteBaseUrl + row.id;
                    tbody.append(
                        '<tr>' +
                        '<td>' + (i+1) + '</td>' +
                        '<td>' +
                            '<div class="fw-semibold" style="font-size:.9em;">' + (row.nama_lengkap || row.username || '-') + '</div>' +
                            '<div class="opacity-75" style="font-size:.8em;">' + (row.username || '') + '</div>' +
                        '</td>' +
                        '<td>' + deviceIcon(row.device_type) + (row.device_type || 'Desktop') + '</td>' +
                        '<td>' +
                            '<div class="fw-semibold" style="font-size:.88em;">' + (row.device_os || '-') + '</div>' +
                            '<div class="opacity-75" style="font-size:.8em;">' + (row.browser || '-') + '</div>' +
                        '</td>' +
                        '<td><code>' + (row.ip_address || '-') + '</code></td>' +
                        '<td style="font-size:.88em;">' + renderLokasi(row) + '</td>' +
                        '<td style="white-space:nowrap;font-size:.88em;">' + formatDate(row.created_at) + '</td>' +
                        '<td><a href="' + deleteUrl + '" class="btn btn-danger btn-sm btn-delete-riwayat aksi-btn" title="Hapus"><i class="fas fa-trash"></i></a></td>' +
                        '</tr>'
                    );
                });

                // Hapus inline style display:none, biarkan class d-none d-md-block bekerja
                $('#riwayatAdminTableWrap').attr('style', '');
                $('#riwayatAdminTable').DataTable({
                    language: {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        zeroRecords: 'Data tidak ditemukan',
                        paginate: { previous: '&lsaquo;', next: '&rsaquo;' }
                    },
                    info: false,       // Sembunyikan "Menampilkan X - Y dari Z data"
                    pageLength: 10,
                    order: [[6, 'desc']],
                    columnDefs: [{ orderable: false, targets: [0, 7] }]
                });

                // ── Mobile Cards ──────────────────────────────────────
                const mc = $('#riwayatAdminMobileCards');
                $.each(rows, function(i, row) {
                    const deleteUrl = deleteBaseUrl + row.id;
                    mc.append(
                        '<div class="border rounded mb-3 p-3 shadow-sm pengaturan-card-mobile">' +
                            '<div class="fw-bold mb-1 fs-6">' + (row.nama_lengkap || row.username || '-') + '</div>' +
                            '<div class="mb-3 opacity-75" style="font-size:0.95em;"><i class="bi bi-person me-1"></i>Akun: ' + (row.username||'-') + '</div>' +
                            
                            '<div class="mb-2" style="font-size:0.95em;">' + deviceIcon(row.device_type) + (row.device_os||'-') + ' — ' + (row.browser||'-') + '</div>' +
                            '<div class="mb-1" style="font-size:0.95em;"><b>IP:</b> <code>' + (row.ip_address||'-') + '</code></div>' +
                            '<div class="mb-2" style="font-size:0.95em;"><b>Lokasi:</b> ' + renderLokasi(row) + '</div>' +
                            
                            '<hr class="my-2 opacity-50">' +
                            
                            '<div class="d-flex justify-content-between align-items-center mt-2">' +
                                '<div class="opacity-75" style="font-size:0.85em;"><i class="bi bi-clock me-1"></i>' + formatDate(row.created_at) + '</div>' +
                                '<a href="' + deleteUrl + '" class="btn btn-danger btn-sm btn-delete-riwayat aksi-btn px-3"><i class="fas fa-trash"></i> Hapus</a>' +
                            '</div>' +
                        '</div>'
                    );
                });
                // Hapus inline style display:none, biarkan class d-block d-md-none bekerja
                $('#riwayatAdminMobileWrap').attr('style', '');
            },
            error: function() {
                $('#riwayatSkeleton').hide();
                $('#riwayatAdminEmpty').text('Terjadi kesalahan saat memuat data.').show();
            }
        });

        // ── Konfirmasi hapus riwayat ──────────────────────────────────────
        $(document).on('click', '.btn-delete-riwayat', function (e) {
            e.preventDefault();
            const url = $(this).attr('href');
            Swal.fire({
                title: 'Hapus Riwayat Ini?',
                text: 'Data riwayat login ini akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) window.location.href = url;
            });
        });

        // ── Load Whitelist 2FA via AJAX ───────────────────────────────────
        const whitelistAjaxUrl  = "<?= base_url('admin/pengaturan/whitelist_2fa_ajax') ?>";
        const revokeBaseUrl     = "<?= base_url('admin/pengaturan/whitelist_2fa/revoke/') ?>";

        let adminWhitelistData = [];
        let wlAdminMobilePage = 1;
        const wlAdminMobileLength = 10;

        function renderAdminWhitelistMobileCards() {
            var start = (wlAdminMobilePage - 1) * wlAdminMobileLength;
            var end = start + wlAdminMobileLength;
            var pageData = adminWhitelistData.slice(start, end);

            var mc = $('#whitelistAdminMobileCards');
            mc.empty();

            if (adminWhitelistData.length === 0) return;

            var now = new Date();
            $.each(pageData, function(i, r) {
                var expires  = new Date(r.expires_at.replace(' ', 'T'));
                var isActive = expires > now;
                var badge    = isActive
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-secondary">Expired</span>';

                mc.append(
                    '<div class="border rounded mb-3 p-3 shadow-sm pengaturan-card-mobile">' +
                        '<div class="d-flex justify-content-between align-items-center mb-2">' +
                            '<span class="fw-bold fs-6">No. ' + (start + i + 1) + ' - ' + (r.nama_lengkap || r.username || '-') + '</span>' +
                            badge +
                        '</div>' +
                        '<div style="font-size: 0.95em;" class="mb-1"><span class="opacity-75">Username:</span> ' + (r.username || '') + '</div>' +
                        '<div style="font-size: 0.95em;" class="mb-1"><b>IP:</b> <code>' + r.ip_address + '</code></div>' +
                        '<div style="font-size: 0.95em;" class="mb-1"><b>Ditambahkan:</b> <span class="opacity-75">' + formatDate(r.created_at) + '</span></div>' +
                        '<div style="font-size: 0.95em;" class="mb-1"><b>Berlaku:</b> <span class="opacity-75">' + formatDate(r.expires_at) + '</span></div>' +
                        '<hr class="my-2 opacity-50">' +
                        '<div class="text-end mt-2">' +
                            '<a href="' + revokeBaseUrl + r.id + '" class="btn btn-outline-danger btn-sm btn-revoke-whitelist aksi-btn" title="Hapus"><i class="fas fa-trash me-1"></i>Hapus</a>' +
                        '</div>' +
                    '</div>'
                );
            });

            var totalPages = Math.ceil(adminWhitelistData.length / wlAdminMobileLength);
            if (totalPages <= 1) {
                $('#whitelistAdminMobilePagination').hide();
            } else {
                $('#whitelistAdminMobilePagination').show();
                var pageNumbersHtml = "";
                var startPage = Math.max(1, wlAdminMobilePage - 1);
                var endPage = Math.min(totalPages, startPage + 2);
                if (endPage - startPage < 2) startPage = Math.max(1, endPage - 2);
                
                for (var j = startPage; j <= endPage; j++) {
                    var act = (j === wlAdminMobilePage) ? "active" : "";
                    pageNumbersHtml += '<li class="page-item"><button class="page-link wl-admin-page-number ' + act + '" data-page="' + j + '">' + j + '</button></li>';
                }
                $('#wlAdminMobilePageNumbers').html(pageNumbersHtml);
                $('#wlAdminMobilePrev').prop('disabled', wlAdminMobilePage === 1);
                $('#wlAdminMobileNext').prop('disabled', wlAdminMobilePage === totalPages);

                $('.wl-admin-page-number').off('click').on('click', function() {
                    wlAdminMobilePage = parseInt($(this).data('page'));
                    renderAdminWhitelistMobileCards();
                });
            }
        }

        $('#wlAdminMobilePrev').on('click', function() {
            if (wlAdminMobilePage > 1) { wlAdminMobilePage--; renderAdminWhitelistMobileCards(); }
        });
        $('#wlAdminMobileNext').on('click', function() {
            if (wlAdminMobilePage < Math.ceil(adminWhitelistData.length / wlAdminMobileLength)) { wlAdminMobilePage++; renderAdminWhitelistMobileCards(); }
        });

        $.ajax({
            url: whitelistAjaxUrl,
            type: 'POST',
            data: { [csrfToken]: csrfHash },
            dataType: 'json',
            success: function(res) {
                $('#whitelistSkeleton').hide();
                const rows = res.data || [];
                adminWhitelistData = rows;

                if (rows.length === 0) {
                    $('#whitelistAdminEmpty').show();
                    return;
                }
                
                const tbody = $('#whitelistAdminBody');
                const now   = new Date();
                $.each(rows, function(i, r) {
                    const expires  = new Date(r.expires_at.replace(' ', 'T'));
                    const isActive = expires > now;
                    const badge    = isActive
                        ? '<span class="badge bg-success">Aktif</span>'
                        : '<span class="badge bg-secondary">Expired</span>';
                    tbody.append(
                        '<tr>' +
                        '<td>' + (i+1) + '</td>' +
                        '<td><div class="fw-semibold" style="font-size:.9em;">' + (r.nama_lengkap || r.username || '-') + '</div>' +
                            '<div class="opacity-75" style="font-size:.8em;">' + (r.username || '') + '</div></td>' +
                        '<td><code>' + r.ip_address + '</code></td>' +
                        '<td style="font-size:.88em;">' + formatDate(r.created_at) + '</td>' +
                        '<td style="font-size:.88em;">' + formatDate(r.expires_at) + '</td>' +
                        '<td>' + badge + '</td>' +
                        '<td><a href="' + revokeBaseUrl + r.id + '" class="btn btn-danger btn-sm btn-revoke-whitelist aksi-btn" title="Hapus"><i class="fas fa-trash"></i></a></td>' +
                        '</tr>'
                    );
                });
                
                $('#whitelistTableWrap').attr('style', '');
                $('#whitelistAdminMobileWrap').attr('style', '');

                $('#whitelistAdminTable').DataTable({
                    language: {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        zeroRecords: 'Data tidak ditemukan',
                        paginate: { previous: '&lsaquo;', next: '&rsaquo;' }
                    },
                    info: false,
                    pageLength: 10,
                    order: [],
                    columnDefs: [{ orderable: false, targets: [0, 6] }]
                });

                renderAdminWhitelistMobileCards();
            },
            error: function() {
                $('#whitelistSkeleton').hide();
                $('#whitelistAdminEmpty').text('Gagal memuat data.').show();
            }
        });

        // Konfirmasi hapus whitelist
        $(document).on('click', '.btn-revoke-whitelist', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            Swal.fire({
                title: 'Hapus Perangkat Tepercaya?',
                text: 'User akan diminta OTP saat login ulang dari perangkat ini.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
            }).then(result => { if (result.isConfirmed) window.location.href = url; });
        });

        // ── Toggle exempt 2FA user via AJAX ──────────────────────────────
        $(document).on('change', '.btn-toggle-exempt-switch', function() {
            const url    = $(this).data('url');
            const userId = $(this).data('userid');
            const btn    = $(this);
            btn.prop('disabled', true);

            $.ajax({
                url: url,
                type: 'POST',
                data: { [csrfToken]: csrfHash },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        const badge = res.exempt
                            ? '<span class="badge bg-warning text-dark">Dikecualikan</span>'
                            : '<span class="badge bg-success">Wajib OTP</span>';
                        $('#exempt-badge-' + userId).html(badge);
                        $('[id="exempt-badge-mobile-' + userId + '"]').html(badge);
                        Swal.fire({ icon: 'success', text: res.message, timer: 1500, showConfirmButton: false });
                        
                        // Sync checkboxes if multiple exist for same user
                        $('.btn-toggle-exempt-switch[data-userid="'+userId+'"]').prop('checked', !res.exempt);
                    }
                },
                complete: function() { btn.prop('disabled', false); }
            });
        });

        // ── Card Collapse Toggle ─────────────────────────────────────────
        $(document).on('click', '.pengaturan-card-header', function(e) {
            if ($(e.target).closest('.btn, .btn-close, input, a, form, select').length) return;
            var card = $(this).closest('.card');
            var body = card.find('.pengaturan-card-body');
            var chevron = $(this).find('.chevron-icon');
            if (body.is(':visible')) {
                body.stop(true, true).slideUp(180);
                chevron.css('transform', 'rotate(180deg)');
            } else {
                body.stop(true, true).slideDown(180);
                chevron.css('transform', 'rotate(0deg)');
            }
        });

        // ── SweetAlert untuk 2FA Global ──────────────────────────────────
        function apply2FaGlobalState(isActive) {
            const exempt = $('.btn-toggle-exempt-switch');
            const wrapper = $('#exemptSectionWrapper');
            const banner  = $('#exemptDisabledBanner');

            if (isActive) {
                // Restore actual exempt values from data attrs & enable
                exempt.each(function() {
                    const realVal = $(this).data('realChecked');
                    if (typeof realVal !== 'undefined') {
                        $(this).prop('checked', realVal);
                    }
                    $(this).prop('disabled', false).css('opacity', '').css('cursor', 'pointer');
                });
                wrapper.css('opacity', '').css('pointer-events', '');
                banner.addClass('d-none');
            } else {
                // Save actual values then force all OFF + disable
                exempt.each(function() {
                    if (typeof $(this).data('realChecked') === 'undefined') {
                        $(this).data('realChecked', $(this).prop('checked'));
                    }
                    $(this).prop('checked', false).prop('disabled', true);
                });
                wrapper.css('opacity', '0.45').css('pointer-events', 'none');
                banner.removeClass('d-none');
            }
        }

        // Run on page load
        apply2FaGlobalState(<?= $is2FaEnabled ? 'true' : 'false' ?>);

        $(document).on('change', '.btn-toggle-2fa-global', function(e) {
            e.preventDefault();
            const cb = $(this);
            const isEnabled = cb.data('status') === '1';
            const title = isEnabled ? 'Nonaktifkan 2FA Global?' : 'Aktifkan 2FA Global?';
            const text = isEnabled ? 'Semua user dapat login tanpa OTP.' : 'Semua user akan diminta OTP saat login dari perangkat baru.';
            
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#formToggle2FaGlobal').submit();
                } else {
                    cb.prop('checked', cb.prop('defaultChecked')); // revert state
                }
            });
        });
    });
    </script>
    <style>
        .theme-card-inner {
            background-color: var(--bs-light, #f8f9fa);
        }
        [data-bs-theme="dark"] .theme-card-inner, .dark-mode .theme-card-inner {
            background-color: rgba(255, 255, 255, 0.05); /* Slight highlight in dark mode */
            border-color: rgba(255, 255, 255, 0.1) !important;
        }
    </style>

</body>
</html>
