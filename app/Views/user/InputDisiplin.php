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
    <title>Input Kedisiplinan - MASSIPA</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/user/InputDisiplin.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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
    <?php include(APPPATH . 'Views/components/sidebar_user.php'); ?>

    <?php include(APPPATH . 'Views/components/navbar_user.php'); ?>

    <div class="main-content">
        <div class="container-fluid px-0">
            <?php if (session()->getFlashdata('msg')): ?>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <script>
                    <?php if (session()->getFlashdata('msg_type') === 'success'): ?>
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: <?= json_encode(session()->getFlashdata('msg')) ?>,
                            confirmButtonColor: '#7c3aed',
                            confirmButtonText: 'OK'
                        }).then(function () {
                            window.location.reload();
                        });
                    <?php else: ?>
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: <?= json_encode(session()->getFlashdata('msg')) ?>,
                            confirmButtonColor: '#7c3aed',
                            confirmButtonText: 'OK'
                        });
                    <?php endif; ?>
                </script>
            <?php endif; ?>
            <div class="row mb-0">
                <div class="col-12">
                    <?php
                    $isiPetunjuk = '<div class="row">
                        <div class="col-12 col-md-7 mb-3 mb-md-0">
                            <span class="fw-semibold d-block mb-2" style="color:#5b21b6;">Petunjuk Input Kedisiplinan Tabel</span>
                            <ul class="mb-0 ps-3" style="font-size:0.98em;">
                            <li>Pilih bulan dan tahun untuk menampilkan daftar pegawai.</li>
                                <li>Centang pegawai yang ingin diinput, lalu isi jumlah pelanggaran dan keterangan jika ada.</li>
                            <li>Tekan <b>Simpan</b> untuk menyimpan data pegawai yang dicentang pada halaman ini.</li>
                            <li>Gunakan kolom pencarian untuk mencari nama atau NIP pegawai.</li>
                                <li>Klik ikon <b><i class="fas fa-trash"></i></b> untuk menghapus data pegawai tertentu.</li>
                        </ul>
                </div>
                <div class="col-12 col-md-5">
                            <span class="fw-semibold d-block mb-2" style="color:#5b21b6;">Keterangan Singkatan</span>
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <ul class="mb-0 ps-3" style="line-height:1.7;">
                                    <li>t = Terlambat</li>
                                    <li>tam = Tidak Absen Masuk</li>
                                    <li>pa = Pulang Awal</li>
                                    <li>tap = Tidak Absen Pulang</li>
                                    <li>kti = Keluar Tidak Izin</li>
                                </ul>
                            </div>
                            <div class="col-12 col-md-6">
                                <ul class="mb-0 ps-3" style="line-height:1.7;">
                                    <li>tk = Tidak Masuk Tanpa Ket</li>
                                    <li>tms = Tidak Masuk Sakit</li>
                                    <li>tmk = Tidak Masuk Kerja</li>
                                    <li>BP = Bentuk Pembinaan</li>
                                    <li>Ket = Keterangan</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    </div>';
                    echo view('components/petunjuk', ['isiPetunjuk' => $isiPetunjuk]);
                    ?>
                </div>
            </div>
            <div class="card mb-3 d-none d-md-block">
                <div class="card-header">Filter Periode & Jabatan</div>
                <div class="card-body py-3">
                    <form method="get" action="<?= base_url('user/inputdisiplin') ?>" class="row g-2 align-items-end"
                        id="filterForm">
                        <div class="col-12 col-md-6 col-lg-6">
                            <label for="bulan" class="form-label" style="color:#7c3aed;font-weight:600;">Bulan</label>
                            <select class="form-select" id="bulan" name="bulan" style="width:100%;">
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?= $i ?>" <?= ($filter_bulan == $i) ? 'selected' : '' ?>>
                                        <?= getBulanIndo($i) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6 col-lg-6">
                            <label for="tahun" class="form-label" style="color:#7c3aed;font-weight:600;">Tahun</label>
                            <input type="number" class="form-control" id="tahun" name="tahun"
                                value="<?= $filter_tahun ?>" required style="width:100%;">
                        </div>
                        <div class="w-100"></div>
                        <div class="col-12 col-md-10 col-lg-10">
                            <label for="filter_jabatan" class="form-label" style="color:#7c3aed;font-weight:600;">Jabatan <span style="font-weight:400;font-size:0.9em;">(bisa pilih lebih dari satu)</span></label>
                            <select name="jabatan[]" id="filter_jabatan" class="form-select" multiple size="5" style="min-height:2.5em;">
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
                        <div class="col-12 col-md-2 col-lg-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary filter-btn-custom w-100">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div id="notifDataTerpilih" class="notification-popup" style="display:none;">
                <div class="notification-content">
                    <i class="fas fa-check-circle me-2"></i>
                    <span id="notifTextTerpilih"></span>
                </div>
            </div>
            
            <form id="formKedisiplinanTabel" method="post" action="#">
                <?= csrf_field() ?>
                <input type="hidden" name="bulan" id="inputBulan" value="<?= $filter_bulan ?>">
                <input type="hidden" name="tahun" id="inputTahun" value="<?= $filter_tahun ?>">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Data Kedisiplinan Pegawai</span>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm d-block d-md-none" id="btnPilihSemuaMobile"
                                style="background: #7c3aed; border-color: #7c3aed; color: #fff;" title="Pilih Semua">
                                <i class="fas fa-check-double"></i>
                            </button>
                            <button type="button" class="btn btn-sm d-block d-md-none" id="btnHapusMobile" onclick="clearSavedCheckboxState()"
                                style="background: #7c3aed; border-color: #7c3aed; color: #fff;" title="Hapus Data Tersimpan">
                                <i class="fas fa-trash"></i>
                            </button>
                            <button type="button" class="btn btn-sm d-block d-md-none" style="background: #7c3aed; border-color: #7c3aed; color: #fff;" data-bs-toggle="modal" data-bs-target="#filterDisiplinModal" title="Filter Periode & Jabatan">
                                <i class="bi bi-funnel"></i>
                            </button>
                        </div>
                        <div class="d-flex flex-column flex-md-row gap-2 ms-auto w-100 w-md-auto d-none d-md-flex" style="min-width:400px;max-width:600px;">
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnPilihSemua"
                                    style="white-space:nowrap;">
                                    <input type="checkbox" id="checkAllBox" style="margin-right:6px;vertical-align:middle;">
                                    Pilih Semua
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="clearSavedCheckboxState()"
                                    style="white-space:nowrap;">
                                    <i class="fas fa-trash me-1"></i> Hapus Data Tersimpan
                                </button>
                            </div>
                            <input type="text" id="searchPegawai" class="form-control search-pegawai-input"
                                placeholder="Ketik nama/NIP pegawai..." autocomplete="off">
                        </div>
                    </div>
                    <div class="d-block d-md-none px-3 pb-2">
                        <input type="text" id="searchPegawaiMobile" class="form-control search-pegawai-input"
                            placeholder="Ketik nama/NIP pegawai..." autocomplete="off">
                    </div>
                    <div class="card-body">
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover table-borderless align-middle modern-table"
                                id="tabelKedisiplinanAjax">
                                <thead>
                                    <tr class="desktop-header">
                                        <th style="width:32px;"><input type="checkbox" id="checkAllBoxHeader"></th>
                                        <th style="width:40px;">No</th>
                                        <th style="width:140px;">Nama Pegawai</th>
                                        <th style="width:180px;">NIP</th>
                                        <th style="width:80px;">T</th>
                                        <th style="width:80px;">TAM</th>
                                        <th style="width:80px;">PA</th>
                                        <th style="width:80px;">TAP</th>
                                        <th style="width:80px;">KTI</th>
                                        <th style="width:80px;">TK</th>
                                        <th style="width:80px;">TMS</th>
                                        <th style="width:80px;">TMK</th>
                                        <th style="width:50px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyKedisiplinanAjax">
                                    <tr>
                                        <td colspan="14" class="text-center">Memuat data...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-block d-md-none">
                            <div id="mobileInputDisiplinCards"></div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-primary d-none" id="btnSimpanSemua" style="display: none !important;"><i
                        class="fas fa-save me-1"></i> Simpan</button>
                <button type="button" class="btn btn-primary d-block d-md-none" id="btnSimpanSemuaMobile"><i
                        class="fas fa-save me-1"></i> Simpan</button>
                <button type="button" class="btn btn-primary d-none d-md-block" id="btnSimpanSemuaDesktop"><i
                        class="fas fa-save me-1"></i> Simpan</button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        window.getPegawaiKedisiplinanAjaxUrl = "<?= base_url('user/getPegawaiKedisiplinanAjax') ?>";
    </script>
    <script src="<?= base_url('assets/js/user/InputDisiplin.js') ?>"></script>
    <div class="modal fade d-md-none" id="filterDisiplinModal" tabindex="-1" aria-labelledby="filterDisiplinModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-bottom-sheet">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterDisiplinModalLabel">
                        <i class="fas fa-filter me-2"></i>Filter Periode & Jabatan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="get" action="<?= base_url('user/inputdisiplin') ?>" id="filterFormMobile">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="bulan_mobile" class="form-label" style="color:#7c3aed;font-weight:600;">Bulan</label>
                                <select class="form-select" id="bulan_mobile" name="bulan" style="width:100%;">
                                    <?php for ($i = 1; $i <= 12; $i++): ?>
                                        <option value="<?= $i ?>" <?= ($filter_bulan == $i) ? 'selected' : '' ?>>
                                            <?= getBulanIndo($i) ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="tahun_mobile" class="form-label" style="color:#7c3aed;font-weight:600;">Tahun</label>
                                <input type="number" class="form-control" id="tahun_mobile" name="tahun"
                                    value="<?= $filter_tahun ?>" required style="width:100%;">
                            </div>
                            <div class="col-12">
                                <label for="filter_jabatan_mobile" class="form-label" style="color:#7c3aed;font-weight:600;">Jabatan <span style="font-weight:400;font-size:0.9em;">(bisa pilih lebih dari satu)</span></label>
                                <select name="jabatan[]" id="filter_jabatan_mobile" class="form-select" multiple size="5" style="min-height:2.5em;">
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
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#filter_jabatan').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih jabatan...',
                    allowClear: false,
                closeOnSelect: false,
                width: '100%'
            }).on('change', function(e) {
                e.preventDefault();
                return false;
                });
                
                $('#filter_jabatan_mobile').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Pilih jabatan...',
                    allowClear: false,
                    closeOnSelect: false,
                    width: '100%',
                    dropdownParent: $('#filterDisiplinModal')
                });
            
            $('#btnFilterMobile').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                let bulan = $('#bulan_mobile').val();
                let tahun = $('#tahun_mobile').val();
                let jabatan = $('#filter_jabatan_mobile').val() || [];

                $('#inputBulan').val(bulan);
                $('#inputTahun').val(tahun);

                var modalElement = document.getElementById('filterDisiplinModal');
                if (modalElement) {
                    var modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    } else {
                        $('#filterDisiplinModal').modal('hide');
                    }
                }

                if (typeof loadTabelKedisiplinanAjax === 'function') {
                    loadTabelKedisiplinanAjax(bulan, tahun, jabatan);
                } else {
                    console.warn('loadTabelKedisiplinanAjax not found, reloading page...');
                    let url = '<?= base_url('user/inputdisiplin') ?>?bulan=' + bulan + '&tahun=' + tahun;
                    if (jabatan && jabatan.length > 0) {
                        jabatan.forEach(function(jab) {
                            url += '&jabatan[]=' + encodeURIComponent(jab);
                        });
                    }
                    window.location.href = url;
                }
                
                return false;
            });
            
            $('#filterFormMobile').on('submit', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#btnFilterMobile').trigger('click');
                return false;
            });
            
            $('#btnPilihSemuaMobile').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#btnPilihSemua').trigger('click');
                return false;
            });
            
            $('#searchPegawaiMobile').on('input', function() {
                let val = $(this).val();
                $('#searchPegawai').val(val).trigger('input');
            });
            
            $('#searchPegawai').on('input', function() {
                let val = $(this).val();
                $('#searchPegawaiMobile').val(val);
            });

            // Pastikan Select2 terinisialisasi dengan benar saat modal mobile terbuka
            $('#filterDisiplinModal').on('shown.bs.modal', function() {
                // Destroy Select2 jika sudah ada untuk menghindari duplikasi
                if ($('#filter_jabatan_mobile').hasClass('select2-hidden-accessible')) {
                    $('#filter_jabatan_mobile').select2('destroy');
                }
                
                // Inisialisasi ulang Select2 untuk filter jabatan mobile
                $('#filter_jabatan_mobile').select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Pilih jabatan...',
                    allowClear: false,
                    closeOnSelect: false,
                    width: '100%',
                    dropdownParent: $('#filterDisiplinModal')
                });
            });
        });
    </script>
    
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
