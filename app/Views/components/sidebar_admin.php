<?php
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
<style>
.sidebar-section-title {
    font-size: 0.78em;
    font-weight: bold;
    color: #a0a0b0;
    margin: 18px 0 6px 18px;
    letter-spacing: 1px;
    text-transform: uppercase;
}
</style>
<?php $uri = service('uri'); ?>
<?php $active = isset($active) ? $active : null; ?>
<div class="sidebar" id="sidebar-admin" style="display: flex; flex-direction: column; height: 100vh;">
    <div class="sidebar-header" style="text-align:center; flex: 0 0 auto; min-height: 70px; display: flex; align-items: center; justify-content: center; margin-bottom: 0; padding-bottom: 0; padding-top: 28px;">
        <img src="<?= base_url('assets/img/logo_landscape.webp') ?>" alt="Logo MADIPA"
            style="max-width: 170px; height: auto; margin-bottom: 0;">
    </div>
    <ul class="sidebar-menu mt-2">
        <div class="sidebar-section-title">Umum</div>
        <li><a href="<?= base_url('admin/dashboard') ?>"
                class="<?= ($active == 'admin/dashboard' || $uri->getSegment(2) == 'dashboard') ? 'active' : '' ?>">
                <i class="bi <?= ($active == 'admin/dashboard' || $uri->getSegment(2) == 'dashboard') ? 'bi-house-door-fill' : 'bi-house-door' ?>"></i>
                <span>Beranda</span></a></li>
        <li><a href="<?= base_url('admin/profil') ?>"
                class="<?= ($active == 'admin/profil' || $uri->getSegment(2) == 'profil') ? 'active' : '' ?>">
                <i class="bi <?= ($active == 'admin/profil' || $uri->getSegment(2) == 'profil') ? 'bi-person-fill' : 'bi-person' ?>"></i>
                <span>Profile</span></a></li>

        <div class="sidebar-section-title">Manajemen Data</div>
        <li><a href="<?= base_url('admin/kelola_user') ?>"
                class="<?= ($active == 'admin/kelola_user' || $uri->getSegment(2) == 'kelola_user') ? 'active' : '' ?>">
                <i class="bi <?= ($active == 'admin/kelola_user' || $uri->getSegment(2) == 'kelola_user') ? 'bi-people-fill' : 'bi-people' ?>"></i>
                <span>Kelola User</span></a></li>
        <li><a href="<?= base_url('admin/input_pegawai') ?>"
                class="<?= ($active == 'admin/pegawai' || in_array($uri->getSegment(2), ['input_pegawai', 'mutasi_pegawai'])) ? 'active' : '' ?>">
                <i class="bi <?= ($active == 'admin/pegawai' || in_array($uri->getSegment(2), ['input_pegawai', 'mutasi_pegawai'])) ? 'bi-person-badge-fill' : 'bi-person-badge' ?>"></i>
                <span>Kelola Pegawai</span></a></li>
        <li><a href="<?= base_url('admin/kelola_satker') ?>"
                class="<?= ($uri->getSegment(2) == 'kelola_satker') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'kelola_satker') ? 'bi-building-fill' : 'bi-building' ?>"></i>
                <span>Kelola Satker</span></a></li>
        <li><a href="<?= base_url('admin/kelola_disiplin') ?>"
                class="<?= ($uri->getSegment(2) == 'kelola_disiplin') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'kelola_disiplin') ? 'bi-shield-exclamation' : 'bi-shield-exclamation' ?>"></i>
                <span>Kelola Disiplin</span></a></li>
        <li><a href="<?= base_url('admin/tracking') ?>"
                class="<?= ($uri->getSegment(2) == 'tracking') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'tracking') ? 'bi-search' : 'bi-search' ?>"></i>
                <span>Tracking Disiplin</span></a></li>

        <div class="sidebar-section-title">Laporan & Administrasi</div>
        <li><a href="<?= base_url('admin/input_tanda_tangan') ?>"
                class="<?= ($uri->getSegment(2) == 'input_tanda_tangan') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'input_tanda_tangan') ? 'bi-pencil-square' : 'bi-pencil' ?>"></i>
                <span>Kelola Tanda Tangan</span></a></li>
        <li><a href="<?= base_url('admin/kelola_laporan') ?>"
                class="<?= ($uri->getSegment(2) == 'kelola_laporan') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'kelola_laporan') ? 'bi-file-earmark-text-fill' : 'bi-file-earmark-text' ?>"></i>
                <span>Kelola Laporan</span></a></li>
        <li><a href="<?= base_url('admin/kelola_hukuman_disiplin') ?>"
                class="<?= ($uri->getSegment(2) == 'kelola_hukuman_disiplin') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'kelola_hukuman_disiplin') ? 'bi-exclamation-diamond-fill' : 'bi-exclamation-diamond' ?>"></i>
                <span>Kelola Hukuman</span></a></li>
        <li><a href="<?= base_url('admin/arsip_laporan') ?>"
                class="<?= ($uri->getSegment(2) == 'arsip_laporan') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'arsip_laporan') ? 'bi-archive' : 'bi-archive' ?>"></i>
                <span>Arsip Laporan</span></a></li>
        <li><a href="<?= base_url('admin/rekap_kedisiplinan') ?>"
                class="<?= ($uri->getSegment(2) == 'rekap_kedisiplinan') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'rekap_kedisiplinan') ? 'bi-calendar-check-fill' : 'bi-calendar-check' ?>"></i>
                <span>Status Disiplin</span></a></li>
        <li><a href="<?= base_url('admin/rekap_user_satker') ?>"
                class="<?= ($uri->getSegment(2) == 'rekap_user_satker') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'rekap_user_satker') ? 'bi-people-fill' : 'bi-people' ?>"></i>
                <span>Rekap Pegawai</span></a></li>

        <div class="sidebar-section-title">Lain lain</div>
        <li><a href="<?= base_url('admin/pengaturan') ?>"
                class="<?= ($uri->getSegment(2) == 'pengaturan') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'pengaturan') ? 'bi-gear' : 'bi-gear' ?>"></i>
                <span>Pengaturan</span></a></li>
        <li><a href="<?= base_url('admin/notifikasi') ?>"
                class="<?= ($uri->getSegment(2) == 'notifikasi') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'notifikasi') ? 'bi-bell-fill' : 'bi-bell' ?>"></i>
                <span>Notifikasi</span></a></li>
        <li><a href="<?= base_url('logout') ?>">
                <i class="bi bi-box-arrow-right"></i> <span>Logout</span></a></li>
    </ul>
</div>