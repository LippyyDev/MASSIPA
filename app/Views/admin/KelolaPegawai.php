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
    <title>Kelola Pegawai - MASSIPA</title>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/KelolaPegawai.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/ImportPegawai.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include(APPPATH . 'Views/components/sidebar_admin.php'); ?>

    <?php include(APPPATH . 'Views/components/navbar_admin.php'); ?>

    <div class="main-content">
        <div class="overlay"></div>
        <div class="container-fluid px-0">
            <?php if (session()->getFlashdata("msg")): ?>
                <script>
                    Swal.fire({
                        icon: '<?= session()->getFlashdata("msg_type") === "success" ? "success" : "error" ?>',
                        title: '<?= session()->getFlashdata("msg_type") === "success" ? "Berhasil" : "Gagal" ?>',
                        text: <?= json_encode(session()->getFlashdata("msg")) ?>,
                        timer: 2500,
                        showConfirmButton: false
                    });
                </script>
            <?php endif; ?>
            <div class="row mb-0">
                <div class="col-12">
                    <?php
                    $isiPetunjuk = '<ul class="mb-0 ps-3" style="font-size:0.98em;">
                        <li><i class="bi bi-funnel"></i> Gunakan filter satuan kerja, golongan, dan jabatan untuk menampilkan pegawai sesuai kriteria yang diinginkan.</li>
                        <li><i class="bi bi-award"></i> Kolom <b>Pangkat/Golongan</b> menampilkan pangkat dan golongan terakhir pegawai.</li>
                        <li><i class="bi bi-building"></i> Kolom <b>Satker Aktif</b> menunjukkan satuan kerja tempat pegawai aktif saat ini.</li>
                        <li><i class="bi bi-person-check"></i> Kolom <b>Status</b> menampilkan status keaktifan pegawai (Aktif/Tidak Aktif).</li>
                        <li><i class="bi bi-search"></i> Anda dapat mencari nama atau NIP pegawai menggunakan kolom pencarian di kanan atas tabel.</li>
                        <li><i class="bi bi-pencil-square"></i> Gunakan tombol <i class="fas fa-edit" style="color: #000;"></i> untuk mengubah data pegawai, <i class="fas fa-random" style="color: #000;"></i> untuk memindahkan satker, <i class="bi bi-x-circle" style="color: #000;"></i> untuk mengubah status, dan <i class="fas fa-trash" style="color: #000;"></i> untuk menghapus pegawai.</li>
                        <li><i class="bi bi-file-earmark-arrow-up"></i> <b>Import CSV</b> mendukung update data existing: NIP sama akan diupdate (nama, pangkat, golongan, jabatan), jika satker berbeda akan dibuat mutasi baru.</li>
                    </ul>';
                    echo view('components/petunjuk', ['isiPetunjuk' => $isiPetunjuk]);
                    ?>
                </div>
            </div>
            <div class="card mb-3 d-none d-md-block">
                <div class="card-header">Filter Pegawai</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="filter_satker" class="form-label">Filter Satuan Kerja</label>
                            <select class="form-select" id="filter_satker">
                                <option value="">Semua Satker</option>
                                <?php foreach ($list_satker as $satker): ?>
                                    <option value="<?= esc($satker['nama']) ?>"><?= esc($satker['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
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
                        <div class="col-md-4">
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
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Tambah Pegawai Baru</span>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#tambahPegawaiModal" title="Tambah Pegawai">
                            <i class="fas fa-plus"></i><span class="d-none d-md-inline ms-1">Tambah Pegawai</span>
                        </button>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal"
                            data-bs-target="#importPegawaiModal" title="Import Pegawai (CSV)">
                            <i class="fas fa-file-csv"></i><span class="d-none d-md-inline ms-1">Import Pegawai (CSV)</span>
                        </button>
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
                                    <th>Satker Aktif</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
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
                        <div id="mobilePegawaiCards"></div>
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

    </div>
    </form>
    </div>
    </div>
    </div>
    <div class="modal fade" id="tambahPegawaiModal" tabindex="-1" aria-labelledby="tambahPegawaiModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahPegawaiModalLabel">Tambah Pegawai Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="<?= base_url('admin/input_pegawai/add') ?>">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" autocomplete="name" oninput="this.value = this.value.replace(/[0-9]/g, '')" required>
                        </div>
                        <div class="mb-3">
                            <label for="nip" class="form-label">NIP</label>
                            <input type="number" class="form-control" id="nip" name="nip" autocomplete="off" pattern="[0-9]*" inputmode="numeric" required>
                        </div>
                        <div class="mb-3">
                            <label for="golongan_tambah" class="form-label">Golongan</label>
                            <select class="form-select" id="golongan_tambah" name="golongan" required>
                                <option value="">Pilih Golongan</option>
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
                        <div class="mb-3">
                            <label for="pangkat_tambah" class="form-label">Pangkat</label>
                            <input type="text" class="form-control" id="pangkat_tambah" name="pangkat" readonly
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="jabatan" class="form-label">Jabatan</label>
                            <input type="text" class="form-control" id="jabatan" name="jabatan" oninput="this.value = this.value.replace(/[0-9]/g, '')" required>
                        </div>
                        <div class="mb-3">
                            <label for="satker_id" class="form-label">Satuan Kerja</label>
                            <select class="form-select" id="satker_id" name="satker_id" required>
                                <option value="">Pilih Satuan Kerja</option>
                                <?php foreach ($list_satker as $satker): ?>
                                    <option value="<?= esc($satker['id']) ?>"><?= esc($satker['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tanggal_mulai" class="form-label">Tanggal Mulai Bekerja di Satker Ini</label>
                            <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Pegawai</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        window.inputPegawaiAjaxUrl = "<?= base_url('admin/getPegawaiAjax') ?>";
        window.mutasiPegawaiUrl = "<?= base_url('admin/mutasiPegawai/') ?>";
        window.toggleStatusPegawaiUrl = "<?= base_url('admin/toggleStatusPegawai/') ?>";
        window.deletePegawaiUrl = "<?= base_url('admin/input_pegawai/delete/') ?>";
        window.updatePegawaiUrl = "<?= base_url('admin/input_pegawai/update/') ?>";

        $(document).ready(function () {
            $('.modal').removeAttr('aria-hidden');

            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'aria-hidden') {
                        $(mutation.target).removeAttr('aria-hidden');
                    }
                });
            });

            $('.modal').each(function () {
                observer.observe(this, {
                    attributes: true,
                    attributeFilter: ['aria-hidden']
                });
            });
        });
    </script>
    <script src="<?= base_url('assets/js/admin/KelolaPegawai.js') ?>"></script>
    <script src="<?= base_url('assets/js/admin/ImportPegawai.js') ?>"></script>

    <div class="modal fade" id="editPegawaiModal" tabindex="-1" aria-labelledby="editPegawaiModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?= base_url('admin/input_pegawai/update') ?>" id="editPegawaiForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPegawaiModalLabel">Edit Pegawai</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_pegawai_id">
                        <div class="mb-3">
                            <label for="edit_nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="edit_nama" name="nama" oninput="this.value = this.value.replace(/[0-9]/g, '')" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_nip" class="form-label">NIP</label>
                            <input type="number" class="form-control" id="edit_nip" name="nip" pattern="[0-9]*" inputmode="numeric" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_golongan" class="form-label">Golongan</label>
                            <select class="form-select" id="edit_golongan" name="golongan" required>
                                <option value="">Pilih Golongan</option>
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
                        <div class="mb-3">
                            <label for="edit_pangkat" class="form-label">Pangkat</label>
                            <input type="text" class="form-control" id="edit_pangkat" name="pangkat" required readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit_jabatan" class="form-label">Jabatan</label>
                            <input type="text" class="form-control" id="edit_jabatan" name="jabatan" oninput="this.value = this.value.replace(/[0-9]/g, '')" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_satker_id" class="form-label">Satuan Kerja</label>
                            <select class="form-select" id="edit_satker_id" name="satker_id" required>
                                <option value="">Pilih Satuan Kerja</option>
                                <?php foreach ($list_satker as $satker): ?>
                                    <option value="<?= esc($satker['id']) ?>"><?= esc($satker['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_tanggal_mulai" class="form-label">Tanggal Mulai Bekerja di Satker
                                Ini</label>
                            <input type="date" class="form-control" id="edit_tanggal_mulai" name="tanggal_mulai"
                                required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?= $this->include('admin/ImportPegawai') ?>

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
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="filter_satker_mobile" class="form-label">Filter Satuan Kerja</label>
                            <select class="form-select filter-mobile" id="filter_satker_mobile" data-filter-type="satker">
                                <option value="">Semua Satker</option>
                                <?php foreach ($list_satker as $satker): ?>
                                    <option value="<?= esc($satker['nama']) ?>"><?= esc($satker['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
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
                </div>
            </div>
        </div>
    </div>

    <?php include(APPPATH . 'Views/components/bottom_nav_admin.php'); ?>
</body>

</html>