<?php helper('app'); ?>
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
    <title>Rekap Laporan - MASSIPA</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/user/RekapLaporanDisiplin.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
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
    <?php include(APPPATH . 'Views/components/sidebar_user.php'); ?>

    <?php include(APPPATH . 'Views/components/navbar_user.php'); ?>

    <div class="main-content">
        <div class="container-fluid px-0">
            <?php if (session()->getFlashdata("msg")): ?>
                <div class="alert alert-<?= esc(session()->getFlashdata("msg_type")) ?> alert-dismissible fade show"
                    role="alert">
                    <?= esc(session()->getFlashdata("msg")) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row mb-0">
                <div class="col-12">
                    <?php
                    $isiPetunjuk = '<ul class="mb-0 ps-3" style="font-size:0.98em;">
                        <li><i class="bi bi-check2-square"></i> Centang data hakim/pegawai yang ingin diekspor. Data centang akan <b>tersimpan otomatis</b> dan <b>tidak akan hilang</b> meskipun Anda mengganti filter, mengubah jumlah entri, atau berpindah halaman.</li>
                        <li><i class="bi bi-funnel"></i> Anda bisa mengumpulkan data dari berbagai filter (misal: filter "Hakim", centang, lalu filter "Hakim Muda", centang lagi, dst). Semua data yang dicentang akan terkumpul dan bisa diekspor sekaligus.</li>
                        <li><i class="bi bi-check2-square"></i> Gunakan tombol <b>"Pilih Semua"</b> untuk mencentang semua data yang sedang tampil di halaman ini, atau meng-uncheck semua jika sudah tercentang semua.</li>
                        <li><i class="bi bi-trash"></i> Gunakan tombol <b>"Hapus Data Tersimpan"</b> untuk menghapus seluruh data centang yang sudah terkumpul.</li>
                        <li><i class="bi bi-exclamation-triangle"></i> <b>Penting:</b> Jika ingin mengekspor seluruh data yang sudah dicentang, pastikan dropdown <b>"Tampilkan entri"</b> diubah ke <b>"Semua"</b> sebelum menekan tombol Export, agar semua data yang dicentang benar-benar ikut diekspor.</li>
                    </ul>';
                    echo view('components/petunjuk', ['isiPetunjuk' => $isiPetunjuk]);
                    ?>
                </div>
            </div>
            <div class="card mb-3 d-none d-md-block">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Filter Rekap Laporan</span>
                </div>
                <div class="card-body">
                    <form action="" method="GET" class="row g-2 align-items-end invisible-on-load"
                        id="filterForm" style="margin-bottom:0;">
                        <div class="col-12 col-md-6">
                            <label for="bulan" class="form-label">Bulan</label>
                            <select name="bulan" id="bulan" class="form-select" onchange="this.form.submit()">
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>" <?= ($filter_bulan == $i) ? 'selected' : '' ?>>
                                        <?= getBulanIndo($i) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select name="tahun" id="tahun" class="form-select" onchange="this.form.submit()">
                                <?php foreach ($tahun_tersedia as $tahun): ?>
                                    <option value="<?= $tahun ?>" <?= ($filter_tahun == $tahun) ? 'selected' : '' ?>>
                                        <?= $tahun ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="w-100"></div>
                        <div class="d-flex flex-wrap align-items-end gap-2 mb-2">
                            <div class="filter-golongan flex-shrink-0"
                                style="min-width:180px; max-width:220px; width:200px;">
                                <label for="filter_golongan" class="form-label">Golongan <span
                                        style="font-weight:400;font-size:0.9em;">(bisa pilih lebih dari
                                        satu)</span></label>
                                <select name="golongan[]" id="filter_golongan" class="form-select" multiple
                                    size="7" style="min-height:2.5em;">
                                    <option value="">Semua</option>
                                    <?php
                                    $golongan_list = array_unique(array_filter(array_map(function ($row) {
                                        return $row['golongan'] ?? '';
                                    }, $kedisiplinan_data)));
                                    sort($golongan_list);
                                    $selected_gol = isset($_GET['golongan']) ? (array) $_GET['golongan'] : [];
                                    foreach ($golongan_list as $gol) {
                                        $selected = in_array($gol, $selected_gol) ? 'selected' : '';
                                        echo "<option value=\"$gol\" $selected>$gol</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="filter-jabatan flex-grow-1" style="min-width:180px;">
                                <label for="filter_jabatan" class="form-label">Jabatan <span
                                        style="font-weight:400;font-size:0.9em;">(bisa pilih lebih dari
                                        satu)</span></label>
                                <select name="jabatan[]" id="filter_jabatan" class="form-select" multiple
                                    size="7" style="min-height:2.5em;">
                                    <option value="">Semua</option>
                                    <?php
                                    $jabatan_list = array_unique(array_filter(array_map(function ($row) {
                                        return $row['jabatan'] ?? '';
                                    }, $kedisiplinan_data)));
                                    sort($jabatan_list);
                                    $selected_jab = isset($_GET['jabatan']) ? (array) $_GET['jabatan'] : [];
                                    foreach ($jabatan_list as $jab) {
                                        $selected = in_array($jab, $selected_jab) ? 'selected' : '';
                                        echo "<option value=\"$jab\" $selected>$jab</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-auto d-flex align-items-end justify-content-end"
                                style="min-width:140px;">
                                <button type="submit" class="btn btn-primary filter-btn-custom"><i
                                        class="bi bi-funnel"></i> Filter</button>
                            </div>
                        </div>
                    </form>
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-3 d-none d-md-flex">
                        <button type="button"
                            onclick="submitExport('<?= base_url('user/rekaplaporandisiplin/export_pdf') ?>')"
                            class="btn btn-danger btn-md"><i class="bi bi-file-earmark-pdf"></i> Export
                            PDF</button>
                        <button type="button"
                            onclick="submitExport('<?= base_url('user/rekaplaporandisiplin/export_word') ?>')"
                            class="btn btn-primary btn-md" style="background:#7c3aed;border-color:#7c3aed;"><i
                                class="bi bi-file-earmark-word"></i> Export Word</button>
                        <button type="button" id="toggleCheckAllBtn" onclick="toggleCheckAll()"
                            class="btn btn-outline-primary btn-md">
                            <i class="bi bi-check2-square"></i> Pilih Semua
                        </button>
                        <button type="button" onclick="clearSavedState()"
                            class="btn btn-outline-danger btn-md"><i class="bi bi-trash"></i> Hapus Data
                            Tersimpan</button>
                    </div>
                </div>
            </div>
            
            <div id="notifDataTerpilih" class="notification-popup" style="display:none;">
                <div class="notification-content">
                    <i class="fas fa-check-circle me-2"></i>
                    <span id="notifTextTerpilih"></span>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Rekap Disiplin</span>
                    <div class="d-flex gap-2 align-items-center d-block d-md-none">
                        <button type="button" class="btn btn-sm" style="background: #7c3aed; border-color: #7c3aed; color: #fff;" data-bs-toggle="modal" data-bs-target="#filterRekapLaporanModal" title="Filter Rekap">
                            <i class="bi bi-funnel"></i>
                        </button>
                        <button type="button" onclick="submitExport('<?= base_url('user/rekaplaporandisiplin/export_pdf') ?>')" class="btn btn-danger btn-sm" title="Export PDF" style="min-width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </button>
                        <button type="button" onclick="submitExport('<?= base_url('user/rekaplaporandisiplin/export_word') ?>')" class="btn btn-primary btn-sm" title="Export Word" style="background:#7c3aed;border-color:#7c3aed; min-width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="bi bi-file-earmark-word"></i>
                        </button>
                        <button type="button" id="toggleCheckAllBtnMobile" onclick="toggleCheckAll()" class="btn btn-outline-primary btn-sm" title="Pilih Semua" style="min-width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="bi bi-check2-square"></i>
                        </button>
                        <button type="button" onclick="clearSavedState()" class="btn btn-outline-danger btn-sm" title="Hapus Data Tersimpan" style="min-width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="report-header">
                        <h4>LAPORAN DISIPLIN HAKIM</h4>
                        <h5>YANG TIDAK MEMATUHI KETENTUAN JAM KERJA SESUAI DENGAN PERMA NO 7 TAHUN 2016</h5>
                        <p>BULAN : <?= strtoupper(getBulanIndo($filter_bulan)) . ' ' . $filter_tahun; ?></p>
                        <p>SATKER : <?= strtoupper($filter_satker_name); ?></p>
                    </div>

                    <form id="exportForm" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="bulan" value="<?= esc($filter_bulan) ?>">
                        <input type="hidden" name="tahun" value="<?= esc($filter_tahun) ?>">
                        <div class="table-responsive d-none d-md-block">
                            <table id="rekapTable" class="table table-hover table-borderless complex-table"
                                style="position:relative;">
                                <thead style="position:sticky;top:0;z-index:2;background:#fff;">
                                    <tr>
                                        <th class="text-center" rowspan="2">NO</th>
                                        <th class="text-center" rowspan="2">NAMA/NIP</th>
                                        <th class="text-center" rowspan="2">PANGKAT/GOL. RUANG</th>
                                        <th class="text-center" rowspan="2">JABATAN</th>
                                        <th class="text-center" rowspan="2">SATUAN KERJA</th>
                                        <th class="text-center" colspan="8">URAIAN AKUMULASI TIDAK DIPATUHKANNYA JAM
                                            KERJA DALAM 1 BULAN</th>
                                        <th class="text-center" rowspan="2">BENTUK PEMBINAAN</th>
                                        <th class="text-center" rowspan="2">KETERANGAN</th>
                                        <th class="text-center" rowspan="2">AKSI</th>
                                    </tr>
                                    <tr>
                                        <th class="text-center">t</th>
                                        <th class="text-center">tam</th>
                                        <th class="text-center">pa</th>
                                        <th class="text-center">tap</th>
                                        <th class="text-center">kti</th>
                                        <th class="text-center">tk</th>
                                        <th class="text-center">tms</th>
                                        <th class="text-center">tmk</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    $selected_gol = isset($_GET['golongan']) ? (array) $_GET['golongan'] : [];
                                    $selected_jab = isset($_GET['jabatan']) ? (array) $_GET['jabatan'] : [];
                                    foreach ($kedisiplinan_data as $row):
                                        // Filter data sesuai golongan dan jabatan jika ada (multi-select)
                                        if (
                                            (count($selected_gol) > 0 && $selected_gol[0] !== '' && !in_array($row['golongan'], $selected_gol)) ||
                                            (count($selected_jab) > 0 && $selected_jab[0] !== '' && !in_array($row['jabatan'], $selected_jab))
                                        ) {
                                            continue;
                                        }
                                        $total = $row["terlambat"] + $row["tidak_absen_masuk"] + $row["pulang_awal"] + $row["tidak_absen_pulang"] + $row["keluar_tidak_izin"] + $row["tidak_masuk_tanpa_ket"] + $row["tidak_masuk_sakit"] + $row["tidak_masuk_kerja"];
                                        ?>
                                        <tr class="data-row" data-id="<?= $row['id'] ?>">
                                            <td><?= $no++; ?></td>
                                            <td><?= $row["nama"]; ?><br><?= $row["nip"]; ?></td>
                                            <td><?= $row["pangkat"]; ?><br><?= $row["golongan"]; ?></td>
                                            <td><?= $row["jabatan"]; ?></td>
                                            <td><?= $row["nama_satker"] ?? '-'; ?></td>
                                            <td><?= $row["terlambat"]; ?></td>
                                            <td><?= $row["tidak_absen_masuk"]; ?></td>
                                            <td><?= $row["pulang_awal"]; ?></td>
                                            <td><?= $row["tidak_absen_pulang"]; ?></td>
                                            <td><?= $row["keluar_tidak_izin"]; ?></td>
                                            <td><?= $row["tidak_masuk_tanpa_ket"]; ?></td>
                                            <td><?= $row["tidak_masuk_sakit"]; ?></td>
                                            <td><?= $row["tidak_masuk_kerja"]; ?></td>
                                            <td><?= $row["bentuk_pembinaan"]; ?></td>
                                            <td><?= $row["keterangan"]; ?></td>
                                            <td><input type="checkbox" name="selected[]" value="<?= $row['id'] ?>"
                                                    onchange="updateBadgeTerpilih();highlightRow(this)"></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-block d-md-none">
                            <div class="mb-3">
                                <input type="text" id="mobileSearchRekap" class="form-control" placeholder="Cari pegawai (nama/NIP)...">
                            </div>
                            
                            <div id="mobileCardsContainerRekap">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <div class="mt-2">Memuat data...</div>
                                </div>
                            </div>
                            
                            <div id="mobilePaginationRekap" class="mt-3" style="display:none;">
                                <ul class="pagination mb-0">
                                    <li class="page-item">
                                        <button class="page-link" id="mobilePrevRekap" type="button">&lt;</button>
                                    </li>
                                    <li class="page-item">
                                        <button class="page-link" id="mobileNextRekap" type="button">&gt;</button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </form>

                    <div class="keterangan-section">
                        <h6>KETERANGAN :</h6>
                        <ul>
                            <li>t = TERLAMBAT</li>
                            <li>kti = KELUAR KANTOR TIDAK IZIN ATASAN</li>
                            <li>tam = TIDAK ABSEN MASUK</li>
                            <li>tk = TIDAK MASUK TANPA KETERANGAN</li>
                            <li>pa = PULANG AWAL</li>
                            <li>tms = TIDAK MASUK KARENA SAKIT TANPA MENGAJUKAN CUTI SAKIT</li>
                            <li>tap = TIDAK ABSEN PULANG</li>
                            <li>tmk = TIDAK MASUK KERJA</li>
                        </ul>
                    </div>

                    <div class="report-footer">
                        <?php if (!empty($tanda_tangan)): ?>
                            <p><?= $tanda_tangan["lokasi"]; ?>, <?= tanggalIndo($tanda_tangan["tanggal"]); ?></p>
                            <p><?= $tanda_tangan["nama_jabatan"]; ?></p>
                            <div class="report-signature">
                                <p><b><?= $tanda_tangan["nama_penandatangan"]; ?></b></p>
                                <p>NIP. <?= $tanda_tangan["nip_penandatangan"]; ?></p>
                            </div>
                        <?php else: ?>
                            <p>Tanda tangan belum tersedia.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('assets/js/user/RekapLaporanDisiplin.js') ?>"></script>

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

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <div class="modal fade d-md-none" id="filterRekapLaporanModal" tabindex="-1" aria-labelledby="filterRekapLaporanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-bottom-sheet">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterRekapLaporanModalLabel">
                        <i class="bi bi-funnel me-2"></i>Filter Rekap Laporan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="GET" action="" id="filterFormMobile">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="bulan_mobile" class="form-label">Bulan</label>
                                <select name="bulan" id="bulan_mobile" class="form-select">
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= $i ?>" <?= ($filter_bulan == $i) ? 'selected' : '' ?>>
                                            <?= getBulanIndo($i) ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="tahun_mobile" class="form-label">Tahun</label>
                                <select name="tahun" id="tahun_mobile" class="form-select">
                                    <?php foreach ($tahun_tersedia as $tahun): ?>
                                        <option value="<?= $tahun ?>" <?= ($filter_tahun == $tahun) ? 'selected' : '' ?>>
                                            <?= $tahun ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="filter_golongan_mobile" class="form-label">Golongan <span style="font-weight:400;font-size:0.9em;">(bisa pilih lebih dari satu)</span></label>
                                <select name="golongan[]" id="filter_golongan_mobile" class="form-select" multiple size="7" style="min-height:2.5em;">
                                    <option value="">Semua</option>
                                    <?php
                                    $selected_gol = isset($_GET['golongan']) ? (array) $_GET['golongan'] : [];
                                    foreach ($golongan_list as $gol) {
                                        $selected = in_array($gol, $selected_gol) ? 'selected' : '';
                                        echo "<option value=\"$gol\" $selected>$gol</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="filter_jabatan_mobile" class="form-label">Jabatan <span style="font-weight:400;font-size:0.9em;">(bisa pilih lebih dari satu)</span></label>
                                <select name="jabatan[]" id="filter_jabatan_mobile" class="form-select" multiple size="7" style="min-height:2.5em;">
                                    <option value="">Semua</option>
                                    <?php
                                    $selected_jab = isset($_GET['jabatan']) ? (array) $_GET['jabatan'] : [];
                                    foreach ($jabatan_list as $jab) {
                                        $selected = in_array($jab, $selected_jab) ? 'selected' : '';
                                        echo "<option value=\"$jab\" $selected>$jab</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnFilterMobile">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        $(document).ready(function() {
            $('.modal').removeAttr('aria-hidden');
            
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'aria-hidden') {
                        $(mutation.target).removeAttr('aria-hidden');
                    }
                });
            });
            
            $('.modal').each(function() {
                observer.observe(this, {
                    attributes: true,
                    attributeFilter: ['aria-hidden']
                });
            });
        });
    </script>
    
    <?php include(APPPATH . 'Views/components/bottom_nav_user.php'); ?>
</body>

</html>