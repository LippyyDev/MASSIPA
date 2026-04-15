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
    <title>Status Disiplin - MASSIPA</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">

    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
    <link rel="preload" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" as="style">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/user/StatusDisiplinPegawai.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
        <div class="container-fluid">
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
                        <li><i class="bi bi-calendar"></i> Pilih tahun di pojok kanan atas untuk melihat rekap kedisiplinan. Data akan otomatis ditampilkan setelah memilih tahun (tanpa perlu klik tombol).</li>
                        <li><i class="bi bi-people"></i> Tabel menampilkan seluruh pegawai aktif pada tahun yang dipilih.</li>
                        <li><i class="bi bi-check2"></i> <b>Tanda centang (✓)</b> artinya pegawai telah mengisi data kedisiplinan pada bulan tersebut.</li>
                        <li><i class="bi bi-dash"></i> <b>Tanda "-"</b> artinya tidak ada data kedisiplinan pada bulan tersebut.</li>
                        <li><i class="bi bi-arrow-left-right"></i> Jika pegawai mutasi, keterangan mutasi akan muncul di kolom bulan terkait.</li>
                        <li><i class="bi bi-info-circle"></i> Pegawai yang sudah pensiun tidak akan muncul di rekap ini. Data riwayat dan kedisiplinan sebelum pensiun tetap bisa dilihat di tahun-tahun sebelumnya.</li>
                    </ul>';
                    echo view('components/petunjuk', ['isiPetunjuk' => $isiPetunjuk]);
                    ?>
                </div>
            </div>

            <div class="card mb-1">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Data Kedisiplinan Tahun <span id="tahunDisplay"><?= htmlspecialchars($tahun_dipilih); ?></span></span>
                    <form action="<?= base_url('user/rekap_bulanan') ?>" method="GET" id="formTahun" class="d-flex align-items-center" style="gap:8px; min-width:120px;">
                        <select name="tahun" id="tahun" class="form-select form-select-sm" style="max-width:120px;">
                            <?php foreach ($daftar_tahun as $tahun): ?>
                                <option value="<?= esc($tahun) ?>" <?= $tahun == $tahun_dipilih ? 'selected' : '' ?>>
                                    <?= esc($tahun) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive d-none d-lg-block status-table-desktop" style="min-height: 400px;">
                        <table id="rekapTable" class="table table-hover table-borderless align-middle modern-table" style="width: 100%; min-width: 1050px;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th style="width: 200px;">Nama / NIP</th>
                                    <th style="width: 200px;">Jabatan</th>
                                    <?php $nama_bulan_singkat = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']; ?>
                                    <?php foreach ($nama_bulan_singkat as $nama): ?>
                                        <th class="text-center" style="width: 60px;"><?= esc($nama); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="15" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <div class="mt-2">Memuat data...</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-block d-lg-none">
                        <div class="mb-3">
                            <input type="text" id="mobileSearch" class="form-control" placeholder="Cari pegawai (nama/NIP)...">
                        </div>
                        
                        <div id="mobileCardsContainer">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div class="mt-2">Memuat data...</div>
                            </div>
                        </div>
                        
                        <div id="mobilePegawaiPagination" style="display:none;">
                            <ul class="pagination mb-0" style="justify-content: center; gap: 8px;">
                                <li class="page-item">
                                    <button class="page-link" id="mobilePegawaiPrev" type="button">&lt;</button>
                                </li>
                                <li id="mobilePegawaiPageNumbers" style="display: contents;"></li>
                                <li class="page-item">
                                    <button class="page-link" id="mobilePegawaiNext" type="button">&gt;</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        window.rekapBulananAjaxUrl = "<?= base_url('user/getRekapBulananAjax') ?>";
    </script>
    <script src="<?= base_url('assets/js/user/StatusDisiplinPegawai.js') ?>"></script>
    <?php include(APPPATH . 'Views/components/bottom_nav_user.php'); ?>
</body>

</html>