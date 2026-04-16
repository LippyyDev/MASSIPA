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
    <title>Kelola Satker - MASSIPA</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/KelolaSatker.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

<body data-flash-msg="<?= esc(session()->getFlashdata('msg')) ?>"
    data-flash-type="<?= esc(session()->getFlashdata('msg_type')) ?>"
    data-flash-success="<?= esc(session()->getFlashdata('success')) ?>"
    data-flash-error="<?= esc(session()->getFlashdata('error')) ?>">
    <?php include(APPPATH . 'Views/components/sidebar_admin.php'); ?>

    <?php include(APPPATH . 'Views/components/navbar_admin.php'); ?>

    <div class="main-content">
        <div class="overlay"></div>
        <div class="container-fluid px-0">
            <div class="row mb-0">
                <div class="col-12">
                    <?php
                    $isiPetunjuk = '<ul class="mb-0 ps-3" style="font-size:0.98em;">
                        <li><i class="bi bi-plus-lg"></i> Gunakan tombol <b>Tambah Satker</b> untuk menambah data satuan kerja baru.</li>
                        <li><i class="bi bi-pencil-square"></i> Gunakan tombol <b>Edit</b> untuk mengubah data satker, dan <b>Hapus</b> untuk menghapus satker.</li>
                        <li><i class="bi bi-geo-alt"></i> Pastikan nama dan alamat satker diisi dengan benar.</li>
                        <li><i class="bi bi-exclamation-triangle"></i> Data satker yang sudah digunakan user tidak dapat dihapus.</li>
                    </ul>';
                    echo view('components/petunjuk', ['isiPetunjuk' => $isiPetunjuk]);
                    ?>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Daftar Satker</span>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalSatker"><i class="bi bi-plus-lg me-1"></i> Tambah Satker</button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive d-none d-md-block">
                            <table id="satkerTable"
                                class="table table-hover table-borderless align-middle modern-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th style="width:40%;">Nama Satker</th>
                                        <th style="width:40%;">Alamat</th>
                                        <th style="width:1%; white-space:nowrap;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1;
                                    foreach ($list_satker as $satker): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td style="width:40%;"><?= esc($satker['nama']) ?></td>
                                            <td style="width:40%;"><?= esc($satker['alamat']) ?></td>
                                            <td class="d-flex gap-2" style="width:1%; white-space:nowrap;">
                                                <button type="button" class="btn btn-warning btn-action"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalSatkerEdit<?= $satker['id'] ?>"><i
                                                        class="bi bi-pencil-square"></i> <span class="d-none d-xl-inline">Edit</span></button>
                                                <a href="<?= base_url('admin/hapusSatker/' . $satker['id']) ?>"
                                                    class="btn btn-danger btn-action btn-delete-satker"
                                                    data-nama="<?= esc($satker['nama']) ?>"><i class="bi bi-trash"></i> <span class="d-none d-xl-inline">Hapus</span></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-block d-md-none">
                            <?php $no = 1;
                            foreach ($list_satker as $satker): ?>
                                <div class="border rounded mb-3 p-3 shadow-sm" style="background:#fff;">
                                    <div class="fw-bold mb-1">No. <?= $no++; ?> - <?= esc($satker['nama']) ?></div>
                                    <div><b>Alamat:</b> <?= esc($satker['alamat']) ?></div>
                                    <div class="mt-2 d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#modalSatkerEdit<?= $satker['id'] ?>"><i
                                                class="bi bi-pencil-square"></i> Edit</button>
                                        <a href="<?= base_url('admin/hapusSatker/' . $satker['id']) ?>"
                                            class="btn btn-danger btn-sm btn-delete-satker"
                                            data-nama="<?= esc($satker['nama']) ?>"><i class="bi bi-trash"></i> Hapus</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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
    </div>
    <div class="modal fade" id="modalSatker" tabindex="-1" aria-labelledby="modalSatkerLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?= base_url('admin/simpanSatker') ?>">
                    <?= csrf_field() ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalSatkerLabel">Tambah Satker</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Satker</label>
                            <input type="text" class="form-control" name="nama" autocomplete="off" oninput="this.value = this.value.replace(/[0-9]/g, '')" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat Satker</label>
                            <textarea class="form-control" name="alamat" rows="2" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php foreach ($list_satker as $satker): ?>
        <div class="modal fade" id="modalSatkerEdit<?= $satker['id'] ?>" tabindex="-1"
            aria-labelledby="modalSatkerEditLabel<?= $satker['id'] ?>">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="<?= base_url('admin/simpanSatker') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $satker['id'] ?>">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalSatkerEditLabel<?= $satker['id'] ?>">
                                Edit Satker</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Nama Satker</label>
                                <input type="text" class="form-control" name="nama" value="<?= esc($satker['nama']) ?>"
                                    autocomplete="off" oninput="this.value = this.value.replace(/[0-9]/g, '')" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat Satker</label>
                                <textarea class="form-control" name="alamat" rows="2"
                                    required><?= esc($satker['alamat']) ?></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    <script src="<?= base_url('assets/js/admin/KelolaSatker.js') ?>"></script>
    <?php include(APPPATH . 'Views/components/bottom_nav_admin.php'); ?>
</body>

</html>