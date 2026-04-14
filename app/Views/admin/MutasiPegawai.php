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
    <title>Mutasi Pegawai - MASSIPA</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/MutasiPegawai.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="<?= base_url('assets/js/admin/MutasiPegawai.js') ?>"></script>
</head>

<body>
    <?php include(APPPATH . 'Views/components/sidebar_admin.php'); ?>
    <?php include(APPPATH . 'Views/components/navbar_admin.php'); ?>

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

    <div class="main-content">
        <div class="overlay"></div>
        <div class="container-fluid px-0">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Form Mutasi Pegawai</span>
                    <button id="toggleFormMutasi" class="btn btn-outline-primary btn-sm" type="button">
                        <i class="bi bi-chevron-down"></i><span class="d-none d-md-inline ms-1">Tampilkan Form</span>
                    </button>
                </div>
                <div class="card-body" id="formMutasiWrapper" style="display:none;">
                    <form method="POST" action="<?= base_url('admin/prosesMutasiPegawai') ?>">
                        <input type="hidden" name="pegawai_id" value="<?= $pegawai['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label">Nama Pegawai</label>
                            <input type="text" class="form-control" value="<?= esc($pegawai['nama']) ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Satker Baru</label>
                            <select class="form-select" name="satker_id" required>
                                <option value="">Pilih Satker</option>
                                <?php foreach ($list_satker as $satker): ?>
                                    <option value="<?= esc($satker['id']) ?>"><?= esc($satker['nama']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Mutasi</label>
                            <input type="date" class="form-control" name="tanggal_mutasi" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-simpan-mutasi">Simpan Mutasi</button>
                        <a href="<?= base_url('admin/input_pegawai') ?>" class="btn btn-secondary btn-kembali-mutasi">Kembali</a>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header">Riwayat Mutasi Pegawai</div>
                <div class="card-body">
                    <div class="table-responsive d-none d-md-block">
                        <table id="mutasiTable" class="table modern-table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Satker</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1;
                                foreach ($riwayat as $row): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= esc($list_satker[array_search($row['satker_id'], array_column($list_satker, 'id'))]['nama'] ?? '-') ?>
                                        </td>
                                        <td><?= esc($row['tanggal_mulai']) ?></td>
                                        <td><?= $row['tanggal_selesai'] ? esc($row['tanggal_selesai']) : '-' ?></td>
                                        <td>
                                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#editMutasiModal<?= $row['id'] ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <?php if ($no > 1): ?>
                                                <a href="<?= base_url('admin/deleteMutasiPegawai/' . $row['id']) ?>"
                                                    class="btn btn-danger btn-sm btn-hapus-mutasi"
                                                    data-nama="<?= esc($list_satker[array_search($row['satker_id'], array_column($list_satker, 'id'))]['nama'] ?? '-') ?>">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-block d-md-none">
                        <div id="mobileMutasiCards"></div>
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
        <script>
            window.mutasiRiwayatData = <?= json_encode($riwayat) ?>;
            window.mutasiListSatker = <?= json_encode($list_satker) ?>;
            window.baseUrl = '<?= base_url() ?>';
        </script>
        <?php foreach ($riwayat as $row): ?>
            <div class="modal fade" id="editMutasiModal<?= $row['id'] ?>" tabindex="-1"
                aria-labelledby="editMutasiModalLabel<?= $row['id'] ?>" inert>
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editMutasiModalLabel<?= $row['id'] ?>">Edit Mutasi Pegawai</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="<?= base_url('admin/updateMutasiPegawai') ?>">
                            <div class="modal-body">
                                <input type="hidden" name="mutasi_id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="pegawai_id" value="<?= $pegawai['id'] ?>">
                                <div class="mb-3">
                                    <label class="form-label">Satker</label>
                                    <select class="form-select" name="satker_id" required>
                                        <?php foreach ($list_satker as $satker): ?>
                                            <option value="<?= esc($satker['id']) ?>" <?= $row['satker_id'] == $satker['id'] ? 'selected' : '' ?>><?= esc($satker['nama']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Mulai</label>
                                    <input type="date" class="form-control" name="tanggal_mulai"
                                        value="<?= esc($row['tanggal_mulai']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Selesai</label>
                                    <input type="date" class="form-control" name="tanggal_selesai"
                                        value="<?= esc($row['tanggal_selesai']) ?>">
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
        <?php endforeach; ?>
    </div>

    <?php include(APPPATH . 'Views/components/bottom_nav_admin.php'); ?>
</body>

</html>