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
    <title>Beranda - MASSIPA</title>
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
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/BerandaAdmin.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/realtime_notifications.css') ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        window.BULAN_LABELS = <?= json_encode($bulan_labels) ?>;
        window.SATKER_TREND = <?= json_encode($satker_trend) ?>;
        window.PEGAWAI_TREND = <?= json_encode($pegawai_trend) ?>;
        window.PENDING_TREND = <?= json_encode($pending_trend) ?>;
        window.ARSIP_TREND = <?= json_encode($arsip_trend) ?>;
        window.LAPORAN_PER_BULAN = [
            <?php for ($i = 1; $i <= 12; $i++): ?>
                    <?= isset($laporan_per_bulan[$i]) ? $laporan_per_bulan[$i] : 0 ?>,
            <?php endfor; ?>
        ];
        window.BASE_URL = '<?= base_url() ?>';
        window.CSRF_HASH = '<?= csrf_hash() ?>';
    </script>
    <script src="<?= base_url('assets/js/admin/BerandaAdmin.js') ?>"></script>
</head>

<body>
    <?php include(APPPATH . 'Views/components/sidebar_admin.php'); ?>
    <?php include(APPPATH . 'Views/components/navbar_admin.php'); ?>
    <div class="main-content">
        <div class="overlay"></div>
        <div class="container-fluid px-0">
            <div class="row">
                <div class="col-md-3">
                    <a href="<?= base_url('admin/kelola_satker') ?>" class="text-decoration-none">
                        <div class="stat-card stat-left position-relative" style="cursor: pointer;">
                            <div class="stat-info">
                                <p class="stat-label">Total Satker</p>
                                <h3 class="stat-value"><?= $satker_count; ?></h3>
                                <canvas id="satkerChart" height="60"></canvas>
                            </div>
                            <i class="bi bi-building stat-icon-bg"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= base_url('admin/input_pegawai') ?>" class="text-decoration-none">
                        <div class="stat-card stat-left position-relative" style="cursor: pointer;">
                            <div class="stat-info">
                                <p class="stat-label">Total Pegawai</p>
                                <h3 class="stat-value"><?= $pegawai_count; ?></h3>
                                <canvas id="pegawaiChart" height="60"></canvas>
                            </div>
                            <i class="bi bi-people stat-icon-bg"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= base_url('admin/kelola_laporan') ?>" class="text-decoration-none">
                        <div class="stat-card stat-left position-relative" style="cursor: pointer;">
                            <div class="stat-info">
                                <p class="stat-label">Total Pending</p>
                                <h3 class="stat-value"><?= $laporan_count; ?></h3>
                                <canvas id="pendingChart" height="60"></canvas>
                            </div>
                            <i class="bi bi-hourglass-split stat-icon-bg"></i>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= base_url('admin/arsip_laporan') ?>" class="text-decoration-none">
                        <div class="stat-card stat-left position-relative" style="cursor: pointer;">
                            <div class="stat-info">
                                <p class="stat-label">Total Arsip</p>
                                <h3 class="stat-value"><?= $arsip_count; ?></h3>
                                <canvas id="arsipChart" height="60"></canvas>
                            </div>
                            <i class="bi bi-archive stat-icon-bg"></i>
                        </div>
                    </a>
                </div>
            </div>
            <div class="row mb-4 d-none d-sm-block">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <span>Total Laporan Masuk Periode <?= date('Y') ?></span>
                        </div>
                        <div class="card-body" style="max-height:260px;">
                            <canvas id="chartLaporanPerBulan" height="40"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="card" onclick="window.location.href='<?= base_url('admin/kelola_hukuman_disiplin') ?>'" style="cursor: pointer;">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Durasi Hukuman Disiplin Pegawai</span>
                            <a href="<?= base_url('admin/kelola_hukuman_disiplin') ?>"
                                class="btn btn-sm btn-primary" title="Lihat" onclick="event.stopPropagation();">
                                <i class="bi bi-eye"></i><span class="d-none d-md-inline ms-1">Lihat</span>
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="hukuman-carousel-container">
                                <?php if (!empty($list_hukuman)): ?>
                                    <div class="hukuman-carousel" id="hukumanCarousel">
                                        <?php foreach ($list_hukuman as $index => $row): ?>
                                            <?php
                                            $today = date('Y-m-d');
                                            if (strtotime($today) < strtotime($row['tanggal_mulai'])) {
                                                $sisa = (strtotime($row['tanggal_berakhir']) - strtotime($row['tanggal_mulai'])) / 86400;
                                            } elseif (strtotime($today) > strtotime($row['tanggal_berakhir'])) {
                                                $sisa = 0;
                                            } else {
                                                $sisa = (strtotime($row['tanggal_berakhir']) - strtotime($today)) / 86400;
                                            }
                                            $sisa = $sisa < 0 ? 0 : $sisa;
                                            ?>
                                            <div class="hukuman-item <?= $index === 0 ? 'active' : '' ?>"
                                                data-index="<?= $index ?>">
                                                <div class="hukuman-content">
                                                    <div class="hukuman-header">
                                                        <h6 class="hukuman-title">
                                                            <?= strlen($row['nama']) > 25 ? esc(substr($row['nama'], 0, 25)) . '...' : esc($row['nama']) ?>
                                                        </h6>
                                                        <div class="badge-container">
                                                            <span
                                                                class="duration-badge <?= $sisa <= 7 ? 'bg-danger' : ($sisa <= 30 ? 'bg-warning' : 'bg-success') ?> text-white">
                                                                <?= $sisa ?> hari
                                                            </span>
                                                            <?php if ($row['status'] === 'pending'): ?>
                                                                <span class="status-badge bg-warning text-dark">
                                                                    Pending
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="hukuman-details">
                                                        <p class="hukuman-position">
                                                            <i class="bi bi-briefcase"></i>
                                                            <?= strlen($row['jabatan']) > 30 ? esc(substr($row['jabatan'], 0, 30)) . '...' : esc($row['jabatan']) ?>
                                                        </p>
                                                        <p class="hukuman-period">
                                                            <i class="bi bi-calendar-range"></i>
                                                            <?= date('d M Y', strtotime($row['tanggal_mulai'])) ?> -
                                                            <?= date('d M Y', strtotime($row['tanggal_berakhir'])) ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="no-hukuman">
                                        <i class="bi bi-check-circle text-muted"></i>
                                        <p class="text-muted">Tidak ada data hukuman aktif</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($list_hukuman)): ?>
                                <div class="carousel-indicators">
                                    <?php foreach ($list_hukuman as $index => $row): ?>
                                        <span class="indicator <?= $index === 0 ? 'active' : '' ?>"
                                            onclick="event.stopPropagation(); goToHukuman(<?= $index ?>)"></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card" onclick="window.location.href='<?= base_url('admin/kelola_laporan') ?>'" style="cursor: pointer;">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Laporan Pending</span>
                            <a href="<?= base_url('admin/kelola_laporan') ?>" class="btn btn-sm btn-primary" title="Lihat" onclick="event.stopPropagation();">
                                <i class="bi bi-eye"></i><span class="d-none d-md-inline ms-1">Lihat</span>
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="laporan-carousel-container">
                                <?php if (!empty($laporan_terbaru)): ?>
                                    <div class="laporan-carousel" id="laporanCarousel">
                                        <?php foreach ($laporan_terbaru as $index => $row): ?>
                                            <div class="laporan-item <?= $index === 0 ? 'active' : '' ?>"
                                                data-index="<?= $index ?>">
                                                <div class="laporan-content">
                                                    <div class="laporan-header">
                                                        <h6 class="laporan-title">
                                                            <?= strlen($row["nama_laporan"]) > 25 ? esc(substr($row["nama_laporan"], 0, 25)) . '...' : esc($row["nama_laporan"]) ?>
                                                        </h6>
                                                        <span class="status-badge bg-warning text-dark">
                                                            <?= getStatusIndo($row["status"]); ?>
                                                        </span>
                                                    </div>
                                                    <div class="laporan-details">
                                                        <p class="laporan-sender">
                                                            <i class="bi bi-person"></i>
                                                            <?= esc($row["nama_lengkap"]); ?>
                                                        </p>
                                                        <p class="laporan-date">
                                                            <i class="bi bi-calendar"></i>
                                                            <?= date('d M Y H:i', strtotime($row["created_at"])); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="no-laporan">
                                        <i class="bi bi-inbox text-muted"></i>
                                        <p class="text-muted">Tidak ada laporan pending</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($laporan_terbaru)): ?>
                                <div class="carousel-indicators">
                                    <?php foreach ($laporan_terbaru as $index => $row): ?>
                                        <span class="indicator <?= $index === 0 ? 'active' : '' ?>"
                                            onclick="event.stopPropagation(); goToLaporan(<?= $index ?>)"></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card" onclick="window.location.href='<?= base_url('admin/rekap_kedisiplinan') ?>'" style="cursor: pointer;">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>Status Disiplin Bulan <?= date('M Y') ?></span>
                            <a href="<?= base_url('admin/rekap_kedisiplinan') ?>"
                                class="btn btn-sm btn-primary" title="Lihat" onclick="event.stopPropagation();">
                                <i class="bi bi-eye"></i><span class="d-none d-md-inline ms-1">Lihat</span>
                            </a>
                        </div>
                        <div class="card-body" style="max-height:200px; overflow-y: auto;">
                            <div class="status-disiplin-list">
                                <?php if (!empty($status_disiplin_bulan_ini)): ?>
                                    <?php foreach ($status_disiplin_bulan_ini as $satker): ?>
                                        <div class="status-disiplin-item">
                                            <div class="status-disiplin-header">
                                                <h6 class="status-disiplin-title"><?= esc($satker['nama_satker']) ?></h6>
                                                <div class="status-disiplin-icons">
                                                    <?php if ($satker['has_disiplin_approved']): ?>
                                                        <span class="status-icon">✅</span>
                                                    <?php elseif ($satker['has_disiplin_pending']): ?>
                                                        <span class="status-icon">⏳</span>
                                                    <?php endif; ?>

                                                    <?php if ($satker['has_apel_approved']): ?>
                                                        <span class="status-icon">🍎</span>
                                                    <?php elseif ($satker['has_apel_pending']): ?>
                                                        <span class="status-icon">⏳</span>
                                                    <?php endif; ?>

                                                    <?php if (!$satker['has_disiplin_approved'] && !$satker['has_disiplin_pending'] && !$satker['has_apel_approved'] && !$satker['has_apel_pending']): ?>
                                                        <span class="status-icon">-</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="status-disiplin-details">
                                                <small class="text-muted">
                                                    <?php if ($satker['has_disiplin_approved'] && $satker['has_apel_approved']): ?>
                                                        Laporan Disiplin & Apel diterima
                                                    <?php elseif ($satker['has_disiplin_approved']): ?>
                                                        Laporan Disiplin diterima
                                                    <?php elseif ($satker['has_apel_approved']): ?>
                                                        Laporan Apel diterima
                                                    <?php elseif ($satker['has_disiplin_pending'] || $satker['has_apel_pending']): ?>
                                                        Menunggu persetujuan
                                                    <?php else: ?>
                                                        Belum ada laporan
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="no-status-disiplin">
                                        <i class="bi bi-info-circle text-muted"></i>
                                        <p class="text-muted">Tidak ada data status disiplin bulan ini</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include(APPPATH . 'Views/components/bottom_nav_admin.php'); ?>
</body>

</html>