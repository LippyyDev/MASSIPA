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
    <title>Kelola Disiplin - MASSIPA</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/KelolaDisiplin.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="main-content">
        <div class="overlay"></div>

        <div class="container-fluid px-0">
            <div class="row mb-0">
                <div class="col-12">
                    <?php
                    $isiPetunjuk = '<ul class="mb-0 ps-3" style="font-size:0.98em;">
                        <li><i class="bi bi-filter"></i> Gunakan filter bulan dan tahun untuk melihat data pelanggaran kedisiplinan.</li>
                        <li><i class="bi bi-eye"></i> Tampilan hanya menampilkan pegawai yang memiliki pelanggaran (≥1).</li>
                        <li><i class="bi bi-building"></i> Satker yang ditampilkan adalah satker tempat pelanggaran dibuat, bukan satker pegawai saat ini.</li>
                        <li><i class="bi bi-info-circle"></i> <b>T</b>=Terlambat, <b>TAM</b>=Tidak Apel Masuk, <b>PA</b>=Pulang Awal, <b>TAP</b>=Tidak Apel Pulang</li>
                        <li><i class="bi bi-info-circle"></i> <b>KTI</b>=Keluar Tanpa Izin, <b>TK</b>=Tidak Kerja, <b>TMS</b>=Tidak Masuk Sidang, <b>TMK</b>=Tidak Masuk Kantor</li>
                    </ul>';
                    echo view('components/petunjuk', ['isiPetunjuk' => $isiPetunjuk]);
                    ?>
                </div>
            </div>

            <?php if (session()->getFlashdata('msg')): ?>
                <div class="alert alert-<?= esc(session()->getFlashdata('msg_type')) ?> alert-dismissible fade show" role="alert">
                    <?= esc(session()->getFlashdata('msg')) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card mb-3 d-none d-md-block">
                <div class="card-header">Filter Data Kedisiplinan</div>
                <div class="card-body">
                    <form class="row g-3 mb-0" id="filterDisiplinForm">
                        <div class="col-md-3">
                            <label for="filter_bulan" class="form-label">Bulan</label>
                            <select name="bulan" id="filter_bulan" class="form-control">
                                <?php foreach ($nama_bulan as $num => $nama): ?>
                                    <option value="<?= esc($num) ?>" <?= $bulan_dipilih == $num ? 'selected' : '' ?>>
                                        <?= esc($nama) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_tahun" class="form-label">Tahun</label>
                            <select name="tahun" id="filter_tahun" class="form-control">
                                <?php foreach ($daftar_tahun as $tahun): ?>
                                    <option value="<?= esc($tahun) ?>" <?= $tahun_dipilih == $tahun ? 'selected' : '' ?>>
                                        <?= esc($tahun) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_satker" class="form-label">Satker</label>
                            <select name="satker" id="filter_satker" class="form-control">
                                <option value="">Semua Satker</option>
                                <?php if (isset($daftar_satker) && is_array($daftar_satker)): ?>
                                    <?php foreach ($daftar_satker as $satker): ?>
                                        <option value="<?= esc($satker['id']) ?>" <?= ($satker_dipilih == $satker['id']) ? 'selected' : '' ?>>
                                            <?= esc($satker['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_pelanggaran" class="form-label">Jenis Pelanggaran</label>
                            <select name="pelanggaran" id="filter_pelanggaran" class="form-control">
                                <option value="">Semua Jenis</option>
                                <option value="T" <?= ($jenis_pelanggaran_dipilih == 'T') ? 'selected' : '' ?>>Terlambat</option>
                                <option value="TAM" <?= ($jenis_pelanggaran_dipilih == 'TAM') ? 'selected' : '' ?>>Tidak Absen Masuk</option>
                                <option value="PA" <?= ($jenis_pelanggaran_dipilih == 'PA') ? 'selected' : '' ?>>Pulang Awal</option>
                                <option value="TAP" <?= ($jenis_pelanggaran_dipilih == 'TAP') ? 'selected' : '' ?>>Tidak Absen Pulang</option>
                                <option value="KTI" <?= ($jenis_pelanggaran_dipilih == 'KTI') ? 'selected' : '' ?>>Keluar Tanpa Izin</option>
                                <option value="TK" <?= ($jenis_pelanggaran_dipilih == 'TK') ? 'selected' : '' ?>>Tidak Kerja</option>
                                <option value="TMS" <?= ($jenis_pelanggaran_dipilih == 'TMS') ? 'selected' : '' ?>>Tidak Masuk Sidang</option>
                                <option value="TMK" <?= ($jenis_pelanggaran_dipilih == 'TMK') ? 'selected' : '' ?>>Tidak Masuk Kantor</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow mb-3">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <span>Data Pelanggaran Disiplin</span>
                    <button type="button" class="btn btn-sm d-block d-md-none" style="background: #7c3aed; border-color: #7c3aed; color: #fff;" data-bs-toggle="modal" data-bs-target="#filterDisiplinModal" title="Filter Disiplin">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover table-borderless align-middle modern-table mb-0" id="kelolaDisiplinTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Pegawai</th>
                                    <th>Jabatan</th>
                                    <th>Satker</th>
                                    <th class="text-center">T</th>
                                    <th class="text-center">TAM</th>
                                    <th class="text-center">PA</th>
                                    <th class="text-center">TAP</th>
                                    <th class="text-center">KTI</th>
                                    <th class="text-center">TK</th>
                                    <th class="text-center">TMS</th>
                                    <th class="text-center">TMK</th>
                                    <th class="text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-block d-md-none">
                        <div class="mb-2 px-0">
                            <input type="text" id="search_mobile_disiplin" class="form-control"
                                placeholder="Cari nama pegawai..." autocomplete="off">
                        </div>
                        <div id="mobileDisiplinCards"></div>
                        <div id="mobilePagination" style="display:none;">
                            <ul class="pagination mobile-pagination-purple mb-0" style="justify-content: center; gap: 8px;">
                                <li class="page-item">
                                    <button class="page-link" id="mobilePrev" type="button">&lt;</button>
                                </li>
                                <span id="mobilePageNumbers"></span>
                                <li class="page-item">
                                    <button class="page-link" id="mobileNext" type="button">&gt;</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        window.kelolaDisiplinAjaxUrl = '<?= base_url('admin/kelola_disiplin/ajax') ?>';
        window.currentBulan = '<?= esc($bulan_dipilih) ?>';
        window.currentTahun = '<?= esc($tahun_dipilih) ?>';
        window.currentSatker = '<?= esc($satker_dipilih ?? '') ?>';
        window.currentJenisPelanggaran = '<?= esc($jenis_pelanggaran_dipilih ?? '') ?>';
    </script>
    
    <div class="modal fade d-md-none" id="filterDisiplinModal" tabindex="-1" aria-labelledby="filterDisiplinModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-bottom-sheet">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterDisiplinModalLabel">
                        <i class="bi bi-funnel me-2"></i>Filter Disiplin
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="filter_bulan_mobile" class="form-label">Bulan</label>
                            <select class="form-select filter-mobile" id="filter_bulan_mobile" data-filter-type="bulan">
                                <?php foreach ($nama_bulan as $num => $nama): ?>
                                    <option value="<?= esc($num) ?>" <?= $bulan_dipilih == $num ? 'selected' : '' ?>>
                                        <?= esc($nama) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="filter_tahun_mobile" class="form-label">Tahun</label>
                            <select class="form-select filter-mobile" id="filter_tahun_mobile" data-filter-type="tahun">
                                <?php foreach ($daftar_tahun as $tahun): ?>
                                    <option value="<?= esc($tahun) ?>" <?= $tahun_dipilih == $tahun ? 'selected' : '' ?>>
                                        <?= esc($tahun) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="filter_satker_mobile" class="form-label">Satker</label>
                            <select class="form-select filter-mobile" id="filter_satker_mobile" data-filter-type="satker">
                                <option value="">Semua Satker</option>
                                <?php if (isset($daftar_satker) && is_array($daftar_satker)): ?>
                                    <?php foreach ($daftar_satker as $satker): ?>
                                        <option value="<?= esc($satker['id']) ?>" <?= ($satker_dipilih == $satker['id']) ? 'selected' : '' ?>>
                                            <?= esc($satker['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="filter_pelanggaran_mobile" class="form-label">Jenis Pelanggaran</label>
                            <select class="form-select filter-mobile" id="filter_pelanggaran_mobile" data-filter-type="pelanggaran">
                                <option value="">Semua Jenis</option>
                                <option value="T" <?= ($jenis_pelanggaran_dipilih == 'T') ? 'selected' : '' ?>>Terlambat</option>
                                <option value="TAM" <?= ($jenis_pelanggaran_dipilih == 'TAM') ? 'selected' : '' ?>>Tidak Absen Masuk</option>
                                <option value="PA" <?= ($jenis_pelanggaran_dipilih == 'PA') ? 'selected' : '' ?>>Pulang Awal</option>
                                <option value="TAP" <?= ($jenis_pelanggaran_dipilih == 'TAP') ? 'selected' : '' ?>>Tidak Absen Pulang</option>
                                <option value="KTI" <?= ($jenis_pelanggaran_dipilih == 'KTI') ? 'selected' : '' ?>>Keluar Tanpa Izin</option>
                                <option value="TK" <?= ($jenis_pelanggaran_dipilih == 'TK') ? 'selected' : '' ?>>Tidak Kerja</option>
                                <option value="TMS" <?= ($jenis_pelanggaran_dipilih == 'TMS') ? 'selected' : '' ?>>Tidak Masuk Sidang</option>
                                <option value="TMK" <?= ($jenis_pelanggaran_dipilih == 'TMK') ? 'selected' : '' ?>>Tidak Masuk Kantor</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?= base_url('assets/js/admin/KelolaDisiplin.js') ?>"></script>
    <?php include(APPPATH . 'Views/components/bottom_nav_admin.php'); ?>
</body>

</html>
