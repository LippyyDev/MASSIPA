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
    <title>Kirim Laporan - MASSIPA</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/user/KirimLaporan.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">

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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <?php include(APPPATH . 'Views/components/sidebar_user.php'); ?>

    <?php include(APPPATH . 'Views/components/navbar_user.php'); ?>

    <div class="main-content">
        <div class="overlay"></div>

        <div class="container-fluid px-0">
            <div class="row mb-0">
                <div class="col-12">
                    <?php
                    $isiPetunjuk = '<ul class="mb-0 ps-3" style="font-size:0.98em;">
                        <li><i class="bi bi-pencil-square"></i> Isi nama laporan, pilih bulan dan tahun, serta tambahkan keterangan jika diperlukan.</li>
                        <li><i class="bi bi-file-earmark-arrow-up"></i> Upload file laporan dengan format PDF saja.</li>
                        <li><i class="bi bi-collection"></i> Maksimal 1 file dengan ukuran maksimal 1MB.</li>
                        <li><i class="bi bi-funnel"></i> Gunakan filter di bawah untuk menampilkan laporan sesuai bulan, tahun, dan kategori.</li>
                        <li><i class="bi bi-search"></i> Setelah upload, laporan akan dicek oleh admin. Status laporan akan berubah menjadi <b>Terkirim</b>, <b>Disetujui</b>, atau <b>Ditolak</b>.</li>
                        <li><i class="bi bi-arrow-repeat"></i> Jika laporan ditolak, Anda dapat mengupload ulang laporan yang sudah diperbaiki.</li>
                        <li><i class="bi bi-eye"></i> Gunakan tombol <b>Lihat File</b> untuk melihat file yang sudah diupload.</li>
                        <li><i class="bi bi-exclamation-triangle"></i> <b>Penting:</b> Laporan yang sudah disetujui (status "Disetujui") tidak dapat dihapus untuk menjaga integritas data arsip. Hanya laporan dengan status "Terkirim" atau "Ditolak" yang dapat dihapus.</li>
                    </ul>';
                    echo view('components/petunjuk', ['isiPetunjuk' => $isiPetunjuk]);
                    ?>
                </div>
            </div>

            <?php if (session()->getFlashdata("msg")): ?>
                <script>
                    Swal.fire({
                        icon: '<?= session()->getFlashdata('msg_type') === 'success' ? 'success' : (session()->getFlashdata('msg_type') === 'warning' ? 'warning' : 'error') ?>',
                        title: '<?= session()->getFlashdata('msg_type') === 'success' ? 'Berhasil' : (session()->getFlashdata('msg_type') === 'warning' ? 'Peringatan' : 'Gagal') ?>',
                        text: <?= json_encode(session()->getFlashdata('msg')) ?>,
                        timer: 3000,
                        showConfirmButton: false
                    });
                </script>
            <?php endif; ?>

            <div class="card shadow mb-3">
                <div class="card-header py-3 form-upload-header" style="cursor: pointer; user-select: none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-dark">Form Upload Laporan</h6>
                        <i class="bi bi-chevron-down chevron-form-upload" style="transition: transform 0.2s;"></i>
                    </div>
                </div>
                <div class="card-body form-upload-body" style="display: none;">
                    <form action="<?= base_url("user/kirimlaporan/add") ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="nama_laporan" class="form-label">Nama Laporan</label>
                            <input type="text" class="form-control" id="nama_laporan" name="nama_laporan"
                                autocomplete="off" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="bulan" class="form-label">Bulan</label>
                                <select class="form-select" id="bulan" name="bulan" required>
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= $i; ?>" <?= (date("n") == $i) ? "selected" : ""; ?>>
                                            <?= getBulanIndo($i); ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="tahun" class="form-label">Tahun</label>
                                <input type="number" class="form-control" id="tahun" name="tahun"
                                    value="<?= date('Y'); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="link_drive" class="form-label">Link Drive</label>
                            <input type="url" class="form-control" id="link_drive" name="link_drive"
                                placeholder="https://drive.google.com/...">
                            <small class="form-text text-muted">Masukkan link Google Drive (opsional jika sudah upload
                                file).</small>
                        </div>
                        <div class="mb-3">
                            <label for="kategori" class="form-label">Kategori Berkas <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="kategori" name="kategori" required>
                                <option value="">Pilih Kategori</option>
                                <option value="Laporan Disiplin">Laporan Disiplin</option>
                                <option value="Laporan Apel">Laporan Apel</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="files" class="form-label">Upload File</label>
                            <input type="file" class="form-control" id="files" name="files[]" accept=".pdf">
                            <small class="form-text text-muted">Format: PDF saja (Max 1MB). Opsional jika sudah mengisi
                                Link Drive.</small>
                        </div>

                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-2"></i>Upload
                            Laporan</button>
                    </form>
                </div>
            </div>

            <div class="card shadow mb-3 d-none d-md-block">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-dark">Filter Laporan</h6>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('user/kirimlaporan') ?>" method="get" class="row g-3" id="filterForm">
                        <div class="col-md-4">
                            <label for="filter_bulan" class="form-label">Bulan</label>
                            <select name="bulan" id="filter_bulan" class="form-select">
                                <option value="">Semua Bulan</option>
                                <?php
                                $nama_bulan = [
                                    1 => 'Januari',
                                    2 => 'Februari',
                                    3 => 'Maret',
                                    4 => 'April',
                                    5 => 'Mei',
                                    6 => 'Juni',
                                    7 => 'Juli',
                                    8 => 'Agustus',
                                    9 => 'September',
                                    10 => 'Oktober',
                                    11 => 'November',
                                    12 => 'Desember'
                                ];
                                for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>" <?= isset($filter_bulan) && $filter_bulan == $i ? 'selected' : '' ?>>
                                        <?= $nama_bulan[$i] ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filter_tahun" class="form-label">Tahun</label>
                            <select name="tahun" id="filter_tahun" class="form-select">
                                <option value="">Semua Tahun</option>
                                <?php foreach ($tahun_tersedia as $tahun): ?>
                                    <option value="<?= $tahun ?>" <?= isset($filter_tahun) && $filter_tahun == $tahun ? 'selected' : '' ?>>
                                        <?= $tahun ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="filter_kategori" class="form-label">Kategori</label>
                            <select name="kategori" id="filter_kategori" class="form-select">
                                <option value="">Semua Kategori</option>
                                <option value="Laporan Disiplin" <?= isset($filter_kategori) && $filter_kategori == 'Laporan Disiplin' ? 'selected' : '' ?>>
                                    Laporan Disiplin
                                </option>
                                <option value="Laporan Apel" <?= isset($filter_kategori) && $filter_kategori == 'Laporan Apel' ? 'selected' : '' ?>>
                                    Laporan Apel
                                </option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow mb-3">
                <div class="card-header py-3 d-flex flex-wrap align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-dark">Daftar Laporan</h6>
                    <div class="d-flex gap-2 align-items-center">
                        <button type="button" class="btn btn-sm d-block d-md-none" style="background: #7c3aed; border-color: #7c3aed; color: #fff;" data-bs-toggle="modal" data-bs-target="#filterKirimLaporanModal" title="Filter Laporan">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover table-borderless align-middle modern-table" id="laporanTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Laporan</th>
                                    <th>Bulan/Tahun</th>
                                    <th>Kategori</th>
                                    <th>Keterangan</th>
                                    <th>Status</th>
                                    <th>Feedback</th>
                                    <th>Tanggal Upload</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-block d-md-none">
                        <div id="mobileLaporanCards"></div>
                        <div id="mobileLaporanPagination" style="display:none;">
                            <ul class="pagination mb-0" style="justify-content: center; gap: 8px;">
                                <li class="page-item">
                                    <button class="page-link" id="mobileLaporanPrev" type="button">&lt;</button>
                                </li>
                                <span id="mobileLaporanPageNumbers"></span>
                                <li class="page-item">
                                    <button class="page-link" id="mobileLaporanNext" type="button">&gt;</button>
                                </li>
                            </ul>
                                </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Hapus Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus laporan ini?</p>
                    <p class="text-muted small">Laporan yang sudah disetujui tidak dapat dihapus untuk menjaga
                        integritas data arsip.</p>
                    <form action="<?= base_url("user/kirimlaporan/delete") ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="laporan_id_to_delete" id="delete_laporan_id">
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        window.laporanAjaxUrl = '<?= base_url('user/kirimlaporan/getLaporanAjax') ?>';
    </script>
    <script src="<?= base_url('assets/js/user/KirimLaporan.js') ?>"></script>
    
    <script>
    (function() {
        if (window._formUploadToggleInit) return;
        window._formUploadToggleInit = true;
        $(document).on('click', '.form-upload-header', function() {
            var card = $(this).closest('.card');
            var body = card.find('.form-upload-body');
            var chevron = card.find('.chevron-form-upload');
            if (body.is(':visible')) {
                body.stop(true, true).slideUp(180);
                chevron.css('transform', 'rotate(0deg)');
            } else {
                body.stop(true, true).slideDown(180);
                chevron.css('transform', 'rotate(180deg)');
            }
        });
    })();
    </script>
    
    <div class="modal fade d-md-none" id="filterKirimLaporanModal" tabindex="-1" aria-labelledby="filterKirimLaporanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-bottom-sheet">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterKirimLaporanModalLabel">
                        <i class="bi bi-funnel me-2"></i>Filter Laporan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="GET" action="<?= base_url('user/kirimlaporan') ?>" id="filterFormMobile">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="filter_bulan_mobile" class="form-label">Bulan</label>
                                <select name="bulan" id="filter_bulan_mobile" class="form-select">
                                    <option value="">Semua Bulan</option>
                                    <?php
                                    $nama_bulan = [
                                        1 => 'Januari',
                                        2 => 'Februari',
                                        3 => 'Maret',
                                        4 => 'April',
                                        5 => 'Mei',
                                        6 => 'Juni',
                                        7 => 'Juli',
                                        8 => 'Agustus',
                                        9 => 'September',
                                        10 => 'Oktober',
                                        11 => 'November',
                                        12 => 'Desember'
                                    ];
                                    for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= $i ?>" <?= isset($filter_bulan) && $filter_bulan == $i ? 'selected' : '' ?>>
                                            <?= $nama_bulan[$i] ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="filter_tahun_mobile" class="form-label">Tahun</label>
                                <select name="tahun" id="filter_tahun_mobile" class="form-select">
                                    <option value="">Semua Tahun</option>
                                    <?php foreach ($tahun_tersedia as $tahun): ?>
                                        <option value="<?= $tahun ?>" <?= isset($filter_tahun) && $filter_tahun == $tahun ? 'selected' : '' ?>>
                                            <?= $tahun ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="filter_kategori_mobile" class="form-label">Kategori</label>
                                <select name="kategori" id="filter_kategori_mobile" class="form-select">
                                    <option value="">Semua Kategori</option>
                                    <option value="Laporan Disiplin" <?= isset($filter_kategori) && $filter_kategori == 'Laporan Disiplin' ? 'selected' : '' ?>>
                                        Laporan Disiplin
                                    </option>
                                    <option value="Laporan Apel" <?= isset($filter_kategori) && $filter_kategori == 'Laporan Apel' ? 'selected' : '' ?>>
                                        Laporan Apel
                                    </option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnFilterMobile">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <?php include(APPPATH . 'Views/components/bottom_nav_user.php'); ?>
</body>

</html>