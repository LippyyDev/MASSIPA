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
    <title>Kelola User - MASSIPA</title>
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/KelolaUser.css') ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="<?= base_url('assets/js/admin/KelolaUser.js') ?>"></script>
    
</head>

<body data-flash-msg="<?= esc(session()->getFlashdata("msg")) ?>" data-flash-type="<?= esc(session()->getFlashdata("msg_type")) ?>" data-admin-count="<?= (int)$admin_count ?>">
    <?php include(APPPATH . 'Views/components/sidebar_admin.php'); ?>
    <?php include(APPPATH . 'Views/components/navbar_admin.php'); ?>
    <div class="main-content">
        <div class="overlay"></div>
        <div class="container-fluid px-0">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Tambah User Baru</span>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#tambahUserModal" title="Tambah User">
                        <i class="bi bi-plus-lg"></i><span class="d-none d-md-inline ms-1">Tambah User</span>
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive d-none d-md-block">
                        <table id="userTable" class="table table-hover table-borderless align-middle modern-table">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Username</th>
                                    <th>Nama Lengkap</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Satker</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                foreach ($users as $row):
                                    ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= $row["username"]; ?></td>
                                        <td><?= $row["nama_lengkap"]; ?></td>
                                        <td><?= $row["email"]; ?></td>
                                        <td>
                                            <span class="badge bg-<?= $row["role"] == "admin" ? "danger" : "primary"; ?>">
                                                <?= ucfirst($row["role"]); ?>
                                            </span>
                                        </td>
                                        <td><?= isset($row["nama_satker"]) ? esc($row["nama_satker"]) : "-"; ?></td>
                                        <td class="d-flex gap-2">
                                            <button type="button" class="btn btn-warning btn-action" data-bs-toggle="modal"
                                                data-bs-target="#editUserModal<?= $row["id"]; ?>" title="Edit">
                                                <i class="bi bi-pencil-square"></i><span class="d-none d-md-inline ms-1">Edit</span>
                                            </button>
                                            <form action="<?= base_url('admin/deleteUser') ?>" method="post"
                                                class="d-inline delete-user-form">
                                                <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                                                <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                                <button type="button" class="btn btn-danger btn-action btn-delete-user" data-role="<?= $row['role'] ?>" title="Hapus">
                                                    <i class="bi bi-trash"></i><span class="d-none d-md-inline ms-1">Hapus</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-block d-md-none">
                        <?php $no = 1;
                        foreach ($users as $row): ?>
                            <div class="border rounded mb-3 p-3 shadow-sm" style="background:#fff;">
                                <div class="fw-bold mb-1 d-flex justify-content-between align-items-center">
                                    <span>No. <?= $no++; ?> - <?= $row["username"]; ?></span>
                                    <span class="badge bg-<?= $row["role"] == "admin" ? "danger" : "primary"; ?>">
                                        <?= ucfirst($row["role"]); ?>
                                    </span>
                                </div>
                                <div><b>Nama:</b> <?= $row["nama_lengkap"]; ?></div>
                                <div><b>Email:</b> <?= $row["email"]; ?></div>
                                <div><b>Satker:</b> <?= isset($row["nama_satker"]) ? esc($row["nama_satker"]) : "-"; ?>
                                </div>
                                <div class="mt-2 d-flex gap-2">
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#editUserModal<?= $row["id"]; ?>" title="Edit">
                                        <i class="bi bi-pencil-square"></i><span class="d-none d-md-inline ms-1">Edit</span>
                                    </button>
                                    <form action="<?= base_url('admin/deleteUser') ?>" method="post"
                                        class="d-inline delete-user-form">
                                        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                                        <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                                        <button type="button" class="btn btn-danger btn-sm btn-delete-user" title="Hapus">
                                            <i class="bi bi-trash"></i><span class="d-none d-md-inline ms-1">Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="tambahUserModal" tabindex="-1" aria-labelledby="tambahUserModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?= base_url('admin/addUser') ?>" method="post">
                    <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tambahUserModalLabel">Tambah User Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" autocomplete="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div style="position:relative;">
                                <input type="password" class="form-control" id="password" name="password" autocomplete="new-password" required style="padding-right:2.5rem;background-image:none;">
                                <button type="button" class="toggle-password" data-target="password" tabindex="-1" style="position:absolute;top:50%;transform:translateY(-50%);right:0.5rem;background:none;border:none;cursor:pointer;color:#6c757d;line-height:1;display:flex;align-items:center;padding:0;">
                                    <i class="bi bi-eye-slash" style="font-size: 0.95rem;"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" autocomplete="name" oninput="this.value = this.value.replace(/[0-9]/g, '')" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" autocomplete="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="tambah_role" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                        <div class="mb-3" id="tambah_satker_group">
                            <label for="satker_id" class="form-label">Satuan Kerja</label>
                            <select class="form-select" id="satker_id" name="satker_id">
                                <option value="">Pilih Satker</option>
                                <?php
                                $used_satker = array_column(array_filter($users, function ($u) {
                                    return !empty($u['satker_id']);
                                }), 'satker_id', 'id');
                                ?>
                                <?php foreach ($list_satker as $satker): ?>
                                    <?php
                                    $used_by = null;
                                    foreach ($users as $u) {
                                        if ($u['satker_id'] == $satker['id']) {
                                            $used_by = $u['nama_lengkap'];
                                            break;
                                        }
                                    }
                                    ?>
                                    <option value="<?= esc($satker['id']) ?>" <?= $used_by ? 'disabled' : '' ?>>
                                        <?= esc($satker['nama']) ?>
                                        <?= $used_by ? ' (Sudah dipakai: ' . esc($used_by) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Satuan Kerja harus dipilih</div>
                            <div class="form-text">Tambah/ubah satker hanya di menu <b>Kelola Satker</b>.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php foreach ($users as $row): ?>
        <div class="modal fade" id="editUserModal<?= $row["id"] ?>" tabindex="-1"
            aria-labelledby="editUserModalLabel<?= $row["id"] ?>">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="<?= base_url('admin/updateUser') ?>" method="post">
                        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
                        <input type="hidden" name="user_id" value="<?= $row["id"] ?>">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editUserModalLabel<?= $row["id"] ?>">Edit User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="username_<?= $row['id'] ?>" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username_<?= $row['id'] ?>" name="username"
                                    value="<?= $row["username"] ?>" autocomplete="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="nama_lengkap_<?= $row['id'] ?>" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama_lengkap_<?= $row['id'] ?>"
                                    name="nama_lengkap" value="<?= $row["nama_lengkap"] ?>" autocomplete="name" oninput="this.value = this.value.replace(/[0-9]/g, '')" required>
                            </div>
                            <div class="mb-3">
                                <label for="email_<?= $row['id'] ?>" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email_<?= $row['id'] ?>" name="email"
                                    value="<?= $row["email"] ?>" autocomplete="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password_<?= $row['id'] ?>" class="form-label">Password (Kosongkan jika tidak ingin mengubah)</label>
                                <div style="position:relative;">
                                    <input type="password" class="form-control" id="password_<?= $row['id'] ?>" name="password" autocomplete="new-password" style="padding-right:2.5rem;background-image:none;">
                                    <button type="button" class="toggle-password" data-target="password_<?= $row['id'] ?>" tabindex="-1" style="position:absolute;top:50%;transform:translateY(-50%);right:0.5rem;background:none;border:none;cursor:pointer;color:#6c757d;line-height:1;display:flex;align-items:center;padding:0;">
                                        <i class="bi bi-eye-slash" style="font-size: 0.95rem;"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="role_<?= $row['id'] ?>" class="form-label">Role</label>
                                <select class="form-select edit-role-select" id="role_<?= $row['id'] ?>" name="role" required data-user-id="<?= $row['id'] ?>">
                                    <option value="admin" <?= $row["role"] == "admin" ? "selected" : ""; ?>>Admin</option>
                                    <option value="user" <?= $row["role"] == "user" ? "selected" : ""; ?>>User</option>
                                </select>
                            </div>
                            <div class="mb-3 edit-satker-group" id="edit_satker_group_<?= $row['id'] ?>" <?= $row['role'] == 'admin' ? 'style="display:none;"' : '' ?>>
                                <label for="satker_id_<?= $row['id'] ?>" class="form-label">Satuan Kerja</label>
                                <select class="form-select edit-satker-select" id="satker_id_<?= $row['id'] ?>" name="satker_id" <?= $row['role'] == 'user' ? 'required' : '' ?>>
                                    <option value="">Pilih Satker</option>
                                    <?php
                                    foreach ($list_satker as $satker):
                                        $used_by = null;
                                        foreach ($users as $u) {
                                            if ($u['satker_id'] == $satker['id'] && $u['id'] != $row['id']) {
                                                $used_by = $u['nama_lengkap'];
                                                break;
                                            }
                                        }
                                        ?>
                                        <option value="<?= esc($satker['id']) ?>" <?= $row["satker_id"] == $satker['id'] ? 'selected' : '' ?> <?= $used_by ? 'disabled' : '' ?>>
                                            <?= esc($satker['nama']) ?>
                                            <?= $used_by ? ' (Sudah dipakai: ' . esc($used_by) . ')' : '' ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Tambah/ubah satker hanya di menu <b>Kelola Satker</b>.</div>
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
    <?php include(APPPATH . 'Views/components/bottom_nav_admin.php'); ?>
</body>

</html>