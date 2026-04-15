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
    <title>Daftar Pegawai - MASSIPA</title>
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
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://code.jquery.com" crossorigin>
    <link rel="preconnect" href="https://cdn.datatables.net" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="all"
        onload="this.media='all'">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/user/DaftarPegawai.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js" defer></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js" defer></script>
    <script src="<?= base_url('assets/js/user/DaftarPegawai.js') ?>" defer></script>
    <script>
        window.daftarPegawaiAjaxUrl = "<?= base_url('user/getPegawaiAjax') ?>";
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
                        <li><i class="bi bi-funnel"></i> Gunakan filter golongan dan jabatan untuk menampilkan pegawai sesuai kriteria yang diinginkan.</li>
                        <li><i class="bi bi-award"></i> Kolom <b>Pangkat/Golongan</b> menampilkan pangkat dan golongan terakhir pegawai.</li>
                        <li><i class="bi bi-building"></i> Kolom <b>Satker Aktif</b> menunjukkan satuan kerja tempat pegawai aktif saat ini.</li>
                        <li><i class="bi bi-person-check"></i> Kolom <b>Status</b> menampilkan status keaktifan pegawai (Aktif/Tidak Aktif).</li>
                        <li><i class="bi bi-search"></i> Anda dapat mencari nama atau NIP pegawai menggunakan kolom pencarian di kanan atas tabel.</li>
                    </ul>';
                    echo view('components/petunjuk', ['isiPetunjuk' => $isiPetunjuk]);
                    ?>
                </div>
            </div>
            <div class="card mb-3 d-none d-md-block">
                <div class="card-header">Filter Pegawai</div>
                <div class="card-body">
                    <form id="filterForm" class="mb-0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="filter_golongan" class="form-label">Filter Golongan</label>
                                <select class="form-select" id="filter_golongan">
                                    <option value="">Semua Golongan</option>
                                    <option value="I/a">I/a</option>
                                    <option value="I/b">I/b</option>
                                    <option value="I/c">I/c</option>
                                    <option value="I/d">I/d</option>
                                    <option value="II/a">II/a</option>
                                    <option value="II/b">II/b</option>
                                    <option value="II/c">II/c</option>
                                    <option value="II/d">II/d</option>
                                    <option value="III/a">III/a</option>
                                    <option value="III/b">III/b</option>
                                    <option value="III/c">III/c</option>
                                    <option value="III/d">III/d</option>
                                    <option value="IV/a">IV/a</option>
                                    <option value="IV/b">IV/b</option>
                                    <option value="IV/c">IV/c</option>
                                    <option value="IV/d">IV/d</option>
                                    <option value="IV/e">IV/e</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="filter_jabatan" class="form-label">Filter Jabatan</label>
                                <select class="form-select" id="filter_jabatan">
                                    <option value="">Semua Jabatan</option>
                                    <?php
                                    $jabatan_list = [];
                                    foreach ($pegawai as $row) {
                                        if (!empty($row["jabatan"]) && !in_array($row["jabatan"], $jabatan_list)) {
                                            $jabatan_list[] = $row["jabatan"];
                                        }
                                    }
                                    sort($jabatan_list);
                                    foreach ($jabatan_list as $jabatan): ?>
                                        <option value="<?= esc($jabatan) ?>"><?= esc($jabatan) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <section class="daftar-pegawai-section">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Daftar Pegawai</span>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm d-block d-md-none" style="background: #7c3aed; border-color: #7c3aed; color: #fff;" data-bs-toggle="modal" data-bs-target="#filterPegawaiModal" title="Filter Pegawai">
                                <i class="bi bi-funnel"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive d-none d-md-block">
                            <table id="pegawaiTable" class="table table-hover table-borderless align-middle modern-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama</th>
                                        <th>NIP</th>
                                        <th>Pangkat/Golongan</th>
                                        <th>Jabatan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-block d-md-none">
                            <div class="mb-3">
                                <input type="text" id="search_mobile_pegawai" class="form-control"
                                    placeholder="Cari nama/NIP pegawai..." autocomplete="off">
                            </div>
                            <div id="mobileCards"></div>
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
            </section>
        </div>
    </div>

    <div class="modal fade d-md-none" id="filterPegawaiModal" tabindex="-1" aria-labelledby="filterPegawaiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-bottom-sheet">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterPegawaiModalLabel">
                        <i class="bi bi-funnel me-2"></i>Filter Pegawai
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="filterForm">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="filter_golongan_mobile" class="form-label">Filter Golongan</label>
                                <select class="form-select filter-mobile" id="filter_golongan_mobile" data-filter-type="golongan">
                                    <option value="">Semua Golongan</option>
                                    <option value="I/a">I/a</option>
                                    <option value="I/b">I/b</option>
                                    <option value="I/c">I/c</option>
                                    <option value="I/d">I/d</option>
                                    <option value="II/a">II/a</option>
                                    <option value="II/b">II/b</option>
                                    <option value="II/c">II/c</option>
                                    <option value="II/d">II/d</option>
                                    <option value="III/a">III/a</option>
                                    <option value="III/b">III/b</option>
                                    <option value="III/c">III/c</option>
                                    <option value="III/d">III/d</option>
                                    <option value="IV/a">IV/a</option>
                                    <option value="IV/b">IV/b</option>
                                    <option value="IV/c">IV/c</option>
                                    <option value="IV/d">IV/d</option>
                                    <option value="IV/e">IV/e</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="filter_jabatan_mobile" class="form-label">Filter Jabatan</label>
                                <select class="form-select filter-mobile" id="filter_jabatan_mobile" data-filter-type="jabatan">
                                    <option value="">Semua Jabatan</option>
                                    <?php
                                    $jabatan_list = [];
                                    foreach ($pegawai as $row) {
                                        if (!empty($row["jabatan"]) && !in_array($row["jabatan"], $jabatan_list)) {
                                            $jabatan_list[] = $row["jabatan"];
                                        }
                                    }
                                    sort($jabatan_list);
                                    foreach ($jabatan_list as $jabatan): ?>
                                        <option value="<?= esc($jabatan) ?>"><?= esc($jabatan) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include(APPPATH . 'Views/components/bottom_nav_user.php'); ?>
</body>

</html>