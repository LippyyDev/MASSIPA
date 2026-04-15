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
    <title>Rekap Pegawai - MASSIPA</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/RekapPegawaiSatker.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="<?= base_url('assets/js/admin/RekapPegawaiSatker.js') ?>"></script>
    <meta name="color-scheme" content="dark light">
</head>

<body>
    <?php include(APPPATH . 'Views/components/sidebar_admin.php'); ?>

    <?php include(APPPATH . 'Views/components/navbar_admin.php'); ?>

    <div class="main-content">
        <div class="overlay"></div>
        
        <div class="container-fluid px-0">
            <div class="row mb-0">
                <div class="col-12">
                    <?php
                    $isiPetunjuk = '<ul class="mb-0 ps-3" style="font-size:0.98em;">
                        <li><i class="bi bi-funnel"></i> Gunakan filter bulan dan tahun untuk melihat rekap pegawai per satker.</li>
                        <li><i class="bi bi-table"></i> Tabel menampilkan rekapitulasi kedisiplinan pegawai per satker pada periode yang dipilih.</li>
                        <li><i class="bi bi-info-circle"></i> Keterangan singkatan pelanggaran ada di bawah tabel.</li>
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
                <div class="card-header fw-semibold">Filter Rekap User & Satker</div>
                <div class="card-body">
                    <form action="<?= base_url('admin/rekap_user_satker') ?>" method="get"
                        class="row g-2 align-items-end" id="filterForm" style="margin-bottom:0;">
                        <div class="col-12 col-md-5">
                            <label for="bulan" class="form-label">Bulan</label>
                            <select name="bulan" id="bulan" class="form-select">
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>" <?= $filter_bulan == $i ? 'selected' : '' ?>>
                                        <?= strtoupper(getBulanIndo($i)) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-5">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select name="tahun" id="tahun" class="form-select">
                                <?php foreach ($tahun_tersedia as $tahun): ?>
                                    <option value="<?= $tahun['tahun'] ?>" <?= $filter_tahun == $tahun['tahun'] ? 'selected' : '' ?>>
                                        <?= $tahun['tahun'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 d-flex align-items-end justify-content-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i>
                                Filter</button>
                        </div>
                    </form>
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-3 d-none d-md-flex">
                        <form action="<?= base_url('admin/exportUserSatkerPdf') ?>" method="post"
                            class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="bulan" value="<?= $filter_bulan ?>">
                            <input type="hidden" name="tahun" value="<?= $filter_tahun ?>">
                            <button type="submit" class="btn btn-danger btn-md"><i class="fas fa-file-pdf"></i>
                                Export PDF</button>
                        </form>
                        <form action="<?= base_url('admin/exportUserSatkerWord') ?>" method="post"
                            class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="bulan" value="<?= $filter_bulan ?>">
                            <input type="hidden" name="tahun" value="<?= $filter_tahun ?>">
                            <button type="submit" class="btn btn-primary btn-md"
                                style="background:#7c3aed;border-color:#7c3aed;"><i
                                    class="fas fa-file-word"></i> Export Word</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php
            $tandaTanganModel = new \App\Models\TandaTanganModel();
            $tanda_tangan = $tandaTanganModel->where('user_id', session()->get('user_id'))->orderBy('id', 'DESC')->first();
            ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Rekap Satker</span>
                    <div class="d-flex gap-2 align-items-center">
                        <button type="button" class="btn btn-sm d-block d-md-none" style="background: #7c3aed; border-color: #7c3aed; color: #fff;" data-bs-toggle="modal" data-bs-target="#filterRekapPegawaiModal" title="Filter Rekap">
                            <i class="bi bi-funnel"></i>
                        </button>
                        <form action="<?= base_url('admin/exportUserSatkerPdf') ?>" method="post" class="d-block d-md-none">
                            <?= csrf_field() ?>
                            <input type="hidden" name="bulan" value="<?= $filter_bulan ?>">
                            <input type="hidden" name="tahun" value="<?= $filter_tahun ?>">
                            <button type="submit" class="btn btn-danger btn-sm" title="Export PDF" style="min-width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                <i class="fas fa-file-pdf"></i>
                            </button>
                        </form>
                        <form action="<?= base_url('admin/exportUserSatkerWord') ?>" method="post" class="d-block d-md-none">
                            <?= csrf_field() ?>
                            <input type="hidden" name="bulan" value="<?= $filter_bulan ?>">
                            <input type="hidden" name="tahun" value="<?= $filter_tahun ?>">
                            <button type="submit" class="btn btn-primary btn-sm" title="Export Word" style="background:#7c3aed;border-color:#7c3aed; min-width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                <i class="fas fa-file-word"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3 report-heading">
                        <h5 class="fw-bold">LAPORAN DISIPLIN HAKIM</h5>
                        <p class="mb-1">YANG TIDAK MEMATUHI KETENTUAN JAM KERJA SESUAI DENGAN PERMA NO 7 TAHUN 2016</p>
                        <p class="mb-0">BULAN: <?= strtoupper(getBulanIndo($filter_bulan)) ?> <?= $filter_tahun ?></p>
                    </div>
                    <div class="table-responsive d-none d-md-block">
                        <table id="rekapTable" class="table table-hover table-borderless modern-table">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="text-center">NO</th>
                                    <th rowspan="2" class="text-center">SATKER</th>
                                    <th rowspan="2" class="text-center">ALAMAT</th>
                                    <th rowspan="2" class="text-center">TOTAL PEGAWAI</th>
                                    <th colspan="8" class="text-center">JUMLAH PEGAWAI DENGAN AKUMULASI TIDAK DIPATUHKAN
                                    </th>
                                    <th rowspan="2" class="text-center">BENTUK PEMBINAAN</th>
                                    <th rowspan="2" class="text-center">KETERANGAN</th>
                                </tr>
                                <tr>
                                    <th class="text-center">t</th>
                                    <th class="text-center">kti</th>
                                    <th class="text-center">tam</th>
                                    <th class="text-center">tk</th>
                                    <th class="text-center">pa</th>
                                    <th class="text-center">tms</th>
                                    <th class="text-center">tap</th>
                                    <th class="text-center">tmk</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $ada_data = false;
                                $no = 1;
                                foreach ($rekap_data as $data) {
                                    $ada_data = true;
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $no++ ?></td>
                                        <td class="text-center"><?= esc($data['nama_satker']) ?></td>
                                        <td class="text-center"><?= esc($data['alamat']) ?></td>
                                        <td class="text-center"><?= $data['total_pegawai'] ?></td>
                                        <?php if (!empty($data['belum_ada_data'])): ?>
                                            <td class="text-center">-</td>
                                            <td class="text-center">-</td>
                                            <td class="text-center">-</td>
                                            <td class="text-center">-</td>
                                            <td class="text-center">-</td>
                                            <td class="text-center">-</td>
                                            <td class="text-center">-</td>
                                            <td class="text-center">-</td>
                                            <td class="text-center">-</td>
                                            <td class="text-center">-</td>
                                        <?php else: ?>
                                            <td class="text-center"><?= $data['t'] ?></td>
                                            <td class="text-center"><?= $data['kti'] ?></td>
                                            <td class="text-center"><?= $data['tam'] ?></td>
                                            <td class="text-center"><?= $data['tk'] ?></td>
                                            <td class="text-center"><?= $data['pa'] ?></td>
                                            <td class="text-center"><?= $data['tms'] ?></td>
                                            <td class="text-center"><?= $data['tap'] ?></td>
                                            <td class="text-center"><?= $data['tmk'] ?></td>
                                            <td class="text-center"><?= esc($data['bentuk_pembinaan']) ?></td>
                                            <td class="text-center"><?= esc($data['keterangan']) ?></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php }
                                if (!$ada_data): ?>
                                    <tr>
                                        <td class="text-center" colspan="14">Belum ada data kedisiplinan pada bulan dan
                                            tahun ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-block d-md-none">
                        <div class="mb-3">
                            <input type="text" id="mobileSearchRekapSatker" class="form-control"
                                placeholder="Cari satker atau alamat..." autocomplete="off">
                        </div>
                        <div id="mobileRekapSatkerCards" class="mb-3">
                            <div class="text-center text-muted py-4">
                                <div class="spinner-border text-primary mb-2" role="status"></div>
                                <div>Memuat data...</div>
                            </div>
                        </div>
                        <div id="mobileRekapSatkerPagination" style="display:none;">
                            <ul class="pagination mb-0">
                                <li class="page-item">
                                    <button class="page-link" id="mobileRekapSatkerPrev" type="button">&lt;</button>
                                </li>
                                <li class="page-item">
                                    <button class="page-link" id="mobileRekapSatkerNext" type="button">&gt;</button>
                                </li>
                            </ul>
                        </div>
                    </div>
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
                    <?php if (isset($tanda_tangan) && $tanda_tangan): ?>
                        <div class="report-footer mt-3" style="text-align:right;">
                            <p><?= esc($tanda_tangan['lokasi']) ?>,
                                <?= tanggalIndo($tanda_tangan['tanggal']) ?>
                            </p>
                            <p><?= esc($tanda_tangan['nama_jabatan']) ?></p>
                            <div class="report-signature" style="margin-top:60px;">
                                <p><b><?= esc($tanda_tangan['nama_penandatangan']) ?></b></p>
                                <p>NIP. <?= esc($tanda_tangan['nip_penandatangan']) ?></p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="report-footer mt-3" style="text-align:right;">
                            <p>Tanda tangan belum tersedia.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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
    <div class="modal fade d-md-none" id="filterRekapPegawaiModal" tabindex="-1" aria-labelledby="filterRekapPegawaiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-bottom-sheet">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterRekapPegawaiModalLabel">
                        <i class="bi bi-funnel me-2"></i>Filter Rekap User & Satker
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="<?= base_url('admin/rekap_user_satker') ?>" method="get" id="filterRekapPegawaiFormMobile">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="bulan_mobile" class="form-label">Bulan</label>
                                <select name="bulan" id="bulan_mobile" class="form-select">
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= $i ?>" <?= $filter_bulan == $i ? 'selected' : '' ?>>
                                            <?= strtoupper(getBulanIndo($i)) ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="tahun_mobile" class="form-label">Tahun</label>
                                <select name="tahun" id="tahun_mobile" class="form-select">
                                    <?php foreach ($tahun_tersedia as $tahun): ?>
                                        <option value="<?= $tahun['tahun'] ?>" <?= $filter_tahun == $tahun['tahun'] ? 'selected' : '' ?>>
                                            <?= $tahun['tahun'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('filterRekapPegawaiFormMobile').submit();">
                        <i class="bi bi-funnel me-1"></i>Filter
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
    
    <?php include(APPPATH . 'Views/components/bottom_nav_admin.php'); ?>
</body>

</html>