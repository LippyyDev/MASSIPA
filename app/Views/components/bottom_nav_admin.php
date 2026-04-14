<?php
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= base_url('assets/css/components/bottom_nav_admin.css') ?>">

<nav class="bottom-nav-admin d-md-none">
    <div class="bottom-nav-container">
        <a href="<?= base_url('admin/dashboard') ?>" class="nav-item" data-route="admin/dashboard">
            <i class="bi bi-house-door"></i>
            <span class="nav-label">Home</span>
        </a>

        <a href="<?= base_url('admin/notifikasi') ?>" class="nav-item" data-route="admin/notifikasi">
            <i class="bi bi-bell"></i>
            <span class="nav-label">Notifikasi</span>
        </a>

        <a href="<?= base_url('admin/tracking') ?>" class="nav-item" data-route="admin/tracking">
            <i class="bi bi-search"></i>
            <span class="nav-label">Search</span>
        </a>

        <a href="<?= base_url('admin/kelola_laporan') ?>" class="nav-item" data-route="admin/kelola_laporan">
            <i class="bi bi-file-earmark-text"></i>
            <span class="nav-label">Laporan</span>
        </a>

        <a href="<?= base_url('admin/profil') ?>" class="nav-item" data-route="admin/profil">
            <i class="bi bi-person"></i>
            <span class="nav-label">Profil</span>
        </a>
    </div>
</nav>

<script>
    (function() {
        const currentPath = window.location.pathname;
        const navItems = document.querySelectorAll('.bottom-nav-admin .nav-item');
        
        navItems.forEach(item => {
            const route = item.getAttribute('data-route');
            if (route && currentPath.includes(route)) {
                item.classList.add('active');
            }
        });
    })();
</script>

