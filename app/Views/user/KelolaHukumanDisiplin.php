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
    <title>Kelola Hukuman Disiplin - MASSIPA</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/user/KelolaHukumanDisiplin.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="color-scheme" content="dark light">
    <script>
        window.BASE_URL = "<?= base_url() ?>";
    </script>
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
    <script src="<?= base_url('assets/js/user/KelolaHukumanDisiplin.js') ?>" defer></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
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
                    <li><i class="bi bi-person-badge"></i> Pilih pegawai, jabatan & satker otomatis terisi.</li>
                    <li><i class="bi bi-file-earmark"></i> No SK wajib diisi, file SK PDF opsional.</li>
                    <li><i class="bi bi-calendar"></i> Tanggal mulai dan berakhir wajib diisi.</li>
                    <li><i class="bi bi-exclamation-diamond"></i> Isi hukuman disiplin, peraturan yang dilanggar, dan keterangan jika ada.</li>
                    <li><i class="bi bi-download"></i> File SK dapat diunduh jika sudah diupload.</li>
                    <li><i class="bi bi-pencil-square"></i> Gunakan tombol <b>Edit</b> untuk mengubah data, <b>Hapus</b> untuk menghapus.</li>
                    <li><i class="bi bi-info-circle"></i> Data yang diajukan akan berstatus <b>pending</b> sampai disetujui admin.</li>
                </ul>';
                    echo view('components/petunjuk', ['isiPetunjuk' => $isiPetunjuk]);
                    ?>
                </div>
            </div>
            <?php if (session()->getFlashdata('msg')): ?>
                <script>
                    Swal.fire({
                        icon: '<?= session()->getFlashdata('msg_type') === 'success' ? 'success' : 'error' ?>',
                        title: '<?= session()->getFlashdata('msg_type') === 'success' ? 'Berhasil' : 'Gagal' ?>',
                        text: <?= json_encode(session()->getFlashdata('msg')) ?>,
                        timer: 2500,
                        showConfirmButton: false
                    });
                </script>
            <?php endif; ?>
            <div class="card mb-3">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center" id="toggleTambahHukuman" style="cursor:pointer;">
                    <span>Tambah Hukuman Disiplin</span>
                    <i class="bi bi-chevron-down ms-2" id="chevronTambahHukuman"></i>
                </div>
                <div class="collapse-form" style="display:none;">
                    <div class="card-body">
                        <form action="<?= base_url('user/kelola_hukuman_disiplin/addHukumanDisiplin') ?>" method="post" enctype="multipart/form-data">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label">Pegawai <span class="text-muted" style="font-weight:400;font-size:0.95em;">(Ketik nama/NIP untuk mencari)</span></label>
                                    <div class="position-relative">
                                        <input type="text" id="pegawai_search" class="form-control" placeholder="Cari nama/NIP pegawai..." autocomplete="off">
                                        <input type="hidden" name="pegawai_id" id="pegawai_id" required>
                                        <div id="pegawai_results" class="position-absolute w-100 bg-white border rounded shadow-sm" style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Jabatan</label>
                                    <input type="text" name="jabatan" id="jabatan" class="form-control" readonly required>
                                </div>
                            </div>
                            <div class="row g-3 align-items-end mt-1">
                                <div class="col-md-4">
                                    <label class="form-label">No SK</label>
                                    <input type="text" name="no_sk" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Upload File SK (PDF, opsional)</label>
                                    <input type="file" name="file_sk" class="form-control" accept="application/pdf">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Tgl Mulai</label>
                                    <input type="date" name="tanggal_mulai" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Tgl Berakhir</label>
                                    <input type="date" name="tanggal_berakhir" class="form-control" required>
                                </div>
                            </div>
                            <div class="row g-3 align-items-end mt-1">
                                <div class="col-12">
                                    <label class="form-label">Peraturan yang Dilanggar</label>
                                    <input type="text" name="peraturan_dilanggar" class="form-control" required>
                                </div>
                            </div>
                            <div class="row g-3 align-items-end mt-1">
                                <div class="col-12">
                                    <label class="form-label">Hukuman Disiplin yang Dijatuhkan</label>
                                    <input type="text" name="hukuman_dijatuhkan" class="form-control" required>
                                </div>
                            </div>
                            <div class="row g-3 align-items-end mt-1">
                                <div class="col-12">
                                    <label class="form-label">Keterangan</label>
                                    <input type="text" name="keterangan" class="form-control">
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-semibold"></i>Daftar Hukuman Disiplin</span>
                    <div class="d-flex align-items-center">
                        <div class="form-check form-check-inline me-2">
                            <input class="form-check-input" type="checkbox" id="exportPublic" value="1">
                            <label class="form-check-label" for="exportPublic">Publik</label>
                        </div>
                        <a href="<?= base_url('user/kelola_hukuman_disiplin/exportPdf') ?>" id="exportPdfBtn" class="btn btn-danger btn-sm me-2" aria-label="Export PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                        <a href="<?= base_url('user/kelola_hukuman_disiplin/exportWord') ?>" id="exportWordBtn" class="btn btn-primary btn-sm" aria-label="Export Word"><i class="bi bi-file-earmark-word"></i></a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive d-none d-md-block">
                        <table id="user-hukuman-table" class="table table-hover table-borderless align-middle modern-table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Pegawai</th>
                                    <th>Jabatan</th>
                                    <th>No SK</th>
                                    <th>Periode</th>
                                    <th>Hukuman</th>
                                    <th>Peraturan</th>
                                    <th>Keterangan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    
                    <div class="d-block d-md-none">
                        <div class="mb-3">
                            <input type="text" id="search_mobile_hukuman_user" class="form-control" placeholder="Cari nama pegawai..." autocomplete="off">
                        </div>
                        <div id="mobileHukumanCardsUser"></div>
                        <div id="mobilePaginationUser" style="display:none;">
                            <ul class="pagination mb-0" style="justify-content: center; gap: 8px;">
                                <li class="page-item">
                                    <button class="page-link" id="mobilePrevUser" type="button">&lt;</button>
                                </li>
                                <span id="mobilePageNumbersUser"></span>
                                <li class="page-item">
                                    <button class="page-link" id="mobileNextUser" type="button">&gt;</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    </div>

    <?php include(APPPATH . 'Views/components/bottom_nav_user.php'); ?>
