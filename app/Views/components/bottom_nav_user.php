<?php
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= base_url('assets/css/components/bottom_nav_user.css') ?>">

<nav class="bottom-nav-user d-md-none">
    <div class="bottom-nav-container">
        <a href="<?= base_url('user/beranda_user') ?>" class="nav-item" data-route="user/beranda_user">
            <i class="bi bi-house-door"></i>
            <span class="nav-label">Home</span>
        </a>

        <a href="<?= base_url('user/notifikasiuser') ?>" class="nav-item" data-route="user/notifikasiuser">
            <i class="bi bi-bell"></i>
            <span class="nav-label">Notifikasi</span>
        </a>

        <a href="<?= base_url('user/inputdisiplin') ?>" class="nav-item" data-route="user/inputdisiplin" data-route-alt="user/kelola_disiplin">
            <i class="bi bi-plus-lg"></i>
            <span class="nav-label">Tambah</span>
        </a>

        <a href="<?= base_url('user/kirimlaporan') ?>" class="nav-item" data-route="user/kirimlaporan">
            <i class="bi bi-file-earmark-text"></i>
            <span class="nav-label">Laporan</span>
        </a>

        <a href="<?= base_url('user/profil_user') ?>" class="nav-item" data-route="user/profil_user">
            <i class="bi bi-person"></i>
            <span class="nav-label">Profil</span>
        </a>
    </div>
</nav>

<script>
    (function() {
        const currentPath = window.location.pathname;
        const navItems = document.querySelectorAll('.bottom-nav-user .nav-item');
        
        navItems.forEach(item => {
            const route = item.getAttribute('data-route');
            const routeAlt = item.getAttribute('data-route-alt');
            
            if (route && (currentPath.includes(route) || (routeAlt && currentPath.includes(routeAlt)))) {
                item.classList.add('active');
            }
        });
    })();
</script>

