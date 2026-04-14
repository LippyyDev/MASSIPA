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
<div class="sidebar" id="sidebar" style="display: flex; flex-direction: column; height: 100vh;">
    <div class="sidebar-header" style="text-align:center; flex: 0 0 auto; min-height: 70px; display: flex; align-items: center; justify-content: center; margin-bottom: 0; padding-bottom: 0; padding-top: 28px;">
        <img src="<?= base_url('assets/img/logo_landscape.webp') ?>" alt="Logo MADIPA"
            style="max-width: 170px; height: auto; margin-bottom: 0;">
    </div>
    <ul class="sidebar-menu mt-2">
        <div class="sidebar-section-title">Umum</div>
        <li><a href="<?= base_url('user/beranda_user') ?>"
                class="<?= ($uri->getSegment(2) == 'beranda_user') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'beranda_user') ? 'bi-house-door-fill' : 'bi-house-door' ?>"></i>
                <span>Beranda</span></a></li>
        <li><a href="<?= base_url('user/profil_user') ?>" class="<?= ($uri->getSegment(2) == 'profil_user') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'profil_user') ? 'bi-person-fill' : 'bi-person' ?>"></i>
                <span>Profile</span></a></li>

        <div class="sidebar-section-title">Data Pegawai & Disiplin</div>
        <li><a href="<?= base_url('user/daftar_pegawai') ?>"
                class="<?= (in_array($uri->getSegment(2), ['daftar_pegawai', 'mutasi_pegawai'])) ? 'active' : '' ?>">
                <i class="bi <?= (in_array($uri->getSegment(2), ['daftar_pegawai', 'mutasi_pegawai'])) ? 'bi-person-badge-fill' : 'bi-person-badge' ?>"></i>
                <span>Daftar Pegawai</span></a></li>
        <li><a href="<?= base_url('user/kelola_disiplin') ?>"
                class="<?= (in_array($uri->getSegment(2), ['kelola_disiplin', 'inputdisiplin'])) ? 'active' : '' ?>">
                <i class="bi <?= (in_array($uri->getSegment(2), ['kelola_disiplin', 'inputdisiplin'])) ? 'bi-journal-check' : 'bi-journal' ?>"></i>
                <span>Kelola Disiplin</span></a></li>
        <li><a href="<?= base_url('user/kelola_hukuman_disiplin') ?>"
                class="<?= ($uri->getSegment(2) == 'kelola_hukuman_disiplin') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'kelola_hukuman_disiplin') ? 'bi-exclamation-diamond-fill' : 'bi-exclamation-diamond' ?>"></i>
                <span>Kelola Hukuman</span></a></li>
        <li><a href="<?= base_url('user/statusdisiplinpegawai') ?>"
                class="<?= ($uri->getSegment(2) == 'statusdisiplinpegawai') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'statusdisiplinpegawai') ? 'bi-calendar-check-fill' : 'bi-calendar-check' ?>"></i>
                <span>Status Disiplin</span></a></li>

        <div class="sidebar-section-title">Laporan & Administrasi</div>
        <li><a href="<?= base_url('user/inputtandatanganuser') ?>"
                class="<?= ($uri->getSegment(2) == 'inputtandatanganuser') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'inputtandatanganuser') ? 'bi-pencil-square' : 'bi-pencil' ?>"></i>
                <span>Kelola Tanda Tangan</span></a></li>
        <li><a href="<?= base_url('user/rekaplaporandisiplin') ?>"
                class="<?= ($uri->getSegment(2) == 'rekaplaporandisiplin') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'rekaplaporandisiplin') ? 'bi-file-earmark-text-fill' : 'bi-file-earmark-text' ?>"></i>
                <span>Rekap Laporan</span></a></li>
        <li><a href="<?= base_url('user/kirimlaporan') ?>"
                class="<?= ($uri->getSegment(2) == 'kirimlaporan') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'kirimlaporan') ? 'bi-upload' : 'bi-upload' ?>"></i> <span>Kirim
                    Laporan</span></a></li>

        <div class="sidebar-section-title">Lainnya</div>
        <li><a href="<?= base_url('user/notifikasiuser') ?>"
                class="<?= ($uri->getSegment(2) == 'notifikasiuser') ? 'active' : '' ?>">
                <i class="bi <?= ($uri->getSegment(2) == 'notifikasiuser') ? 'bi-bell-fill' : 'bi-bell' ?>"></i>
                <span>Notifikasi</span></a></li>
        <li><a href="<?= base_url('logout') ?>">
                <i class="bi bi-box-arrow-right"></i> <span>Logout</span></a></li>
    </ul>
</div>