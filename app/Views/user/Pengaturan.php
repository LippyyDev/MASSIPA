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
        window.flashMessage     = <?= json_encode(session()->getFlashdata('msg')) ?>;
        window.flashMessageType = <?= json_encode(session()->getFlashdata('msg_type')) ?>;
        window.riwayatAjaxUrl   = "<?= base_url('user/pengaturan/riwayat_ajax') ?>";
        window.hapusRiwayatUrl  = "<?= base_url('user/pengaturan/hapus_riwayat') ?>";
        window.csrfToken        = "<?= csrf_token() ?>";
        window.csrfHash         = "<?= csrf_hash() ?>";
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
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-clock-history me-2"></i>Riwayat Perangkat Login</span>
                    <button class="btn btn-danger btn-sm d-none" id="btnHapusSemuaRiwayat">
                        <i class="bi bi-trash me-1"></i> Hapus Semua Riwayat Saya
                    </button>
                </div>
                <div class="card-body">
                    <!-- Skeleton loader -->
                    <div id="riwayatSkeleton" class="py-3 text-center text-muted">
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
                    <div id="riwayatEmpty" class="text-center text-muted py-4" style="display:none;">
                        <i class="bi bi-clock-history fs-2 mb-2 d-block text-primary opacity-50"></i>
                        Belum ada riwayat login tercatat.
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
            return '<span class="text-muted" style="font-size:0.85em;">Tidak tersedia</span>';
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
                            '<div class="text-muted" style="font-size:.85em;">' + (row.browser || '-') + '</div>' +
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
    });
    </script>
</body>
</html>
