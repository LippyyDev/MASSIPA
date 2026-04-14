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
    <title>Rekap Kedisiplinan - MASSIPA</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/StatusDisiplinSatker.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
    <?php include(APPPATH . 'Views/components/sidebar_admin.php'); ?>

    <?php include(APPPATH . 'Views/components/navbar_admin.php'); ?>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="main-content">
        <div class="overlay"></div>

        <div class="container-fluid px-0">
            <div class="row mb-0">
                <div class="col-12">
                    <?php
                    $isiPetunjuk = '<ul class="mb-0 ps-3" style="font-size:0.98em;">
                        <li><i class="bi bi-funnel"></i> Pilih satuan kerja dan tahun untuk melihat rekap kedisiplinan.</li>
                        <li><i class="bi bi-table"></i> Tabel menampilkan status pengisian data kedisiplinan tiap bulan.</li>
                        <li><i class="bi bi-check-circle"></i> ✅ artinya laporan disiplin sudah diterima.</li>
                        <li><i class="bi bi-apple"></i> 🍎 artinya laporan apel sudah diterima.</li>
                        <li><i class="bi bi-check-circle"></i> ✅🍎 artinya kedua laporan (disiplin dan apel) sudah diterima.</li>
                        <li><i class="bi bi-hourglass-split"></i> ⏳ artinya ada laporan yang menunggu persetujuan.</li>
                        <li><i class="bi bi-hourglass-split"></i> ⏳⏳ artinya ada dua laporan (disiplin dan apel) yang menunggu persetujuan.</li>
                    </ul>';
                    echo view('components/petunjuk', ['isiPetunjuk' => $isiPetunjuk]);
                    ?>
                </div>
            </div>
            <div class="row mb-0">
                <div class="col-12">
                    <div class="card mb-3 d-none d-md-block">
                        <div class="card-header">
                            Filter Data
                        </div>
                        <div class="card-body">
                            <form action="<?= base_url('admin/rekap_kedisiplinan') ?>" method="get" class="row g-3 mb-0"
                                id="filterRekapForm">
                                <div class="col-md-6">
                                    <label for="satker_id" class="form-label">Satuan Kerja</label>
                                    <select name="satker_id" id="satker_id" class="form-control"
                                        onchange="document.getElementById('filterRekapForm').submit()">
                                        <option value="">Semua Satker</option>
                                        <?php foreach ($satker_list as $satker): ?>
                                            <option value="<?= esc($satker['id']) ?>" <?= $filter_satker == $satker['id'] ? 'selected' : '' ?>>
                                                <?= esc($satker['nama']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="tahun" class="form-label">Tahun</label>
                                    <select name="tahun" id="tahun" class="form-control"
                                        onchange="document.getElementById('filterRekapForm').submit()">
                                        <?php foreach ($daftar_tahun as $tahun): ?>
                                            <option value="<?= esc($tahun) ?>" <?= $tahun_dipilih == $tahun ? 'selected' : '' ?>>
                                                <?= esc($tahun) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-3">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <span>Data Kedisiplinan User Tahun <?= htmlspecialchars($tahun_dipilih); ?></span>
                    <button type="button" class="btn btn-sm d-block d-md-none" style="background: #7c3aed; border-color: #7c3aed; color: #fff;" data-bs-toggle="modal" data-bs-target="#filterStatusDisiplinModal" title="Filter Data">
                        <i class="bi bi-funnel"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-hover table-borderless align-middle modern-table" id="rekapTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Satker</th>
                                    <?php
                                    $singkatan_bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                                    $i_bulan = 0;
                                    foreach ($nama_bulan as $nama): ?>
                                        <th class="text-center"><?= $singkatan_bulan[$i_bulan++] ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users_data)): ?>
                                    <?php $no = 1; ?>
                                    <?php foreach ($users_data as $user): ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($user["nama_satker"] ?? $user["nama"] ?? "Tidak ada satker"); ?>
                                            </td>
                                            <?php foreach ($nama_bulan as $bulan_num => $nama): ?>
                                                <td class="text-center">
                                                    <?php
                                                    $user_id = $user["user_id"];
                                                    if ($user_id && isset($laporan_data[$user_id][$bulan_num])) {
                                                        $reports = $laporan_data[$user_id][$bulan_num]['reports'];
                                                        
                                                        $disiplin_reports = [];
                                                        $apel_reports = [];
                                                        $all_files_links = [];
                                                        
                                                        foreach ($reports as $report) {
                                                            $status = $report['status'];
                                                            $kategori = $report['kategori'];
                                                            
                                                            if ($status == 'diterima' || $status == 'dilihat' || $status == 'terkirim') {
                                                                if (!empty($report['file_path']) || !empty($report['link_drive'])) {
                                                                    $all_files_links[] = [
                                                                        'id' => $report['id'],
                                                                        'nama' => $report['nama_laporan'],
                                                                        'kategori' => $kategori,
                                                                        'status' => $status,
                                                                        'file_path' => $report['file_path'],
                                                                        'link_drive' => $report['link_drive']
                                                                    ];
                                                                }
                                                                
                                                                if ($kategori == 'Laporan Disiplin') {
                                                                    $disiplin_reports[] = $report;
                                                            } elseif ($kategori == 'Laporan Apel') {
                                                                    $apel_reports[] = $report;
                                                                }
                                                            }
                                                        }
                                                        
                                                        $display = '';
                                                        $has_clickable_items = !empty($all_files_links);
                                                        
                                                        if (!empty($disiplin_reports)) {
                                                            $has_approved = false;
                                                            $has_pending = false;
                                                            
                                                            foreach ($disiplin_reports as $report) {
                                                                if ($report['status'] == 'diterima') {
                                                                    $has_approved = true;
                                                                } else {
                                                                    $has_pending = true;
                                                                }
                                                            }
                                                            
                                                            $icon = $has_approved ? '✅' : '⏳';
                                                            
                                                            if ($has_clickable_items) {
                                                                $data_reports = htmlspecialchars(json_encode($all_files_links));
                                                                $display .= '<span class="clickable-icon" style="cursor: pointer; text-decoration: none;" title="Klik untuk melihat laporan" data-reports=\'' . $data_reports . '\'>' . $icon . '</span>';
                                                            } else {
                                                                $display .= $icon;
                                                            }
                                                        }
                                                        
                                                        if (!empty($apel_reports)) {
                                                            $has_approved = false;
                                                            $has_pending = false;
                                                            
                                                            foreach ($apel_reports as $report) {
                                                                if ($report['status'] == 'diterima') {
                                                                    $has_approved = true;
                                                                } else {
                                                                    $has_pending = true;
                                                                }
                                                            }
                                                            
                                                            $icon = $has_approved ? '🍎' : '⏳';
                                                            
                                                            if ($has_clickable_items) {
                                                                $data_reports = htmlspecialchars(json_encode($all_files_links));
                                                                $display .= '<span class="clickable-icon" style="cursor: pointer; text-decoration: none;" title="Klik untuk melihat laporan" data-reports=\'' . $data_reports . '\'>' . $icon . '</span>';
                                                            } else {
                                                                $display .= $icon;
                                                            }
                                                        }
                                                        
                                                        if (empty($display)) {
                                                            echo '-';
                                                        } else {
                                                            echo $display;
                                                        }
                                                    } else {
                                                        echo "-";
                                                    }
                                                    ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (empty($users_data)): ?>
                        <div class="text-center py-3">Tidak ada data user yang sesuai dengan filter.</div>
                    <?php endif; ?>
                    <div class="d-block d-md-none" id="mobileCardsContainer">
                        <div class="mb-3">
                            <input type="text" id="search_mobile_satker" class="form-control"
                                placeholder="Cari nama satker..." autocomplete="off">
                        </div>
                        <div id="mobileSatkerCards">
                        </div>
                        <div id="mobileSatkerPagination" style="display:none;">
                            <ul class="pagination mb-0" style="justify-content: center; gap: 8px;">
                                <li class="page-item">
                                    <button class="page-link" id="mobileSatkerPrev" type="button">&lt;</button>
                                </li>
                                <li id="mobileSatkerPageNumbers" style="display: contents;"></li>
                                <li class="page-item">
                                    <button class="page-link" id="mobileSatkerNext" type="button">&gt;</button>
                                </li>
                            </ul>
                        </div>
                        <?php if (empty($users_data)): ?>
                            <div class="text-center">Tidak ada data user yang sesuai dengan filter.</div>
                        <?php else: ?>
                            <div id="mobileSatkerCardsOriginal" style="display:none;">
                            <?php $no = 1; ?>
                            <?php foreach ($users_data as $user): ?>
                                <div class="border rounded mb-3 p-3 shadow-sm" style="background:#fff;">
                                    <div class="fw-bold mb-1">No. <?= $no++; ?> -
                                        <?= htmlspecialchars($user["nama_satker"] ?? $user["nama"] ?? "Tidak ada satker"); ?>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php $i_bulan = 0;
                                        foreach ($nama_bulan as $bulan_num => $nama): ?>
                                            <div><b><?= $singkatan_bulan[$i_bulan++] ?>:</b> <?php
                                              $user_id = $user["user_id"];
                                              if ($user_id && isset($laporan_data[$user_id][$bulan_num])) {
                                                  $reports = $laporan_data[$user_id][$bulan_num]['reports'];
                                                  
                                                  $disiplin_reports = [];
                                                  $apel_reports = [];
                                                  $all_files_links = [];
                                                  
                                                  foreach ($reports as $report) {
                                                      $status = $report['status'];
                                                      $kategori = $report['kategori'];
                                                      
                                                      if ($status == 'diterima' || $status == 'dilihat' || $status == 'terkirim') {
                                                          if (!empty($report['file_path']) || !empty($report['link_drive'])) {
                                                              $all_files_links[] = [
                                                                  'id' => $report['id'],
                                                                  'nama' => $report['nama_laporan'],
                                                                  'kategori' => $kategori,
                                                                  'status' => $status,
                                                                  'file_path' => $report['file_path'],
                                                                  'link_drive' => $report['link_drive']
                                                              ];
                                                          }
                                                          
                                                          if ($kategori == 'Laporan Disiplin') {
                                                              $disiplin_reports[] = $report;
                                                      } elseif ($kategori == 'Laporan Apel') {
                                                              $apel_reports[] = $report;
                                                          }
                                                      }
                                                  }
                                                  
                                                  $display = '';
                                                  $has_clickable_items = !empty($all_files_links);
                                                  
                                                  if (!empty($disiplin_reports)) {
                                                      $has_approved = false;
                                                      $has_pending = false;
                                                      
                                                      foreach ($disiplin_reports as $report) {
                                                          if ($report['status'] == 'diterima') {
                                                              $has_approved = true;
                                                          } else {
                                                              $has_pending = true;
                                                          }
                                                      }
                                                      
                                                      $icon = $has_approved ? '✅' : '⏳';
                                                      
                                                      if ($has_clickable_items) {
                                                          $data_reports = htmlspecialchars(json_encode($all_files_links));
                                                          $display .= '<span class="clickable-icon" style="cursor: pointer; text-decoration: none;" title="Klik untuk melihat laporan" data-reports=\'' . $data_reports . '\'>' . $icon . '</span>';
                                                      } else {
                                                          $display .= $icon;
                                                      }
                                                  }
                                                  
                                                  if (!empty($apel_reports)) {
                                                      $has_approved = false;
                                                      $has_pending = false;
                                                      
                                                      foreach ($apel_reports as $report) {
                                                          if ($report['status'] == 'diterima') {
                                                              $has_approved = true;
                                                          } else {
                                                              $has_pending = true;
                                                          }
                                                      }
                                                      
                                                      $icon = $has_approved ? '🍎' : '⏳';
                                                      
                                                      if ($has_clickable_items) {
                                                          $data_reports = htmlspecialchars(json_encode($all_files_links));
                                                          $display .= '<span class="clickable-icon" style="cursor: pointer; text-decoration: none;" title="Klik untuk melihat laporan" data-reports=\'' . $data_reports . '\'>' . $icon . '</span>';
                                                      } else {
                                                          $display .= $icon;
                                                      }
                                                  }
                                                  
                                                  if (empty($display)) {
                                                      echo '-';
                                                  } else {
                                                      echo $display;
                                                  }
                                              } else {
                                                  echo "-";
                                              }
                                              ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="choiceModal" tabindex="-1" aria-labelledby="choiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="choiceModalLabel">Daftar Laporan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="choiceModalText">Pilih laporan yang ingin dibuka:</p>
                    <div id="reportsListContainer">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <button type="button" class="btn btn-primary d-none" id="openFileBtn">
        <i class="bi bi-file-earmark-pdf"></i> Buka File PDF
    </button>
    <button type="button" class="btn btn-success d-none" id="openLinkBtn">
        <i class="bi bi-link-45deg"></i> Buka Link Drive
    </button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <div class="modal fade d-md-none" id="filterStatusDisiplinModal" tabindex="-1" aria-labelledby="filterStatusDisiplinModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-bottom-sheet">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filterStatusDisiplinModalLabel">
                        <i class="bi bi-funnel me-2"></i>Filter Data
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="<?= base_url('admin/rekap_kedisiplinan') ?>" method="get" id="filterRekapFormMobile">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="satker_id_mobile" class="form-label">Satuan Kerja</label>
                                <select name="satker_id" id="satker_id_mobile" class="form-select">
                                    <option value="">Semua Satker</option>
                                    <?php foreach ($satker_list as $satker): ?>
                                        <option value="<?= esc($satker['id']) ?>" <?= $filter_satker == $satker['id'] ? 'selected' : '' ?>>
                                            <?= esc($satker['nama']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="tahun_mobile" class="form-label">Tahun</label>
                                <select name="tahun" id="tahun_mobile" class="form-select">
                                    <?php foreach ($daftar_tahun as $tahun): ?>
                                        <option value="<?= esc($tahun) ?>" <?= $tahun_dipilih == $tahun ? 'selected' : '' ?>>
                                            <?= esc($tahun) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn" style="background: #7c3aed; border-color: #7c3aed; color: #fff;" onclick="document.getElementById('filterRekapFormMobile').submit();">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?= base_url('assets/js/admin/StatusDisiplinSatker.js') ?>"></script>
    <?php include(APPPATH . 'Views/components/bottom_nav_admin.php'); ?>
</body>

</html>