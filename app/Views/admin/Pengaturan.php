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
                <div class="card-header">Kelola API Key</div>
                <div class="card-body">
                    <form action="<?= base_url('admin/api_keys/add') ?>" method="post" class="row g-2 mb-4">
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
                            <div class="text-center text-muted my-2">Belum ada API Key</div>
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
                <div class="card-header">Kelola Domain CORS (Allowed Origins)</div>
                <div class="card-body">
                    <form action="<?= base_url('admin/pengaturan/add_origin') ?>" method="post" class="row g-2 mb-4">
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
                            <div class="text-center text-muted my-2">Belum ada domain diizinkan</div>
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
                <div class="card-header d-flex align-items-center">
                    <i class="bi bi-clock-history me-2"></i>Riwayat Perangkat Login (Semua User)
                </div>
                <div class="card-body">
                    <!-- Skeleton loader -->
                    <div id="riwayatSkeleton" class="py-3 text-center text-muted">
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
                    <div id="riwayatAdminEmpty" class="text-center text-muted py-4" style="display:none;">
                        <i class="bi bi-clock-history fs-2 mb-2 d-block text-primary opacity-50"></i>
                        Belum ada riwayat login tercatat.
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
            return '<span class="text-muted">Tidak tersedia</span>';
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
                            '<div class="text-muted" style="font-size:.8em;">' + (row.username || '') + '</div>' +
                        '</td>' +
                        '<td>' + deviceIcon(row.device_type) + (row.device_type || 'Desktop') + '</td>' +
                        '<td>' +
                            '<div class="fw-semibold" style="font-size:.88em;">' + (row.device_os || '-') + '</div>' +
                            '<div class="text-muted" style="font-size:.8em;">' + (row.browser || '-') + '</div>' +
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
    });
    </script>
</body>
</html>