</body>
</html>
<?php
function getSatkerNama($satker_id) {
    if (!$satker_id) return '-';
    $satkerModel = new \App\Models\SatkerModel();
    $satker = $satkerModel->find($satker_id);
    return $satker ? $satker['nama_satker'] : '-';
}
function getPegawaiSatkerId($pegawai_id) {
    $pegawaiModel = new \App\Models\PegawaiModel();
    $pegawai = $pegawaiModel->find($pegawai_id);
    return $pegawai ? $pegawai['satker_id'] : null;
}
function getSatkerLamaHukuman($pegawai_id, $tanggal_hukuman) {
    $riwayatModel = new \App\Models\RiwayatMutasiModel();
    $riwayat = $riwayatModel->where('pegawai_id', $pegawai_id)
        ->where('tanggal_mulai <=', $tanggal_hukuman)
        ->orderBy('tanggal_mulai', 'DESC')
        ->first();
    if ($riwayat) {
        $satkerModel = new \App\Models\SatkerModel();
        $satker = $satkerModel->find($riwayat['satker_id']);
        return (is_array($satker) && isset($satker['nama_satker'])) ? $satker['nama_satker'] : '-';
    }
    return null;
}

function getSatkerNamaByMutasi($pegawai_id, $tanggal = null) {
    $riwayatModel = new \App\Models\RiwayatMutasiModel();
    $satkerModel = new \App\Models\SatkerModel();
    if (!$tanggal) $tanggal = date('Y-m-d');
    $riwayat = $riwayatModel->where('pegawai_id', $pegawai_id)
        ->where('tanggal_mulai <=', $tanggal)
        ->groupStart()
        ->where('tanggal_selesai IS NULL')
        ->orWhere('tanggal_selesai >', $tanggal)
        ->groupEnd()
        ->orderBy('tanggal_mulai', 'DESC')
        ->first();
    if ($riwayat) {
        $satker = $satkerModel->find($riwayat['satker_id']);
        return (is_array($satker) && isset($satker['nama_satker'])) ? $satker['nama_satker'] : '-';
    }
    return '-';
}
?> 