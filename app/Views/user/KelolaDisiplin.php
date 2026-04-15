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
    <meta name="color-scheme" content="dark light">
    <title>Input Kedisiplinan - MASSIPA</title>
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
    <link rel="stylesheet" href="<?= base_url('assets/css/user/KelolaDisiplin.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body>
    <?php include(APPPATH . 'Views/components/sidebar_user.php'); ?>

    <?php include(APPPATH . 'Views/components/navbar_user.php'); ?>

    <div class="main-content">
        <div class="container-fluid px-0">
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                <?php if (session()->getFlashdata('msg')): ?>
                    Swal.fire({
                        icon: '<?= session()->getFlashdata('msg_type') === 'success' ? 'success' : 'error' ?>',
                        title: '<?= session()->getFlashdata('msg_type') === 'success' ? 'Berhasil' : 'Gagal' ?>',
                        text: <?= json_encode(session()->getFlashdata('msg')) ?>,
                        timer: 2500,
                        showConfirmButton: false
                    });
                <?php endif; ?>
            </script>

            <div class="row mb-0">
                <div class="col-12">
                    <?php
                    $isiPetunjuk = '<ul class="mb-0 ps-3" style="font-size:0.98em;">
                        <li><i class="bi bi-calendar"></i> Pilih tahun untuk melihat rekap data kedisiplinan per bulan.</li>
                        <li><i class="bi bi-plus-square"></i> Klik <b>Input Data Kedisiplinan</b> untuk menambah atau mengedit data pegawai pada bulan tertentu.</li>
                        <li><i class="bi bi-pencil-square"></i> Gunakan tombol <b>Edit</b> untuk mengubah data, dan <b><i class=\'fas fa-eye\'></i></b> untuk melihat detail laporan.</li>
                        <li><i class="bi bi-trash"></i> Tekan tombol <b>Hapus</b> jika ingin menghapus seluruh data pada bulan tertentu.</li>
                    </ul>';
                    echo view('components/petunjuk', ['isiPetunjuk' => $isiPetunjuk]);
                    ?>
                </div>
            </div>

            <div class="card mb-3 d-none d-md-block">
                <div class="card-header">
                    <span>Filter Data Kedisiplinan</span>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?= base_url('user/kelola_disiplin') ?>" class="row g-3 mb-0" id="filterDisiplinForm">
                        <div class="col-md-3">
                            <label for="tahun" class="form-label">Tahun</label>
                            <select class="form-select" id="tahun" name="tahun" onchange="this.form.submit()">
                                <?php foreach ($tahun_tersedia as $tahun): ?>
                                    <option value="<?= $tahun; ?>" <?= (isset($filter_tahun) && $filter_tahun == $tahun) ? "selected" : ""; ?>>
                                        <?= $tahun; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <span>Rekap Periode Disiplin</span>
                    <div class="d-flex gap-2 align-items-center ms-auto">
                        <a href="<?= base_url('user/inputdisiplin') ?>"
                        class="btn btn-outline-ungu-custom d-flex align-items-center gap-2" style="font-weight:600; margin-right: 0;" title="Tambah Data Kedisiplinan">
                        <i class="bi bi-plus-lg"></i> <span class="d-none d-md-inline">Tambah Data Kedisiplinan</span>
                    </a>
                        <button type="button" class="btn btn-sm d-block d-md-none" style="background: #7c3aed; border-color: #7c3aed; color: #fff; margin-right: 0;" data-bs-toggle="modal" data-bs-target="#filterDisiplinModal" title="Filter Disiplin">
                            <i class="bi bi-funnel"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive d-none d-md-block">
                        <table id="rekapPeriodeTable"
                            class="table table-hover table-borderless align-middle modern-table mb-0">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Bulan/Tahun</th>
                                    <th data-bs-toggle="tooltip" title="Terlambat">t</th>
                                    <th data-bs-toggle="tooltip" title="Tidak Absen Masuk">tam</th>
                                    <th data-bs-toggle="tooltip" title="Pulang Awal">pa</th>
                                    <th data-bs-toggle="tooltip" title="Tidak Absen Pulang">tap</th>
                                    <th data-bs-toggle="tooltip" title="Keluar Tidak Izin">kti</th>
                                    <th data-bs-toggle="tooltip" title="Tidak Masuk Tanpa Ket">tk</th>
                                    <th data-bs-toggle="tooltip" title="Tidak Masuk Sakit">tms</th>
                                    <th data-bs-toggle="tooltip" title="Tidak Masuk Kerja">tmk</th>
                                    <th data-bs-toggle="tooltip" title="Bentuk Pembinaan" style="width:130px;">BP</th>
                                    <th data-bs-toggle="tooltip" title="Keterangan" style="width:130px;">Ket</th>
                                    <th style="width:70px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($rekap_periode)): ?>
                                    <?php $no = 1;
                                    foreach ($rekap_periode as $row): ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= getBulanIndo($row['bulan']) . ' ' . $row['tahun']; ?></td>
                                            <td><?= $row['terlambat']; ?></td>
                                            <td><?= $row['tidak_absen_masuk']; ?></td>
                                            <td><?= $row['pulang_awal']; ?></td>
                                            <td><?= $row['tidak_absen_pulang']; ?></td>
                                            <td><?= $row['keluar_tidak_izin']; ?></td>
                                            <td><?= $row['tidak_masuk_tanpa_ket']; ?></td>
                                            <td><?= $row['tidak_masuk_sakit']; ?></td>
                                            <td><?= $row['tidak_masuk_kerja']; ?></td>
                                            <td><?= !empty($row['bentuk_pembinaan']) ? esc($row['bentuk_pembinaan']) : '-' ?>
                                            </td>
                                            <td><?= !empty($row['keterangan']) ? esc($row['keterangan']) : '-' ?></td>
                                            <td>
                                                <div class="d-flex flex-row align-items-center gap-1 justify-content-center">
                                                    <a href="<?= base_url('user/inputdisiplin?bulan=' . $row['bulan'] . '&tahun=' . $row['tahun']) ?>"
                                                        class="btn btn-warning btn-sm" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="<?= base_url('user/rekaplaporandisiplin?bulan=' . $row['bulan'] . '&tahun=' . $row['tahun']) ?>"
                                                        class="btn btn-info btn-sm" title="Lihat Laporan">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <form method="POST"
                                                        action="<?= base_url('user/hapus_kedisiplinan_periode') ?>"
                                                        class="d-inline form-hapus-periode">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="bulan" value="<?= $row['bulan'] ?>">
                                                        <input type="hidden" name="tahun" value="<?= $row['tahun'] ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm btn-hapus-periode"
                                                            title="Hapus Data"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-block d-md-none">
                        <div class="mb-2 px-0">
                            <input type="text" id="search_mobile_user_disiplin" class="form-control"
                                placeholder="Cari periode..." autocomplete="off">
                        </div>
                        <div id="mobileUserDisiplinCards"></div>
                        <div id="mobileUserPagination" style="display:none;">
                            <ul class="pagination mobile-pagination-purple mb-0" style="justify-content: center; gap: 8px;">
                                <li class="page-item">
                                    <button class="page-link" id="mobileUserPrev" type="button">&lt;</button>
                                </li>
                                <span id="mobileUserPageNumbers"></span>
                                <li class="page-item">
                                    <button class="page-link" id="mobileUserNext" type="button">&gt;</button>
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
                    <form method="GET" action="<?= base_url('user/kelola_disiplin') ?>" id="filterDisiplinFormMobile">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="tahun_mobile" class="form-label">Tahun</label>
                                <select class="form-select filter-mobile" id="tahun_mobile" name="tahun" data-filter-type="tahun">
                                    <?php foreach ($tahun_tersedia as $tahun): ?>
                                        <option value="<?= $tahun; ?>" <?= (isset($filter_tahun) && $filter_tahun == $tahun) ? "selected" : ""; ?>>
                                            <?= $tahun; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= base_url('assets/js/user/KelolaDisiplin.js') ?>"></script>

    <script>
        function fixAriaHidden() {
            $('.modal').each(function() {
                var modal = $(this);
                modal.removeAttr('aria-hidden');
                modal.find('[tabindex="-1"]').removeAttr('tabindex');
                modal.find('input:not(:focus), select:not(:focus), textarea:not(:focus)').blur();
            });
            if (!$(document.activeElement).is('input, select, textarea')) {
                $(document.activeElement).blur();
            }
        }
        
        fixAriaHidden();
        setInterval(fixAriaHidden, 100);
        
        $(document).on('hidden.bs.modal', function (e) {
            var modal = $(e.target);
            modal.removeAttr('aria-hidden');
            modal.find('[tabindex="-1"]').removeAttr('tabindex');
            modal.find('input:not(:focus), select:not(:focus), textarea:not(:focus)').blur();
            if (!$(document.activeElement).is('input, select, textarea')) {
                $(document.activeElement).blur();
            }
            setTimeout(function() {
                fixAriaHidden();
            }, 200);
        });
        
        $(document).on('show.bs.modal', function (e) {
            var modal = $(e.target);
            modal.removeAttr('aria-hidden');
        });
        
        $('#filterDisiplinModal').on('hidden.bs.modal', function (e) {
            var modal = $(this);
            modal.removeAttr('aria-hidden');
            modal.find('[tabindex="-1"]').removeAttr('tabindex');
            modal.find('input:not(:focus), select:not(:focus), textarea:not(:focus)').blur();
            setTimeout(function() {
                modal.removeAttr('aria-hidden');
                fixAriaHidden();
            }, 100);
        });
        
        $('#filterDisiplinModal').on('show.bs.modal', function (e) {
            var modal = $(this);
            modal.removeAttr('aria-hidden');
            var desktopValue = $("#tahun").val();
            $("#tahun_mobile").val(desktopValue || $("#tahun_mobile").val());
        });
        
        $(document).on('click', '[data-bs-dismiss="modal"]', function() {
            var modal = $(this).closest('.modal');
            setTimeout(function() {
                modal.removeAttr('aria-hidden');
                modal.find('[tabindex="-1"]').removeAttr('tabindex');
                modal.find('input:not(:focus), select:not(:focus), textarea:not(:focus)').blur();
                if (!$(document.activeElement).is('input, select, textarea')) {
                    $(document.activeElement).blur();
                }
                fixAriaHidden();
            }, 100);
        });
        
        $(document).on('click', '.modal-backdrop', function() {
            var modal = $('.modal.show');
            setTimeout(function() {
                modal.removeAttr('aria-hidden');
                modal.find('[tabindex="-1"]').removeAttr('tabindex');
                modal.find('input:not(:focus), select:not(:focus), textarea:not(:focus)').blur();
                if (!$(document.activeElement).is('input, select, textarea')) {
                    $(document.activeElement).blur();
                }
                fixAriaHidden();
            }, 100);
        });
        
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                var modal = $('.modal.show');
                if (modal.length > 0) {
                    setTimeout(function() {
                        modal.removeAttr('aria-hidden');
                        modal.find('[tabindex="-1"]').removeAttr('tabindex');
                        modal.find('input:not(:focus), select:not(:focus), textarea:not(:focus)').blur();
                        if (!$(document.activeElement).is('input, select, textarea')) {
                            $(document.activeElement).blur();
                        }
                        fixAriaHidden();
                    }, 100);
                }
            }
        });
        
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
        
        $("#tahun_mobile").on("change", function () {
            $("#filterDisiplinFormMobile").submit();
        });
        
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.getElementById("sidebar");
            const sidebarToggle = document.getElementById("sidebarToggle");
            const overlay = document.querySelector(".overlay");
            const mainContent = document.querySelector(".main-content");

            function openSidebar() {
                if (sidebar) sidebar.classList.add("active");
                if (overlay) overlay.classList.add("active");
            }
            function closeSidebarMobile() {
                if (sidebar) sidebar.classList.remove("active");
                if (overlay) overlay.classList.remove("active");
            }
            if (sidebarToggle) {
                sidebarToggle.addEventListener("click", function () {
                    if (sidebar && sidebar.classList.contains("active")) {
                        closeSidebarMobile();
                    } else {
                        openSidebar();
                    }
                });
            }
            if (overlay) {
                overlay.addEventListener("click", closeSidebarMobile);
            }
            function handleResize() {
                if (window.innerWidth <= 991) {
                    if (mainContent) mainContent.style.marginLeft = "0";
                } else {
                    if (mainContent) mainContent.style.marginLeft = "240px";
                    closeSidebarMobile();
                }
            }
            window.addEventListener('resize', handleResize);
            handleResize();
        });
    </script>
    <?php include(APPPATH . 'Views/components/bottom_nav_user.php'); ?>
</body>

</html>