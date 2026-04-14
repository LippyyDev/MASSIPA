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
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/KelolaHukumanDisiplin.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
    <script>
        window.SEARCH_PEGAWAI_URL = '<?= base_url('admin/searchPegawaiAjax') ?>';
    </script>
    <script src="<?= base_url('assets/js/admin/KelolaHukumanDisiplin.js') ?>" defer></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
</head>

<body data-flash-msg="<?= esc(session()->getFlashdata('msg')) ?>"
    data-flash-type="<?= esc(session()->getFlashdata('msg_type')) ?>">
    <?php include(APPPATH . 'Views/components/sidebar_admin.php'); ?>
    <?php include(APPPATH . 'Views/components/navbar_admin.php'); ?>
    <div class="main-content">
        <div class="overlay"></div>
        <div class="container-fluid px-0">
            <div class="row mb-0">
                <div class="col-12">
                    <?php
                    $isiPetunjuk = '<ul class="mb-0 ps-3" style="font-size:0.98em;">
                    <li><i class="bi bi-person-badge"></i> Pilih pegawai, jabatan otomatis terisi.</li>
                    <li><i class="bi bi-file-earmark"></i> No SK wajib diisi, file SK PDF opsional.</li>
                    <li><i class="bi bi-calendar"></i> Tanggal mulai dan berakhir wajib diisi.</li>
                    <li><i class="bi bi-exclamation-diamond"></i> Isi hukuman disiplin, peraturan yang dilanggar, dan keterangan jika ada.</li>
                    <li><i class="bi bi-download"></i> File SK dapat diunduh jika sudah diupload.</li>
                    <li><i class="bi bi-pencil-square"></i> Gunakan tombol <b>Edit</b> untuk mengubah data, <b>Hapus</b> untuk menghapus.</li>
                    <li><i class="bi bi-printer"></i> Gunakan tombol <b>Export PDF/Word</b> untuk ekspor data (nama pegawai otomatis disensor).</li>
                </ul>';
                    echo view('components/petunjuk', ['isiPetunjuk' => $isiPetunjuk]);
                    ?>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center"
                    id="toggleTambahHukuman" style="cursor:pointer;">
                    <span>Tambah Hukuman Disiplin</span>
                    <i class="bi bi-chevron-down ms-2" id="chevronTambahHukuman"></i>
                </div>
                <div class="collapse-form" style="display:none;">
                    <div class="card-body">
                        <form action="<?= base_url('admin/addHukumanDisiplin') ?>" method="post"
                            enctype="multipart/form-data">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Pegawai <span class="text-muted"
                                            style="font-weight:400;font-size:0.95em;">(Ketik nama/NIP untuk
                                            mencari)</span></label>
                                    <div class="position-relative">
                                        <input type="text" id="pegawai_search" class="form-control"
                                            placeholder="Cari nama/NIP pegawai..." autocomplete="off">
                                        <input type="hidden" name="pegawai_id" id="pegawai_id" required>
                                        <div id="pegawai_results"
                                            class="position-absolute w-100 bg-white border rounded shadow-sm"
                                            style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Jabatan</label>
                                    <input type="text" name="jabatan" id="jabatan" class="form-control" readonly
                                        required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">No SK</label>
                                    <input type="text" name="no_sk" class="form-control" required>
                                </div>
                            </div>
                            <div class="row g-3 align-items-end mt-1">
                                <div class="col-md-4">
                                    <label class="form-label">Upload File SK <span class="text-muted" style="font-size:0.88em;">(PDF, maks. 1MB, opsional)</span></label>
                                    <input type="file" name="file_sk" id="file_sk" class="form-control" accept="application/pdf">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tgl Mulai</label>
                                    <input type="date" name="tanggal_mulai" class="form-control" required>
                                </div>
                                <div class="col-md-4">
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
                                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>
                                        Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php
            $daftar_pengajuan = array_filter($list_hukuman, function ($row) {
                return $row['status'] === 'pending';
            });
            $daftar_hukuman = array_filter($list_hukuman, function ($row) {
                $current_user_id = session()->get('user_id');
                return $row['status'] !== 'pending' ||
                    ($row['status'] === 'pending' && $row['user_id'] == $current_user_id);
            });
            ?>
            <div class="card mb-3">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center"
                    id="togglePengajuanHukuman" style="cursor:pointer;">
                    <span></i>Daftar Pengajuan Hukuman Disiplin</span>
                    <i class="bi bi-chevron-down ms-2" id="chevronPengajuanHukuman"></i>
                </div>
                <div class="collapse-pengajuan" style="display:none;">
                    <div class="card-body">
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover table-borderless align-middle modern-table mb-0">
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
                                        <th>Diajukan Oleh</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($daftar_pengajuan)): ?>
                                        <?php $userModel = new \App\Models\UserModel();
                                        $no = 1;
                                        foreach ($daftar_pengajuan as $row): ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= esc($row['nama']) ?></td>
                                                <td><?= esc($row['jabatan']) ?></td>
                                                <td>
                                                    <?php if (!empty($row['file_sk'])): ?>
                                                        <a href="<?= base_url('admin/kelola_hukuman_disiplin/getFile/' . urlencode($row['file_sk'])) ?>"
                                                            target="_blank" title="Lihat File SK"
                                                            class="fw-semibold" style="color:inherit;text-decoration:none;"><?= esc($row['no_sk']) ?></a>
                                                        <a href="<?= base_url('admin/kelola_hukuman_disiplin/getFile/' . urlencode($row['file_sk'])) ?>"
                                                            target="_blank" title="Lihat File SK" class="ms-2"><i
                                                                class="bi bi-file-earmark-pdf-fill text-danger"></i></a>
                                                    <?php else: ?>
                                                        <?= esc($row['no_sk']) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= date('d-m-Y', strtotime($row['tanggal_mulai'])) . ' s/d ' . date('d-m-Y', strtotime($row['tanggal_berakhir'])) ?>
                                                </td>
                                                <td><?= esc($row['hukuman_dijatuhkan']) ?></td>
                                                <td><?= esc($row['peraturan_dilanggar']) ?></td>
                                                <td><?= esc($row['keterangan']) ?></td>
                                                <td><?php $user_pengaju = $row['user_id'] ? $userModel->find($row['user_id']) : null;
                                                echo $user_pengaju ? esc($user_pengaju['nama_lengkap'] ?? $user_pengaju['username']) : '-'; ?>
                                                </td>
                                                <td><span class="badge bg-warning text-dark">Pending</span></td>
                                                <td>
                                                    <?php if (!empty($row['file_sk'])): ?>
                                                        <a href="<?= base_url('admin/kelola_hukuman_disiplin/getFile/' . urlencode($row['file_sk'])) ?>"
                                                            target="_blank" title="Lihat File SK"
                                                            class="btn btn-info btn-sm btn-action me-1"><i
                                                                class="bi bi-eye"></i></a>
                                                    <?php endif; ?>
                                                    <a href="<?= base_url('admin/approveHukumanDisiplin/' . $row['id']) ?>"
                                                        class="btn btn-success btn-sm btn-action btn-approve"
                                                        data-id="<?= $row['id'] ?>" title="Approve"><i
                                                            class="bi bi-check-circle"></i></a>
                                                    <a href="<?= base_url('admin/rejectHukumanDisiplin/' . $row['id']) ?>"
                                                        class="btn btn-danger btn-sm btn-action btn-reject"
                                                        data-id="<?= $row['id'] ?>" title="Reject"><i
                                                            class="bi bi-x-circle"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="11" class="text-center py-3">Tidak ada pengajuan hukuman disiplin
                                                yang pending.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-block d-md-none">
                            <?php if (!empty($daftar_pengajuan)): ?>
                                <?php $userModel = new \App\Models\UserModel();
                                $no = 1;
                                foreach ($daftar_pengajuan as $row): ?>
                                    <div class="border rounded p-3 mb-3 mobile-pengajuan-card">
                                        <div class="fw-bold mb-1 card-title d-flex justify-content-between align-items-center">
                                            <span>No. <?= $no++ ?> - <?= esc($row['nama']) ?></span>
                                            <span class="badge bg-warning text-dark">PENDING</span>
                                        </div>
                                        <div class="small mb-2 card-content">
                                            <div class="card-row"><span class="card-label"
                                                    style="font-weight: bold; display: inline-block; min-width: 80px;">Jabatan:</span>
                                                <span class="card-value"><?= esc($row['jabatan']) ?></span></div>
                                            <div class="card-row"><span class="card-label"
                                                    style="font-weight: bold; display: inline-block; min-width: 80px;">No
                                                    SK:</span> <span class="card-value">
                                                    <?php if (!empty($row['file_sk'])): ?>
                                                        <a href="<?= base_url('admin/kelola_hukuman_disiplin/getFile/' . urlencode($row['file_sk'])) ?>"
                                                            target="_blank"
                                                            style="color:inherit;text-decoration:none;"><?= esc($row['no_sk']) ?></a>
                                                        <a href="<?= base_url('admin/kelola_hukuman_disiplin/getFile/' . urlencode($row['file_sk'])) ?>"
                                                            target="_blank"><i
                                                                class="bi bi-file-earmark-pdf-fill text-danger"></i></a>
                                                    <?php else: ?>
                                                        <?= esc($row['no_sk']) ?>
                                                    <?php endif; ?>
                                                </span></div>
                                            <div class="card-row"><span class="card-label"
                                                    style="font-weight: bold; display: inline-block; min-width: 80px;">Periode:</span>
                                                <span
                                                    class="card-value"><?= date('d-m-Y', strtotime($row['tanggal_mulai'])) . ' s/d ' . date('d-m-Y', strtotime($row['tanggal_berakhir'])) ?></span>
                                            </div>
                                            <div class="card-row"><span class="card-label"
                                                    style="font-weight: bold; display: inline-block; min-width: 80px;">Hukuman:</span>
                                                <span class="card-value"><?= esc($row['hukuman_dijatuhkan']) ?></span></div>
                                            <div class="card-row"><span class="card-label"
                                                    style="font-weight: bold; display: inline-block; min-width: 80px;">Peraturan:</span>
                                                <span class="card-value"><?= esc($row['peraturan_dilanggar']) ?></span></div>
                                            <div class="card-row"><span class="card-label"
                                                    style="font-weight: bold; display: inline-block; min-width: 80px;">Keterangan:</span>
                                                <span class="card-value"><?= esc($row['keterangan']) ?></span></div>
                                            <div class="card-row"><span class="card-label"
                                                    style="font-weight: bold; display: inline-block; min-width: 80px;">Diajukan
                                                    Oleh:</span> <span
                                                    class="card-value"><?php $user_pengaju = $row['user_id'] ? $userModel->find($row['user_id']) : null;
                                                    echo $user_pengaju ? esc($user_pengaju['nama_lengkap'] ?? $user_pengaju['username']) : '-'; ?></span>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php if (!empty($row['file_sk'])): ?>
                                                <a href="<?= base_url('admin/kelola_hukuman_disiplin/getFile/' . urlencode($row['file_sk'])) ?>" target="_blank"
                                                    class="btn btn-info btn-sm btn-action" title="Lihat File"><i class="bi bi-eye"></i></a>
                                            <?php endif; ?>
                                            <a href="<?= base_url('admin/approveHukumanDisiplin/' . $row['id']) ?>"
                                                class="btn btn-success btn-sm btn-action btn-approve" data-id="<?= $row['id'] ?>" title="Approve">
                                                <i class="bi bi-check-circle"></i>
                                            </a>
                                            <a href="<?= base_url('admin/rejectHukumanDisiplin/' . $row['id']) ?>"
                                                class="btn btn-danger btn-sm btn-action btn-reject" data-id="<?= $row['id'] ?>" title="Reject">
                                                <i class="bi bi-x-circle"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-3">Tidak ada pengajuan hukuman disiplin yang pending.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                    <span></i>Daftar Hukuman Disiplin</span>
                    <div class="d-flex align-items-center">
                        <div class="form-check form-check-inline me-2">
                            <input class="form-check-input" type="checkbox" id="exportPublic" value="1">
                            <label class="form-check-label" for="exportPublic">Publik</label>
                        </div>
                        <a href="<?= base_url('admin/exportHukumanDisiplinPdf') ?>" id="exportPdfBtn"
                            class="btn btn-danger btn-sm me-2" aria-label="Export PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                        <a href="<?= base_url('admin/exportHukumanDisiplinWord') ?>" id="exportWordBtn"
                            class="btn btn-primary btn-sm" aria-label="Export Word"><i class="bi bi-file-earmark-word"></i></a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive d-none d-md-block">
                        <table id="admin-hukuman-table"
                            class="table table-hover table-borderless align-middle modern-table mb-0">
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
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="d-block d-md-none">
                        <div class="mb-3">
                            <input type="text" id="search_mobile_hukuman" class="form-control"
                                placeholder="Cari nama pegawai..." autocomplete="off">
                        </div>
                        <div id="mobileHukumanCards"></div>
                        <div id="mobilePagination" style="display:none;">
                            <ul class="pagination mb-0" style="justify-content: center; gap: 8px;">
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
        <div class="modal fade" id="modalEditHukuman" tabindex="-1" aria-labelledby="modalEditHukumanLabel">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="formEditHukuman" enctype="multipart/form-data">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalEditHukumanLabel"><i
                                    class="bi bi-pencil-square me-1"></i>Edit Hukuman Disiplin</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="edit_id">
                            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Pegawai</label>
                                    <div class="position-relative">
                                        <input type="text" id="edit_pegawai_search" class="form-control"
                                            placeholder="Cari pegawai..." autocomplete="off">
                                        <input type="hidden" name="pegawai_id" id="edit_pegawai_id" required>
                                        <div id="edit_pegawai_results"
                                            class="position-absolute w-100 bg-white border rounded shadow-sm"
                                            style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;">
                                        </div>
                                    </div>
                                    <small class="text-muted">Ketik nama atau NIP pegawai untuk mencari</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Jabatan</label>
                                    <input type="text" name="jabatan" id="edit_jabatan" class="form-control" readonly
                                        required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">No SK</label>
                                    <input type="text" name="no_sk" id="edit_no_sk" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Upload File SK <span class="text-muted" style="font-size:0.88em;">(PDF, maks. 1MB, opsional, isi untuk ganti)</span></label>
                                    <input type="file" name="file_sk" id="edit_file_sk" class="form-control"
                                        accept="application/pdf">
                                    <div id="edit_file_sk_link" class="mt-1"></div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Tgl Mulai</label>
                                    <input type="date" name="tanggal_mulai" id="edit_tanggal_mulai" class="form-control"
                                        required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Tgl Berakhir</label>
                                    <input type="date" name="tanggal_berakhir" id="edit_tanggal_berakhir"
                                        class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Hukuman Disiplin yang Dijatuhkan</label>
                                    <input type="text" name="hukuman_dijatuhkan" id="edit_hukuman_dijatuhkan"
                                        class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Peraturan yang Dilanggar</label>
                                    <input type="text" name="peraturan_dilanggar" id="edit_peraturan_dilanggar"
                                        class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Keterangan</label>
                                    <input type="text" name="keterangan" id="edit_keterangan" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan
                                Perubahan</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script>
    // Validasi frontend: max 1MB untuk file SK (sebelum submit ke server)
    const MAX_SK_SIZE = 1 * 1024 * 1024; // 1MB
    function validateFileSKSize(inputEl) {
        if (inputEl.files.length > 0) {
            const file = inputEl.files[0];
            if (file.size > MAX_SK_SIZE) {
                Swal.fire({ icon: 'error', title: 'File Terlalu Besar', text: 'Ukuran file SK maksimal 1MB. File Anda: ' + (file.size / 1024 / 1024).toFixed(2) + 'MB', confirmButtonText: 'OK' });
                inputEl.value = '';
                return false;
            }
            if (file.type !== 'application/pdf') {
                Swal.fire({ icon: 'error', title: 'Format Salah', text: 'Hanya file PDF yang diperbolehkan untuk berkas SK.', confirmButtonText: 'OK' });
                inputEl.value = '';
                return false;
            }
        }
        return true;
    }
    document.getElementById('file_sk')?.addEventListener('change', function() { validateFileSKSize(this); });
    document.getElementById('edit_file_sk')?.addEventListener('change', function() { validateFileSKSize(this); });
    </script>
    <?php include(APPPATH . 'Views/components/bottom_nav_admin.php'); ?>
</body>

</html>