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
    <title>Tanda Tangan - MASSIPA</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/user/InputTandaTanganUser.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="color-scheme" content="dark light">
    <script>
      (function() {
        try {
          var mode = localStorage.getItem('theme-mode');
          if (
            mode === 'dark' ||
            (!mode && window.matchMedia('(prefers-color-scheme: dark)').matches)
          ) {
            document.documentElement.classList.add('dark-mode');
          }
        } catch(e) {}
      })();
    </script>
    
</head>
<body>
    <?php include(APPPATH . 'Views/components/sidebar_user.php'); ?>

    <?php include(APPPATH . 'Views/components/navbar_user.php'); ?>
    
    <div class="main-content">
        <div class="container-fluid px-0">
            <?php if (session()->getFlashdata("msg")): ?>
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
            
            <div class="row mb-0">
                <div class="col-12">
                    <?php
                    $isiPetunjuk = '<ul class="mb-0 ps-3">
                        <li>Pilih tab di atas form untuk input tanda tangan biasa atau gambar.</li>
                        <li>Data tanda tangan juga ditampilkan dalam dua tab: <b>Data Tanda Tangan Biasa</b> dan <b>Data Tanda Tangan Gambar</b>.</li>
                        <li>Tombol aksi hanya berupa ikon: <i class="bi bi-check-circle"></i>/<i class="bi bi-x-circle"></i> (aktif/nonaktif), <i class="fas fa-edit"></i> (edit), <i class="fas fa-trash"></i> (hapus). Urutan: aktif/nonaktif, edit, hapus.</li>
                        <li>Gunakan tombol <b>Simpan</b> untuk menambah data baru, dan <b>Edit</b> untuk mengubah data.</li>
                        <li>Untuk mengganti gambar tanda tangan, gunakan fitur edit pada data tanda tangan gambar.</li>
                    </ul>';
                    echo view('components/petunjuk', ['isiPetunjuk' => $isiPetunjuk]);
                    ?>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-header tambah-ttd-header" style="cursor: pointer; user-select: none;">
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Tambah Data Tanda Tangan</span>
                        <i class="bi bi-chevron-down chevron-tambah-ttd" style="transition: transform 0.2s;"></i>
                    </div>
                    <ul class="nav nav-tabs mb-0 mt-2" id="ttdTab" role="tablist" onclick="event.stopPropagation();">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="ttd-biasa-tab" data-bs-toggle="tab" data-bs-target="#ttd-biasa" type="button" role="tab" aria-controls="ttd-biasa" aria-selected="true">
                                <i class="bi bi-pencil"></i> Tanda Tangan Biasa
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="ttd-gambar-tab" data-bs-toggle="tab" data-bs-target="#ttd-gambar" type="button" role="tab" aria-controls="ttd-gambar" aria-selected="false">
                                <i class="bi bi-image"></i> Tanda Tangan Gambar
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body tambah-ttd-body" style="display: none;">
                        <div class="tab-content" id="ttdTabContent">
                            <div class="tab-pane fade show active" id="ttd-biasa" role="tabpanel" aria-labelledby="ttd-biasa-tab">
                    <form method="POST" action="<?= base_url("user/inputtandatanganuser/add") ?>">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="lokasi" class="form-label">Lokasi</label>
                                <input type="text" class="form-control" id="lokasi" name="lokasi" autocomplete="off" required>
                            </div>
                            <div class="col-md-6">
                                <label for="tanggal" class="form-label">Tanggal</label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= date("Y-m-d"); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="nama_jabatan" class="form-label">Nama Jabatan</label>
                            <input type="text" class="form-control" id="nama_jabatan" name="nama_jabatan" autocomplete="off" oninput="this.value = this.value.replace(/[0-9]/g, '')" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nama_penandatangan" class="form-label">Nama Penandatangan</label>
                                <input type="text" class="form-control" id="nama_penandatangan" name="nama_penandatangan" autocomplete="name" oninput="this.value = this.value.replace(/[0-9]/g, '')" required>
                            </div>
                            <div class="col-md-6">
                                <label for="nip_penandatangan" class="form-label">NIP Penandatangan</label>
                                <input type="number" class="form-control" id="nip_penandatangan" name="nip_penandatangan" autocomplete="off" pattern="[0-9]*" inputmode="numeric" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#pilihPegawaiModal">
                            <i class="fas fa-user-tie me-1"></i> Pilih dari Data Pegawai
                        </button>
                    </form>
                            </div>
                            <div class="tab-pane fade" id="ttd-gambar" role="tabpanel" aria-labelledby="ttd-gambar-tab">
                                <form method="POST" action="<?= base_url('user/inputtandatanganuser/add_gambar') ?>" enctype="multipart/form-data">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label for="tempat_gambar" class="form-label">Lokasi</label>
                                            <input type="text" class="form-control" id="tempat_gambar" name="tempat" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="tanggal_gambar" class="form-label">Tanggal</label>
                                            <input type="date" class="form-control" id="tanggal_gambar" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="gambar_ttd" class="form-label">Upload Gambar Tanda Tangan (PNG/JPG)</label>
                                        <input type="file" class="form-control" id="gambar_ttd" name="gambar_ttd" accept="image/png, image/jpeg" required>
                                        <div class="form-text">Pastikan gambar jelas dan rapi. Crop akan otomatis sesuai area gambar.</div>
                                        <img id="preview_gambar_ttd" src="#" alt="Preview" style="max-width:220px;max-height:80px;display:none;margin-top:10px;border:1px solid #e0e0e0;border-radius:6px;" />
                                    </div>
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Simpan Gambar</button>
                                </form>
                            </div>
                        </div>
                </div>
            </div>
            
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Data Tanda Tangan</span>
                        <ul class="nav nav-tabs mb-0" id="ttdDataTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="data-ttd-biasa-tab" data-bs-toggle="tab" data-bs-target="#data-ttd-biasa" type="button" role="tab" aria-controls="data-ttd-biasa" aria-selected="true">
                                    <i class="bi bi-table"></i> Tanda Tangan Biasa
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="data-ttd-gambar-tab" data-bs-toggle="tab" data-bs-target="#data-ttd-gambar" type="button" role="tab" aria-controls="data-ttd-gambar" aria-selected="false">
                                    <i class="bi bi-image"></i> Tanda Tangan Gambar
                                </button>
                            </li>
                        </ul>
                </div>
                <div class="card-body">
                        <div class="tab-content" id="ttdDataTabContent">
                            <div class="tab-pane fade show active" id="data-ttd-biasa" role="tabpanel" aria-labelledby="data-ttd-biasa-tab">
                                <div class="table-responsive d-none d-md-block">
                                    <table class="table table-hover table-borderless align-middle modern-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Lokasi</th>
                                    <th>Tanggal</th>
                                    <th>Nama Jabatan</th>
                                    <th>Nama Penandatangan</th>
                                    <th>NIP Penandatangan</th>
                                                <th>Status</th>
                                                <th class="col-aksi">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($tanda_tangan_data as $row): ?>
                                <tr>
                                    <td data-label="No"><?= $no++; ?></td>
                                    <td data-label="Lokasi"><?= $row["lokasi"]; ?></td>
                                    <td data-label="Tanggal"><?= tanggalIndo($row["tanggal"]); ?></td>
                                    <td data-label="Nama Jabatan"><?= $row["nama_jabatan"]; ?></td>
                                    <td data-label="Nama Penandatangan"><?= $row["nama_penandatangan"]; ?></td>
                                    <td data-label="NIP Penandatangan"><?= $row["nip_penandatangan"]; ?></td>
                                    <td data-label="Status">
                                                    <?php if ($row['is_aktif']): ?>
                                                        <span class="badge bg-success">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Nonaktif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td data-label="Aksi" class="col-aksi aksi-ttd-biasa">
                                                    <?php if (!$row['is_aktif']): ?>
                                                        <a href="<?= base_url('user/inputtandatanganuser/set_aktif/manual/' . $row['id']) ?>" class="btn btn-outline-primary btn-sm aksi-btn" title="Aktifkan"><i class="bi bi-check-circle"></i></a>
                                                    <?php else: ?>
                                                        <a href="<?= base_url('user/inputtandatanganuser/set_aktif/manual/' . $row['id']) ?>" class="btn btn-outline-danger btn-sm aksi-btn" title="Nonaktifkan"><i class="bi bi-x-circle"></i></a>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-warning btn-sm aksi-btn" data-bs-toggle="modal" data-bs-target="#editTandaTanganModal<?= $row["id"]; ?>" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                        </button>
                                                    <a href="<?= base_url("user/inputtandatanganuser/delete/" . $row["id"]) ?>" class="btn btn-danger btn-sm aksi-btn" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($tanda_tangan_data)): ?>
                                            <tr class="no-data-row"><td colspan="8" class="text-center text-muted py-4">Belum ada data</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="d-block d-md-none">
                                    <?php if (empty($tanda_tangan_data)): ?>
                                        <div class="text-center text-muted">Belum ada data</div>
                                    <?php else: ?>
                                        <?php $no = 1;
                                        foreach ($tanda_tangan_data as $row): ?>
                                            <div class="border rounded mb-3 p-3 shadow-sm" style="background:#fff;">
                                                <div class="fw-bold mb-1 d-flex justify-content-between align-items-center">
                                                    <span>No. <?= $no++ ?> - <?= $row["nama_penandatangan"]; ?></span>
                                                    <span class="badge bg-<?= $row['is_aktif'] ? 'success' : 'secondary' ?>">
                                                        <?= $row['is_aktif'] ? 'Aktif' : 'Nonaktif' ?>
                                                    </span>
                                                </div>
                                                <div><b>Lokasi:</b> <?= $row["lokasi"]; ?></div>
                                                <div><b>Tanggal:</b> <?= tanggalIndo($row["tanggal"]); ?></div>
                                                <div><b>Nama Jabatan:</b> <?= $row["nama_jabatan"]; ?></div>
                                                <div><b>NIP Penandatangan:</b> <?= $row["nip_penandatangan"]; ?></div>
                                                <div class="mt-2 d-flex gap-2 flex-wrap">
                                                    <?php if (!$row['is_aktif']): ?>
                                                        <a href="<?= base_url('user/inputtandatanganuser/set_aktif/manual/' . $row['id']) ?>" class="btn btn-outline-primary btn-sm aksi-btn" title="Aktifkan">
                                                            <i class="bi bi-check-circle"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?= base_url('user/inputtandatanganuser/set_aktif/manual/' . $row['id']) ?>" class="btn btn-outline-danger btn-sm aksi-btn" title="Nonaktifkan">
                                                            <i class="bi bi-x-circle"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-warning btn-sm aksi-btn" data-bs-toggle="modal" data-bs-target="#editTandaTanganModal<?= $row["id"]; ?>" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="<?= base_url("user/inputtandatanganuser/delete/" . $row["id"]) ?>" class="btn btn-danger btn-sm aksi-btn" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="data-ttd-gambar" role="tabpanel" aria-labelledby="data-ttd-gambar-tab">
                                <div class="table-responsive d-none d-md-block">
                                    <table class="table table-hover table-borderless align-middle modern-table">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Lokasi</th>
                                                <th>Tanggal</th>
                                                <th>Gambar</th>
                                                <th>Status</th>
                                                <th class="col-aksi">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $no=1; foreach (($tanda_tangan_gambar_data ?? []) as $row): ?>
                                            <tr>
                                                <td data-label="No"><?= $no++ ?></td>
                                                <td data-label="Lokasi"><?= esc($row['tempat']) ?></td>
                                                <td data-label="Tanggal"><?= tanggalIndo($row['tanggal']) ?></td>
                                                <td data-label="Gambar">
                                                    <?php if (!empty($row['file_path'])): ?>
                                                        <img src="<?= base_url('user/inputtandatanganuser/getFile/' . urlencode($row['file_path'])) ?>" alt="Tanda Tangan Gambar" style="max-width:90px; max-height:60px; border:1px solid #ccc; border-radius:6px;">
                                                    <?php endif; ?>
                                                </td>
                                                <td data-label="Status">
                                                    <?php if ($row['is_aktif']): ?>
                                                        <span class="badge bg-success">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Nonaktif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td data-label="Aksi" class="col-aksi aksi-ttd-gambar">
                                                    <?php if (!$row['is_aktif']): ?>
                                                        <a href="<?= base_url('user/inputtandatanganuser/set_aktif/gambar/' . $row['id']) ?>" class="btn btn-outline-primary btn-sm aksi-btn" title="Aktifkan"><i class="bi bi-check-circle"></i></a>
                                                    <?php else: ?>
                                                        <a href="<?= base_url('user/inputtandatanganuser/set_aktif/gambar/' . $row['id']) ?>" class="btn btn-outline-danger btn-sm aksi-btn" title="Nonaktifkan"><i class="bi bi-x-circle"></i></a>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-warning btn-sm aksi-btn" data-bs-toggle="modal" data-bs-target="#editTandaTanganGambarModal<?= $row['id'] ?>" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="<?= base_url('user/inputtandatanganuser/delete_gambar/' . $row['id']) ?>" class="btn btn-danger btn-sm aksi-btn" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php if (empty($tanda_tangan_gambar_data)): ?>
                                            <tr class="no-data-row"><td colspan="6" class="text-center text-muted py-4">Belum ada data</td></tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="d-block d-md-none">
                                    <?php if (empty($tanda_tangan_gambar_data)): ?>
                                        <div class="text-center text-muted">Belum ada data</div>
                                    <?php else: ?>
                                        <?php $no = 1;
                                        foreach (($tanda_tangan_gambar_data ?? []) as $row): ?>
                                            <div class="border rounded mb-3 p-3 shadow-sm" style="background:#fff;">
                                                <div class="fw-bold mb-1 d-flex justify-content-between align-items-center">
                                                    <span>No. <?= $no++ ?></span>
                                                    <span class="badge bg-<?= $row['is_aktif'] ? 'success' : 'secondary' ?>">
                                                        <?= $row['is_aktif'] ? 'Aktif' : 'Nonaktif' ?>
                                                    </span>
                                                </div>
                                                <div><b>Lokasi:</b> <?= esc($row['tempat']) ?></div>
                                                <div><b>Tanggal:</b> <?= tanggalIndo($row['tanggal']) ?></div>
                                                <?php if (!empty($row['file_path'])): ?>
                                                    <div class="mb-2">
                                                        <b>Gambar:</b><br>
                                                        <img src="<?= base_url('user/inputtandatanganuser/getFile/' . urlencode($row['file_path'])) ?>" alt="Tanda Tangan Gambar" style="max-width:120px; max-height:80px; border:1px solid #ccc; border-radius:6px; margin-top:4px;">
                                                    </div>
                                                <?php endif; ?>
                                                <div class="mt-2 d-flex gap-2 flex-wrap">
                                                    <?php if (!$row['is_aktif']): ?>
                                                        <a href="<?= base_url('user/inputtandatanganuser/set_aktif/gambar/' . $row['id']) ?>" class="btn btn-outline-primary btn-sm aksi-btn" title="Aktifkan">
                                                            <i class="bi bi-check-circle"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?= base_url('user/inputtandatanganuser/set_aktif/gambar/' . $row['id']) ?>" class="btn btn-outline-danger btn-sm aksi-btn" title="Nonaktifkan">
                                                            <i class="bi bi-x-circle"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-warning btn-sm aksi-btn" data-bs-toggle="modal" data-bs-target="#editTandaTanganGambarModal<?= $row['id'] ?>" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="<?= base_url('user/inputtandatanganuser/delete_gambar/' . $row['id']) ?>" class="btn btn-danger btn-sm aksi-btn" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php foreach ($tanda_tangan_data as $row): ?>
                                        <div class="modal fade" id="editTandaTanganModal<?= $row["id"]; ?>" tabindex="-1" aria-labelledby="editTandaTanganModalLabel<?= $row["id"]; ?>" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editTandaTanganModalLabel<?= $row["id"]; ?>">Edit Data Tanda Tangan</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="POST" action="<?= base_url('user/inputtandatanganuser/update') ?>">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="tanda_tangan_id" value="<?= $row["id"]; ?>">
                                                            <div class="mb-3">
                                                                <label for="lokasi" class="form-label">Lokasi</label>
                                                                <input type="text" class="form-control" id="lokasi" name="lokasi" value="<?= $row["lokasi"]; ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="tanggal" class="form-label">Tanggal</label>
                                                                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= $row["tanggal"]; ?>" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="nama_jabatan" class="form-label">Nama Jabatan</label>
                                                                <input type="text" class="form-control" id="nama_jabatan" name="nama_jabatan" value="<?= $row["nama_jabatan"]; ?>" oninput="this.value = this.value.replace(/[0-9]/g, '')" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="nama_penandatangan" class="form-label">Nama Penandatangan</label>
                                                                <input type="text" class="form-control" id="nama_penandatangan" name="nama_penandatangan" value="<?= $row["nama_penandatangan"]; ?>" oninput="this.value = this.value.replace(/[0-9]/g, '')" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="nip_penandatangan" class="form-label">NIP Penandatangan</label>
                                                                <input type="number" class="form-control" id="nip_penandatangan" name="nip_penandatangan" value="<?= $row["nip_penandatangan"]; ?>" pattern="[0-9]*" inputmode="numeric" required>
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
                            <div class="tab-pane fade" id="data-ttd-gambar" role="tabpanel" aria-labelledby="data-ttd-gambar-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover table-borderless align-middle modern-table">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Lokasi</th>
                                                <th>Tanggal</th>
                                                <th>Gambar</th>
                                                <th>Status</th>
                                                <th class="col-aksi">Aksi</th>
                                            </tr>
                                        </thead>
                            <tbody>
                                            <?php foreach (($tanda_tangan_gambar_data ?? []) as $row): ?>
                                <tr>
                                                <td data-label="No"><?= $no++ ?></td>
                                                <td data-label="Lokasi"><?= esc($row['tempat']) ?></td>
                                                <td data-label="Tanggal"><?= tanggalIndo($row['tanggal']) ?></td>
                                                <td data-label="Gambar">
                                                    <?php if (!empty($row['file_path'])): ?>
                                                        <img src="<?= base_url('user/inputtandatanganuser/getFile/' . urlencode($row['file_path'])) ?>" alt="Tanda Tangan Gambar" style="max-width:90px; max-height:60px; border:1px solid #ccc; border-radius:6px;">
                                                    <?php endif; ?>
                                                </td>
                                                <td data-label="Status">
                                                    <?php if ($row['is_aktif']): ?>
                                                        <span class="badge bg-success">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Nonaktif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td data-label="Aksi" class="col-aksi aksi-ttd-gambar">
                                                    <?php if (!$row['is_aktif']): ?>
                                                        <a href="<?= base_url('user/inputtandatanganuser/set_aktif/gambar/' . $row['id']) ?>" class="btn btn-outline-primary btn-sm aksi-btn" title="Aktifkan"><i class="bi bi-check-circle"></i></a>
                                                    <?php else: ?>
                                                        <a href="<?= base_url('user/inputtandatanganuser/set_aktif/gambar/' . $row['id']) ?>" class="btn btn-outline-danger btn-sm aksi-btn" title="Nonaktifkan"><i class="bi bi-x-circle"></i></a>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-warning btn-sm aksi-btn" data-bs-toggle="modal" data-bs-target="#editTandaTanganGambarModal<?= $row['id'] ?>" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                        </button>
                                                    <a href="<?= base_url('user/inputtandatanganuser/delete_gambar/' . $row['id']) ?>" class="btn btn-danger btn-sm aksi-btn" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                            <?php if (empty($tanda_tangan_gambar_data)): ?>
                                            <tr class="no-data-row"><td colspan="6" class="text-center text-muted py-4">Belum ada data</td></tr>
                                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
                    </div>
                </div>

                <?php foreach (($tanda_tangan_gambar_data ?? []) as $row): ?>
                <div class="modal fade" id="editTandaTanganGambarModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="editTandaTanganGambarModalLabel<?= $row['id'] ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editTandaTanganGambarModalLabel<?= $row['id'] ?>">Edit Tanda Tangan Gambar</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" action="<?= base_url('user/inputtandatanganuser/update_gambar') ?>" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <div class="mb-3">
                                        <label for="tempat_edit_<?= $row['id'] ?>" class="form-label">Lokasi</label>
                                        <input type="text" class="form-control" id="tempat_edit_<?= $row['id'] ?>" name="tempat" value="<?= esc($row['tempat']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="tanggal_edit_<?= $row['id'] ?>" class="form-label">Tanggal</label>
                                        <input type="date" class="form-control" id="tanggal_edit_<?= $row['id'] ?>" name="tanggal" value="<?= $row['tanggal'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="gambar_ttd_edit_<?= $row['id'] ?>" class="form-label">Gambar Tanda Tangan (Opsional)</label>
                                        <input type="file" class="form-control" id="gambar_ttd_edit_<?= $row['id'] ?>" name="gambar_ttd" accept="image/png, image/jpeg">
                                        <div class="form-text">Upload gambar baru untuk mengganti gambar yang ada. Kosongkan jika tidak ingin mengubah gambar.</div>

                                        <img id="preview_gambar_edit_<?= $row['id'] ?>" src="#" alt="Preview" style="max-width:200px;max-height:100px;display:none;margin-top:10px;border:1px solid #e0e0e0;border-radius:6px;" />
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
    </div>

    <div class="modal fade" id="pilihPegawaiModal" tabindex="-1" aria-labelledby="pilihPegawaiModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pilihPegawaiModalLabel">Cari & Pilih Pegawai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="search_pegawai_input" class="form-label">Cari nama/NIP pegawai</label>
                    <input type="text" id="search_pegawai_input" class="form-control" placeholder="Ketik nama/NIP..." autocomplete="off">
                    <input type="hidden" id="search_pegawai_id">
                    <div id="search_pegawai_results" class="position-absolute w-100 bg-white border rounded shadow-sm mt-1" style="z-index: 1050; max-height: 220px; overflow-y: auto; display: none;"></div>
                    <div class="form-text mt-2">Ketik minimal 2 huruf, lalu pilih pegawai dari hasil pencarian.</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script src="<?= base_url('assets/js/user/InputTandaTanganUser.js') ?>"></script>
    
    <script>
    (function() {
        if (window._tambahTtdToggleInit) return;
        window._tambahTtdToggleInit = true;
        $(document).on('click', '.tambah-ttd-header', function(e) {
            // Jangan trigger jika klik pada tabs
            if ($(e.target).closest('.nav-tabs').length) return;
            var card = $(this).closest('.card');
            var body = card.find('.tambah-ttd-body');
            var chevron = card.find('.chevron-tambah-ttd');
            if (body.is(':visible')) {
                body.stop(true, true).slideUp(180);
                chevron.css('transform', 'rotate(0deg)');
            } else {
                body.stop(true, true).slideDown(180);
                chevron.css('transform', 'rotate(180deg)');
            }
        });
    })();
    </script>
    
    <?php include(APPPATH . 'Views/components/bottom_nav_user.php'); ?>
</body>
</html>