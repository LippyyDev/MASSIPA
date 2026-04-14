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
    <title>Arsip Laporan - MASSIPA</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/ArsipLaporan.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
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
    <?php include(APPPATH . 'Views/components/sidebar_admin.php'); ?>
    <?php include(APPPATH . 'Views/components/navbar_admin.php'); ?>
    <div class="main-content">
        <div class="container-fluid px-0">
            <div id="notifDataTerpilih" class="notification-popup" style="display:none;">
                <div class="notification-content">
                    <i class="fas fa-check-circle me-2"></i>
                    <span id="notifTextTerpilih"></span>
                </div>
            </div>
            
            <div class="row mb-0">
                <div class="col-12">
                    <?php
                    $isiPetunjuk = '<ul class="mb-0 ps-3" style="font-size:0.98em;">
                        <li><i class="bi bi-archive"></i> Semua laporan yang sudah di-approve otomatis masuk ke arsip.</li>
                        <li><i class="bi bi-check-square"></i> Gunakan checkbox untuk memilih file, lalu klik <b>Download ZIP</b> untuk mengunduh file terpilih sekaligus.</li>
                        <li><i class="bi bi-link-45deg"></i> Laporan dengan link drive akan otomatis dibuat file TXT berisi informasi dan link lengkap.</li>
                        <li><i class="bi bi-trash"></i> Gunakan tombol <b>Hapus</b> untuk menghapus file arsip terpilih.</li>
                    </ul>';
                    echo view('components/petunjuk', ['isiPetunjuk' => $isiPetunjuk]);
                    ?>
                </div>
            </div>
            <?php if (session()->getFlashdata('msg')): ?>
                <div class="alert alert-<?= esc(session()->getFlashdata('msg_type')) ?> alert-dismissible fade show"
                    role="alert">
                    <?= esc(session()->getFlashdata('msg')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <div class="card mb-3 d-none d-md-block">
                <div class="card-header">Filter Arsip</div>
                <div class="card-body">
                    <div class="row mb-0">
                        <div class="col-md-3 mb-2">
                            <label class="form-label mb-1">Filter Pengirim</label>
                            <select id="filterPengirim" class="form-select">
                                <option value="">Semua Pengirim</option>
                                <?php if (!empty($arsip)):
                                    $pengirimList = array_unique(array_map(fn($a) => $a['nama_lengkap'], $arsip));
                                    sort($pengirimList);
                                    foreach ($pengirimList as $pengirim): ?>
                                        <option value="<?= esc($pengirim) ?>"><?= esc($pengirim) ?></option>
                                    <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label mb-1">Filter Bulan</label>
                            <select id="filterBulan" class="form-select">
                                <option value="">Semua Bulan</option>
                                <?php
                                $namaBulan = [
                                    '01' => 'Januari',
                                    '02' => 'Februari',
                                    '03' => 'Maret',
                                    '04' => 'April',
                                    '05' => 'Mei',
                                    '06' => 'Juni',
                                    '07' => 'Juli',
                                    '08' => 'Agustus',
                                    '09' => 'September',
                                    '10' => 'Oktober',
                                    '11' => 'November',
                                    '12' => 'Desember'
                                ];
                                foreach ($namaBulan as $val => $label): ?>
                                    <option value="<?= $val ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label mb-1">Filter Tahun</label>
                            <select id="filterTahun" class="form-select">
                                <option value="">Semua Tahun</option>
                                <?php if (!empty($arsip)):
                                    $tahunList = array_unique(array_map(fn($a) => $a['tahun'], $arsip));
                                    rsort($tahunList);
                                    foreach ($tahunList as $tahun): ?>
                                        <option value="<?= esc($tahun) ?>"><?= esc($tahun) ?></option>
                                    <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label mb-1">Filter Kategori</label>
                            <select id="filterKategori" class="form-select">
                                <option value="">Semua Kategori</option>
                                <option value="Laporan Disiplin">Laporan Disiplin</option>
                                <option value="Laporan Apel">Laporan Apel</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Daftar Arsip Laporan</span>
                    <div class="d-flex gap-2 align-items-center">
                        <button type="button" class="btn btn-sm d-block d-md-none" id="btnPilihSemuaMobile" style="background: #7c3aed; border-color: #7c3aed; color: #fff;" title="Pilih Semua">
                            <i class="fas fa-check-double"></i>
                        </button>
                        <button type="button" class="btn btn-sm d-block d-md-none" style="background: #7c3aed; border-color: #7c3aed; color: #fff;" data-bs-toggle="modal" data-bs-target="#filterArsipModal" title="Filter Arsip">
                            <i class="bi bi-funnel"></i>
                        </button>
                        <button type="button" class="btn btn-success btn-sm d-block d-md-none" id="btnDownloadZipMobile" title="Download ZIP" style="min-width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="fas fa-download"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm d-block d-md-none" id="btnDeleteArsipMobile" title="Hapus" style="min-width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="fas fa-trash"></i>
                        </button>
                        <div class="d-none d-md-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btnPilihSemua" style="white-space:nowrap;">
                                <input type="checkbox" id="checkAllBox" style="margin-right:6px;vertical-align:middle;">
                                Pilih Semua
                            </button>
                            <button type="button" class="btn btn-success btn-sm" id="btnDownloadZip"><i
                                    class="fas fa-download me-1"></i> Download ZIP</button>
                            <button type="button" class="btn btn-danger btn-sm" id="btnDeleteArsip"><i
                                    class="fas fa-trash me-1"></i> Hapus</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form id="arsipForm" method="post">
                        <div class="table-responsive d-none d-md-block">
                            <table id="arsipTable" class="table table-hover table-borderless align-middle modern-table">
                                <thead class="table-light">
                                    <tr>
                                        <th><input type="checkbox" id="selectAll"></th>
                                        <th>No</th>
                                        <th>Nama Laporan</th>
                                        <th>Bulan/Tahun</th>
                                        <th>Kategori</th>
                                        <th>Pengirim</th>
                                        <th>Keterangan</th>
                                        <th>Tanggal Upload</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-block d-md-none">
                            <form id="arsipFormMobile" method="post">
                                <div class="mb-3">
                                    <input type="text" id="search_mobile_arsip" class="form-control"
                                        placeholder="Cari nama laporan, pengirim, atau keterangan..." autocomplete="off">
                                </div>
                                <div id="mobileArsipCards"></div>
                                <div id="mobileArsipPagination" style="display:none;">
                                    <ul class="pagination mb-0" style="justify-content: center; gap: 8px;">
                                        <li class="page-item">
                                            <button class="page-link" id="mobileArsipPrev" type="button">&lt;</button>
                                        </li>
                                        <span id="mobileArsipPageNumbers"></span>
                                        <li class="page-item">
                                            <button class="page-link" id="mobileArsipNext" type="button">&gt;</button>
                                        </li>
                                    </ul>
                                </div>
                                <input type="hidden" name="action" id="mobileAction">
                            </form>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.arsipDataOriginal = <?php echo json_encode($arsip ?? []); ?>;
        window.arsipDownloadUrl = '<?= base_url('admin/arsip_laporan/download/') ?>';
        window.arsipDeleteUrl = '<?= base_url('admin/arsip_laporan/delete/') ?>';
        window.arsipDownloadZipUrl = '<?= base_url('admin/arsip_laporan/download_zip') ?>';
        window.arsipDeleteBulkUrl = '<?= base_url('admin/arsip_laporan/delete') ?>';
        window.getArsipAjaxUrl = '<?= base_url('admin/getArsipLaporanAjax') ?>';
    </script>
    <div class="modal fade d-md-none" id="filterArsipModal" tabindex="-1" aria-labelledby="filterArsipModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-bottom-sheet">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterArsipModalLabel">
                        <i class="bi bi-funnel me-2"></i>Filter Arsip
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="filterPengirimMobile" class="form-label">Filter Pengirim</label>
                            <select id="filterPengirimMobile" class="form-select">
                                <option value="">Semua Pengirim</option>
                                <?php if (!empty($arsip)):
                                    $pengirimList = array_unique(array_map(fn($a) => $a['nama_lengkap'], $arsip));
                                    sort($pengirimList);
                                    foreach ($pengirimList as $pengirim): ?>
                                        <option value="<?= esc($pengirim) ?>"><?= esc($pengirim) ?></option>
                                    <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="filterBulanMobile" class="form-label">Filter Bulan</label>
                            <select id="filterBulanMobile" class="form-select">
                                <option value="">Semua Bulan</option>
                                <?php
                                $namaBulan = [
                                    '01' => 'Januari',
                                    '02' => 'Februari',
                                    '03' => 'Maret',
                                    '04' => 'April',
                                    '05' => 'Mei',
                                    '06' => 'Juni',
                                    '07' => 'Juli',
                                    '08' => 'Agustus',
                                    '09' => 'September',
                                    '10' => 'Oktober',
                                    '11' => 'November',
                                    '12' => 'Desember'
                                ];
                                foreach ($namaBulan as $val => $label): ?>
                                    <option value="<?= $val ?>"><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="filterTahunMobile" class="form-label">Filter Tahun</label>
                            <select id="filterTahunMobile" class="form-select">
                                <option value="">Semua Tahun</option>
                                <?php if (!empty($arsip)):
                                    $tahunList = array_unique(array_map(fn($a) => $a['tahun'], $arsip));
                                    rsort($tahunList);
                                    foreach ($tahunList as $tahun): ?>
                                        <option value="<?= esc($tahun) ?>"><?= esc($tahun) ?></option>
                                    <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="filterKategoriMobile" class="form-label">Filter Kategori</label>
                            <select id="filterKategoriMobile" class="form-select">
                                <option value="">Semua Kategori</option>
                                <option value="Laporan Disiplin">Laporan Disiplin</option>
                                <option value="Laporan Apel">Laporan Apel</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?= base_url('assets/js/admin/ArsipLaporan.js') ?>"></script>
    <?php include(APPPATH . 'Views/components/bottom_nav_admin.php'); ?>
</body>

</html>