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
    <title>Kelola Laporan - MASSIPA</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/KelolaLaporan.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        window.laporanAjaxUrl = "<?= base_url('admin/kelola_laporan/getLaporanAjax') ?>";
        window.csrfTokenName = "<?= csrf_token() ?>";
        window.csrfHash = "<?= csrf_hash() ?>";
    </script>
    <meta name="color-scheme" content="dark light">
    <script>
      (function() {
        try {
          var mode = localStorage.getItem('theme-mode');
          if (
            mode === 'dark' ||
            (!mode && window.matchMedia('(prefers-color-scheme: dark)').matches)
          ) {
            document.documentElement.classList.add('dark-mode');
          }
        } catch(e) {}
      })();
    </script>

</head>
<body data-flash-msg="<?= esc(session()->getFlashdata('msg')) ?>" data-flash-type="<?= esc(session()->getFlashdata('msg_type')) ?>">
    <?php include(APPPATH . 'Views/components/sidebar_admin.php'); ?>

    <?php include(APPPATH . 'Views/components/navbar_admin.php'); ?>

    <div class="main-content">
        <div class="overlay"></div>
        
        <div class="cotainer-fluid px-0">
            <div class="row mb-0">
                <div class="col-12">
                    <?php
                    $isiPetunjuk = '<ul class="mb-0 ps-3" style="font-size:0.98em;">
                        <li><i class="bi bi-funnel"></i> Gunakan filter di bawah ini untuk menampilkan laporan sesuai pengguna, bulan, dan tahun.</li>
                        <li><i class="bi bi-archive"></i> Laporan yang sudah di-approve otomatis masuk ke Arsip Laporan dan tidak muncul di sini.</li>
                        <li><i class="bi bi-eye"></i> Gunakan tombol <b>Lihat</b> untuk melihat file laporan yang diupload.</li>
                        <li><i class="bi bi-check-circle"></i> Gunakan tombol <b>Approve</b> untuk menyetujui laporan, <b>Reject</b> untuk menolak, dan <b>Hapus</b> untuk menghapus laporan.</li>
                        <li><i class="bi bi-chat-left-text"></i> Feedback dapat diisi saat approve/reject untuk memberikan catatan ke user.</li>
                        <li><i class="bi bi-arrow-repeat"></i> Status laporan akan berubah otomatis setelah aksi dilakukan.</li>
                    </ul>';
                    echo view('components/petunjuk', ['isiPetunjuk' => $isiPetunjuk]);
                    ?>
                </div>
            </div>
            
            <div class="card mb-3 d-none d-md-block">
                <div class="card-header">
                    Filter Laporan
                </div>
                <div class="card-body pt-3 pb-1">
                    <form action="<?= base_url('admin/kelola_laporan') ?>" method="get" class="row g-2 gx-2" id="filterForm">
                        <div class="col-md-3 mb-2">
                            <label for="filter_user" class="form-label">Pengguna</label>
                            <select name="user_id" id="filter_user" class="form-select">
                                <option value="">Semua Pengguna</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>" <?= isset($filter_user) && $filter_user == $user['id'] ? 'selected' : '' ?>>
                                        <?= $user['nama_lengkap'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="filter_bulan" class="form-label">Bulan</label>
                            <select name="bulan" id="filter_bulan" class="form-select">
                                <option value="">Semua Bulan</option>
                                <?php 
                                    $nama_bulan = [
                                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                    ];
                                    for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>" <?= isset($filter_bulan) && $filter_bulan == $i ? 'selected' : '' ?>>
                                        <?= $nama_bulan[$i] ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
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
                        <div class="col-md-3 mb-2">
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
            
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Daftar Laporan</span>
                    <button type="button" class="btn btn-sm d-block d-md-none" style="background: #7c3aed; border-color: #7c3aed; color: #fff;" data-bs-toggle="modal" data-bs-target="#filterLaporanModal" title="Filter Laporan">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive d-none d-md-block">
                        <table id="laporanTable" class="table table-hover table-borderless align-middle modern-table">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Laporan</th>
                                    <th style="width:80px;">Bulan/Tahun</th>
                                    <th>Kategori</th>
                                    <th>Pengirim</th>
                                    <th>Keterangan</th>
                                    <th style="width:120px;">Tanggal Upload</th>
                                    <th>Status</th>
                                    <th>Feedback</th>
                                    <th style="width:1%; white-space:nowrap;">Aksi</th>
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

    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="approveModalLabel">Approve Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?= base_url('admin/kelola_laporan/approve') ?>" method="POST" id="approveForm">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                    <input type="hidden" name="laporan_id" id="approveLaporanId" value="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="approveFeedback" class="form-label">Feedback (Opsional)</label>
                            <textarea class="form-control" id="approveFeedback" name="feedback" rows="4"></textarea>
                        </div>
                        <p>Apakah Anda yakin ingin menyetujui laporan ini?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalLabel">Tolak Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="<?= base_url('admin/kelola_laporan/reject') ?>" method="POST" id="rejectForm">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                    <input type="hidden" name="laporan_id" id="rejectLaporanId" value="">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="rejectFeedback" class="form-label">Feedback (Opsional)</label>
                            <textarea class="form-control" id="rejectFeedback" name="feedback" rows="4"></textarea>
                        </div>
                        <p>Apakah Anda yakin ingin menolak laporan ini?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tolak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
    <div class="modal fade d-md-none" id="filterLaporanModal" tabindex="-1" aria-labelledby="filterLaporanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-bottom-sheet">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterLaporanModalLabel">
                        <i class="bi bi-funnel me-2"></i>Filter Laporan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="<?= base_url('admin/kelola_laporan') ?>" method="get" id="filterFormMobile">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="filter_user_mobile" class="form-label">Pengguna</label>
                                <select name="user_id" id="filter_user_mobile" class="form-select">
                                    <option value="">Semua Pengguna</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?= $user['id'] ?>" <?= isset($filter_user) && $filter_user == $user['id'] ? 'selected' : '' ?>>
                                            <?= $user['nama_lengkap'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="filter_bulan_mobile" class="form-label">Bulan</label>
                                <select name="bulan" id="filter_bulan_mobile" class="form-select">
                                    <option value="">Semua Bulan</option>
                                    <?php 
                                        $nama_bulan = [
                                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                                            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
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
                    <button type="button" class="btn btn-primary" id="applyFilterMobile">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                </div>
            </div>
        </div>
    </div>
            
    <script src="<?= base_url('assets/js/admin/KelolaLaporan.js') ?>"></script>
    <?php include(APPPATH . 'Views/components/bottom_nav_admin.php'); ?>
</body>

</html>