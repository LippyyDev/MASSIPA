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
    <link rel="stylesheet" href="<?= base_url('assets/css/user/Pengaturan.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        window.flashMessage        = <?= json_encode(session()->getFlashdata('msg')) ?>;
        window.flashMessageType    = <?= json_encode(session()->getFlashdata('msg_type')) ?>;
        window.riwayatAjaxUrl      = "<?= base_url('user/pengaturan/riwayat_ajax') ?>";
        window.hapusRiwayatUrl     = "<?= base_url('user/pengaturan/hapus_riwayat') ?>";
        window.toggle2FaExemptUrl  = "<?= base_url('user/pengaturan/toggle_2fa_exempt') ?>";
        window.revokeWhitelistBase = "<?= base_url('user/pengaturan/whitelist/revoke/') ?>";
        window.csrfToken           = "<?= csrf_token() ?>";
        window.csrfHash            = "<?= csrf_hash() ?>";
    </script>
</head>

<body>
    <?php include(APPPATH . 'Views/components/sidebar_user.php'); ?>
    <?php include(APPPATH . 'Views/components/navbar_user.php'); ?>
    <div class="main-content">
        <div class="container-fluid px-0">
            <div class="row mb-0">
                <div class="col-12">
                    <?php
                    $isiPetunjuk = '<ul class="mb-0 ps-3" style="font-size:0.98em;">
                        <li><i class="bi bi-shield-lock"></i> Halaman ini menampilkan riwayat perangkat yang digunakan untuk login ke akun Anda.</li>
                        <li><i class="bi bi-laptop"></i> Informasi mencakup: perangkat, sistem operasi, browser, alamat IP, dan lokasi login.</li>
                        <li><i class="bi bi-clock-history"></i> Riwayat disimpan selama 90 hari terakhir. Login dari IP yang sama hanya memperbarui waktu login.</li>
                        <li><i class="bi bi-trash"></i> Anda dapat menghapus seluruh riwayat login Anda kapan saja.</li>
                    </ul>';
                    echo view('components/petunjuk', ['isiPetunjuk' => $isiPetunjuk]);
                    ?>
                </div>
            </div>

            <!-- Card Riwayat Perangkat Login -->
            <div class="card mb-3" id="riwayat-perangkat">
                <div class="card-header d-flex align-items-center justify-content-between pengaturan-card-header" style="cursor: pointer; user-select: none;">
                    <span><i class="bi bi-clock-history me-2"></i>Riwayat Perangkat Login</span>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-danger btn-sm d-none" id="btnHapusSemuaRiwayat" onclick="event.stopPropagation();">
                            <i class="bi bi-trash me-1"></i> Hapus Semua
                        </button>
                        <i class="bi bi-chevron-up chevron-icon opacity-75" style="transition: transform 0.2s; transform: rotate(180deg);"></i>
                    </div>
                </div>
                <div class="card-body pengaturan-card-body" style="display: none;">
                    <!-- Skeleton loader -->
                    <div id="riwayatSkeleton" class="py-3 text-center opacity-75">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        Memuat riwayat...
                    </div>

                    <!-- Desktop Table (hidden until data loaded) -->
                    <div class="table-responsive d-none d-md-block" id="riwayatTableWrap" style="display:none!important;">
                        <table class="table table-hover table-borderless align-middle modern-table" id="riwayatTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Perangkat</th>
                                    <th>OS &amp; Browser</th>
                                    <th>IP Address</th>
                                    <th>Lokasi</th>
                                    <th>Waktu Login</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards (hidden until data loaded) -->
                    <div class="d-block d-md-none" id="riwayatMobileWrap" style="display:none!important;">
                        <div id="riwayatMobileCards"></div>
                    </div>

                    <!-- Empty state -->
                    <div id="riwayatEmpty" class="text-center opacity-75 py-4" style="display:none;">
                        <i class="bi bi-clock-history fs-2 mb-2 d-block text-primary opacity-50"></i>
                        Belum ada riwayat login tercatat.
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════ -->
            <!-- Card Pengaturan 2FA Saya                                   -->
            <!-- ══════════════════════════════════════════════════════════ -->
            <div class="card mb-3" id="pengaturan-2fa-saya">
                <div class="card-header d-flex align-items-center justify-content-between pengaturan-card-header" style="cursor: pointer; user-select: none;">
                    <span><i class="bi bi-shield-lock me-2"></i>Pengaturan Two-Factor Authentication (2FA)</span>
                    <i class="bi bi-chevron-up chevron-icon opacity-50" style="transition: transform 0.2s; transform: rotate(180deg);"></i>
                </div>
                <div class="card-body pengaturan-card-body" style="display: none;">
                    <p class="opacity-75 small mb-3">
                        Jika 2FA global aktif, setiap login dari perangkat baru (IP berbeda) akan membutuhkan kode OTP.
                        Anda dapat mengecualikan diri sendiri agar tidak diminta OTP.
                    </p>
                    <div class="d-flex align-items-center justify-content-between p-3 border rounded shadow-sm theme-card-inner">
                        <div class="d-flex flex-column">
                            <strong>Status 2FA Anda</strong>
                            <span class="opacity-75" style="font-size:0.85em;">
                                <?php if ($isExempt): ?>
                                    Mati (Anda tidak akan dimintai OTP)
                                <?php else: ?>
                                    Aktif (Wajib OTP untuk perangkat baru)
                                <?php endif; ?>
                            </span>
                        </div>
                        <div class="form-check form-switch ms-3" style="font-size: 1.25rem; margin-bottom: 0;">
                            <input class="form-check-input" type="checkbox" id="btnToggleExemptSwitch" style="cursor: pointer;" <?= !$isExempt ? 'checked' : '' ?>>
                        </div>
                    </div>
                    <p class="opacity-75 mt-2" style="font-size:0.82em;">Perubahan berlaku pada login berikutnya.</p>
                </div>
            </div>

            <!-- Card Perangkat Tepercaya Saya -->
            <div class="card mb-3" id="perangkat-tepercaya">
                <div class="card-header d-flex align-items-center justify-content-between pengaturan-card-header" style="cursor: pointer; user-select: none;">
                    <span><i class="bi bi-shield-check me-2"></i>Perangkat Tepercaya Saya</span>
                    <i class="bi bi-chevron-up chevron-icon opacity-50" style="transition: transform 0.2s; transform: rotate(180deg);"></i>
                </div>
                <div class="card-body pengaturan-card-body" style="display: none;">
                    <p class="opacity-75 small mb-3">
                        Daftar alamat IP yang sudah terverifikasi OTP. Perangkat tepercaya berlaku selama <strong>7 hari</strong>.
                        Hapus untuk memaksa verifikasi OTP saat login ulang dari perangkat tsb.
                    </p>
                    <!-- Skeleton loader -->
                    <div id="whitelistSkeleton" class="py-3 text-center opacity-75">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div> Memuat perangkat...
                    </div>
                    
                    <!-- Desktop View -->
                    <div class="table-responsive d-none d-md-block" id="whitelistTableWrap" style="display:none!important;">
                        <table class="table table-hover table-borderless align-middle modern-table" id="whitelistTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>IP Address</th>
                                    <th>Ditambahkan</th>
                                    <th>Berlaku Hingga</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    
                    <!-- Mobile View -->
                    <div class="d-block d-md-none" id="whitelistMobileWrap" style="display:none!important;">
                        <div id="whitelistMobileCards"></div>
                        <div id="whitelistMobilePagination" class="mt-3" style="display:none;">
                            <ul class="pagination mobile-pagination-purple border-0 mb-0" style="justify-content: center; gap: 8px;">
                                <li class="page-item"><button class="page-link" id="wlMobilePrev" type="button">&lt;</button></li>
                                <span id="wlMobilePageNumbers"></span>
                                <li class="page-item"><button class="page-link" id="wlMobileNext" type="button">&gt;</button></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div id="whitelistEmpty" class="text-center opacity-75 py-3" style="display:none;">
                        <i class="bi bi-shield fs-2 d-block mb-1 text-success opacity-50"></i>
                        Belum ada perangkat tepercaya.
                    </div>
                </div>
            </div>

        </div>
    </div>

    <?php include(APPPATH . 'Views/components/bottom_nav_user.php'); ?>

    <script>
    $(document).ready(function () {
        // Flash message
        if (window.flashMessage) {
            Swal.fire({
                icon: window.flashMessageType || 'success',
                title: window.flashMessage,
                timer: 2500,
                showConfirmButton: false
            });
        }

        // ── Render helper ────────────────────────────────────────────────
        function deviceIcon(type) {
            if (type === 'Mobile')  return '<i class="bi bi-phone-fill text-primary me-1"></i>';
            if (type === 'Tablet')  return '<i class="bi bi-tablet-fill text-success me-1"></i>';
            return '<i class="bi bi-laptop-fill text-secondary me-1"></i>';
        }

        function renderLokasi(row) {
            const c = row.location_country || '', r = row.location_region || '', ci = row.location_city || '';
            if (c === 'Jaringan Lokal') return '<span class="badge bg-secondary">Jaringan Lokal</span>';
            const parts = [ci, r, c].filter(Boolean);
            if (parts.length) return '<span style="font-size:0.9em;">' + parts.join(', ') + '</span>';
            return '<span class="opacity-75" style="font-size:0.85em;">Tidak tersedia</span>';
        }

        function formatDate(d) {
            if (!d) return '-';
            const dt = new Date(d.replace(' ', 'T'));
            const pad = n => String(n).padStart(2,'0');
            return pad(dt.getDate()) + '/' + pad(dt.getMonth()+1) + '/' + dt.getFullYear()
                + ' ' + pad(dt.getHours()) + ':' + pad(dt.getMinutes());
        }

        // ── Load data via AJAX POST ──────────────────────────────────────
        $.ajax({
            url: window.riwayatAjaxUrl,
            type: 'POST',
            data: { [window.csrfToken]: window.csrfHash },
            dataType: 'json',
            success: function(res) {
                $('#riwayatSkeleton').hide();
                const rows = res.data || [];

                if (rows.length === 0) {
                    $('#riwayatEmpty').show();
                    return;
                }

                // Tampilkan tombol hapus
                $('#btnHapusSemuaRiwayat').removeClass('d-none');

                // ── Desktop DataTable ─────────────────────────────────
                const tbody = $('#riwayatTable tbody');
                $.each(rows, function(i, row) {
                    tbody.append(
                        '<tr>' +
                        '<td>' + (i + 1) + '</td>' +
                        '<td>' + deviceIcon(row.device_type) + (row.device_type || 'Desktop') + '</td>' +
                        '<td>' +
                            '<div class="fw-semibold" style="font-size:.95em;">' + (row.device_os || '-') + '</div>' +
                            '<div class="opacity-75" style="font-size:.85em;">' + (row.browser || '-') + '</div>' +
                        '</td>' +
                        '<td><code>' + (row.ip_address || '-') + '</code></td>' +
                        '<td>' + renderLokasi(row) + '</td>' +
                        '<td style="white-space:nowrap;">' + formatDate(row.created_at) + '</td>' +
                        '</tr>'
                    );
                });

                // Hapus inline style display:none, biarkan class d-none d-md-block bekerja
                $('#riwayatTableWrap').attr('style', '');
                $('#riwayatTable').DataTable({
                    language: {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        zeroRecords: 'Data tidak ditemukan',
                        paginate: { previous: '&lsaquo;', next: '&rsaquo;' }
                    },
                    info: false,        // Sembunyikan "Menampilkan X - Y dari Z data"
                    pageLength: 10,
                    order: [[5, 'desc']],
                    columnDefs: [{ orderable: false, targets: [0] }]
                });

                // ── Mobile Cards ──────────────────────────────────────
                const mobileContainer = $('#riwayatMobileCards');
                $.each(rows, function(i, row) {
                    mobileContainer.append(
                        '<div class="border rounded mb-3 p-3 shadow-sm pengaturan-card-mobile">' +
                            '<div class="fw-bold mb-2 fs-6">' + deviceIcon(row.device_type) + (row.device_os || 'Tidak diketahui') + '</div>' +
                            '<div class="opacity-75 mb-3" style="font-size:0.95em;"><i class="bi bi-browser-chrome me-1"></i>' + (row.browser || '-') + '</div>' +
                            
                            '<div class="mb-2" style="font-size:0.95em;"><b>IP:</b> <code>' + (row.ip_address || '-') + '</code></div>' +
                            '<div class="mb-2" style="font-size:0.95em;"><b>Lokasi:</b> ' + renderLokasi(row) + '</div>' +
                            
                            '<hr class="my-2 opacity-50">' +
                            
                            '<div class="opacity-75 mt-2" style="font-size:0.85em;"><i class="bi bi-clock me-1"></i>' + formatDate(row.created_at) + '</div>' +
                        '</div>'
                    );
                });
                // Hapus inline style display:none, biarkan class d-block d-md-none bekerja
                $('#riwayatMobileWrap').attr('style', '');
            },
            error: function() {
                $('#riwayatSkeleton').hide();
                $('#riwayatEmpty').text('Terjadi kesalahan saat memuat data.').show();
            }
        });

        // ── Hapus semua riwayat ──────────────────────────────────────────
        $(document).on('click', '#btnHapusSemuaRiwayat', function () {
            Swal.fire({
                title: 'Hapus Semua Riwayat?',
                text: 'Seluruh riwayat perangkat login Anda akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post(window.hapusRiwayatUrl,
                        { [window.csrfToken]: window.csrfHash },
                        function(res) {
                            if (res.status === 'success') {
                                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1800, showConfirmButton: false })
                                    .then(() => location.reload());
                            }
                        }
                    ).fail(function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus riwayat.', 'error');
                    });
                }
            });
        });

        // ── Toggle exempt 2FA user sendiri ──────────────────────────────
        $('#btnToggleExemptSwitch').on('change', function(e) {
            e.preventDefault();
            const btn = $(this);
            const isChecked = btn.prop('checked');
            const title = isChecked ? 'Aktifkan 2FA?' : 'Matikan 2FA?';
            const text = isChecked ? 'Perangkat baru akan diminta kode OTP.' : 'Anda dapat login tanpa OTP dari perangkat mana saja.';

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
                    btn.prop('disabled', true);
                    $.ajax({
                        url: window.toggle2FaExemptUrl,
                        type: 'POST',
                        data: { [window.csrfToken]: window.csrfHash },
                        dataType: 'json',
                        success: function(res) {
                            if (res.status === 'success') {
                                Swal.fire({ icon: 'success', text: res.message, timer: 1500, showConfirmButton: false })
                                    .then(() => location.reload());
                            }
                        },
                        complete: function() { btn.prop('disabled', false); }
                    });
                } else {
                    btn.prop('checked', !isChecked); // revert state
                }
            });
        });

        // ── Load Whitelist via AJAX POST ───────────────────────────────────
        const myWhitelistAjaxUrl = "<?= base_url('user/pengaturan/whitelist_ajax') ?>";
        let whitelistData = [];
        let wlMobilePage = 1;
        const wlMobileLength = 10;

        function renderWhitelistMobileCards() {
            var start = (wlMobilePage - 1) * wlMobileLength;
            var end = start + wlMobileLength;
            var pageData = whitelistData.slice(start, end);

            var mc = $('#whitelistMobileCards');
            mc.empty();

            if (whitelistData.length === 0) return;

            var now = new Date();
            $.each(pageData, function(i, wl) {
                var expiresDate = new Date(wl.expires_at.replace(' ', 'T'));
                var isActive = expiresDate > now;
                var badge = isActive 
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-secondary">Expired</span>';

                var revokeUrl = window.revokeWhitelistBase + wl.id;

                mc.append(
                    '<div class="border rounded mb-3 p-3 shadow-sm pengaturan-card-mobile">' +
                        '<div class="d-flex justify-content-between align-items-center mb-2">' +
                            '<span class="fw-bold fs-6">No. ' + (start + i + 1) + '</span>' +
                            badge +
                        '</div>' +
                        '<div style="font-size: 0.95em;" class="mb-1"><b>IP:</b> <code>' + wl.ip_address + '</code></div>' +
                        '<div style="font-size: 0.95em;" class="mb-1"><b>Ditambahkan:</b> <span class="opacity-75">' + formatDate(wl.created_at) + '</span></div>' +
                        '<div style="font-size: 0.95em;" class="mb-1"><b>Berlaku:</b> <span class="opacity-75">' + formatDate(wl.expires_at) + '</span></div>' +
                        '<hr class="my-2 opacity-50">' +
                        '<div class="text-end mt-2">' +
                            '<a href="' + revokeUrl + '" class="btn btn-outline-danger btn-sm btn-revoke-my-whitelist aksi-btn" title="Hapus"><i class="fas fa-trash me-1"></i>Hapus</a>' +
                        '</div>' +
                    '</div>'
                );
            });

            var totalPages = Math.ceil(whitelistData.length / wlMobileLength);
            if (totalPages <= 1) {
                $('#whitelistMobilePagination').hide();
            } else {
                $('#whitelistMobilePagination').show();
                var pageNumbersHtml = "";
                var startPage = Math.max(1, wlMobilePage - 1);
                var endPage = Math.min(totalPages, startPage + 2);
                if (endPage - startPage < 2) {
                    startPage = Math.max(1, endPage - 2);
                }
                for (var j = startPage; j <= endPage; j++) {
                    var act = (j === wlMobilePage) ? "active" : "";
                    pageNumbersHtml += '<li class="page-item"><button class="page-link wl-page-number ' + act + '" data-page="' + j + '">' + j + '</button></li>';
                }
                $('#wlMobilePageNumbers').html(pageNumbersHtml);
                $('#wlMobilePrev').prop('disabled', wlMobilePage === 1);
                $('#wlMobileNext').prop('disabled', wlMobilePage === totalPages);

                $('.wl-page-number').off('click').on('click', function() {
                    wlMobilePage = parseInt($(this).data('page'));
                    renderWhitelistMobileCards();
                });
            }
        }

        $('#wlMobilePrev').on('click', function() {
            if (wlMobilePage > 1) { wlMobilePage--; renderWhitelistMobileCards(); }
        });
        $('#wlMobileNext').on('click', function() {
            if (wlMobilePage < Math.ceil(whitelistData.length / wlMobileLength)) { wlMobilePage++; renderWhitelistMobileCards(); }
        });

        $.ajax({
            url: myWhitelistAjaxUrl,
            type: 'POST',
            data: { [window.csrfToken]: window.csrfHash },
            dataType: 'json',
            success: function(res) {
                $('#whitelistSkeleton').hide();
                const rows = res.data || [];
                whitelistData = rows;

                if (rows.length === 0) {
                    $('#whitelistEmpty').show();
                    return;
                }

                const tbody = $('#whitelistTable tbody');
                let now = new Date();
                $.each(rows, function(i, wl) {
                    var expiresDate = new Date(wl.expires_at.replace(' ', 'T'));
                    var isActive = expiresDate > now;
                    var badge = isActive 
                        ? '<span class="badge bg-success">Aktif</span>'
                        : '<span class="badge bg-secondary">Expired</span>';

                    var revokeUrl = window.revokeWhitelistBase + wl.id;

                    tbody.append(
                        '<tr>' +
                        '<td>' + (i + 1) + '</td>' +
                        '<td><code>' + wl.ip_address + '</code></td>' +
                        '<td style="font-size:.88em;">' + formatDate(wl.created_at) + '</td>' +
                        '<td style="font-size:.88em;">' + formatDate(wl.expires_at) + '</td>' +
                        '<td>' + badge + '</td>' +
                        '<td><a href="' + revokeUrl + '" class="btn btn-danger btn-sm btn-revoke-my-whitelist aksi-btn" title="Hapus"><i class="fas fa-trash"></i></a></td>' +
                        '</tr>'
                    );
                });

                $('#whitelistTableWrap').attr('style', '');
                $('#whitelistTable').DataTable({
                    language: {
                        search: 'Cari:',
                        lengthMenu: 'Tampilkan _MENU_ data',
                        zeroRecords: 'Data tidak ditemukan',
                        paginate: { previous: '&lsaquo;', next: '&rsaquo;' }
                    },
                    info: false,
                    pageLength: 10,
                    order: [], 
                    columnDefs: [{ orderable: false, targets: [0, 5] }]
                });

                $('#whitelistMobileWrap').attr('style', '');
                renderWhitelistMobileCards();
            },
            error: function() {
                $('#whitelistSkeleton').hide();
                $('#whitelistEmpty').text('Gagal memuat data.').show();
            }
        });

        // ── Konfirmasi hapus whitelist user sendiri ──────────────────────
        $(document).on('click', '.btn-revoke-my-whitelist', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            Swal.fire({
                title: 'Hapus Perangkat Ini?',
                text: 'Anda akan diminta OTP saat login ulang dari perangkat ini.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
            }).then(result => { if (result.isConfirmed) window.location.href = url; });
        });

        // ── Card Collapse Toggle ─────────────────────────────────────────
        $(document).on('click', '.pengaturan-card-header', function(e) {
            if ($(e.target).closest('.btn, .btn-close, input, a, form').length) return;
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

