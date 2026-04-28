<!--
    Author: MUHAMMAD ALIF QADRI 2025
    Licensed to: PTA MAKASSAR DAN SELURUH JAJARANNYA
    Copyright (c) 2025
-->
<!-- Anti-flash dark mode -->
<script>(function(){try{if(localStorage.getItem('loginDark')==='1')document.documentElement.classList.add('login-dark');}catch(e){}})();</script>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MASSIPA</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">
    <link rel="shortcut icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">
    
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="<?= base_url('assets/css/guest/HalamanLogin.css') ?>">
</head>
<body>
<div class="container-fluid p-0">
 <div class="row g-0 split-login">
  <!-- KIRI: Form Login -->
  <div class="col-lg-6 col-12 login-left">
    <div class="login-card">
      <!-- LOGO / Brand -->
      <img src="<?= base_url('assets/img/logo_landscape.webp') ?>" alt="Logo" class="login-logo">
      
      <!-- Header Section -->
      <div class="login-header">
        <h1 class="login-title">Assalamualaikum, Bagaimana Hari Anda?</h1>
        <p class="subtitle">Selamat Datang di Sistem Manajemen Sarana Disiplin Pegawai dan Hakim</p>
      </div>
      
      <!-- Flash message -->
      <?php if (session()->getFlashdata("msg")): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= esc(session()->getFlashdata("msg")) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      <?php if (session()->getFlashdata("msg_success")): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?= esc(session()->getFlashdata("msg_success")) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      <?php if (session()->getFlashdata("msg_blocked")): ?>
        <div class="alert alert-dismissible fade show" role="alert" style="background-color: #6f42c1; color: #ffffff; border-color: #6f42c1;">
          <strong><i class="bi bi-exclamation-triangle-fill"></i> Akses Diblokir:</strong><br>
          <?= esc(session()->getFlashdata("msg_blocked")) ?>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>
      <?php if (isset($isBlocked) && $isBlocked && $blockedUntil): ?>
        <div class="alert" role="alert" id="blockedAlert" style="background-color: #6f42c1; color: #ffffff; border-color: #6f42c1;">
          <strong><i class="bi bi-exclamation-triangle-fill"></i> Akses Diblokir:</strong><br>
          Terlalu banyak percobaan login gagal. Silakan coba lagi dalam <strong id="countdownTimer">2:00</strong> menit.
        </div>
      <?php endif; ?>
      
      <!-- Login Form -->
      <form method="POST" action="<?= base_url('login/auth') ?>" autocomplete="off" id="loginForm" <?= (isset($isBlocked) && $isBlocked) ? 'onsubmit="return false;"' : '' ?>>
        <?= csrf_field() ?>
        <div class="mb-3">
          <label for="username" class="form-label">Nama Pengguna</label>
          <input type="text" class="form-control" id="username" name="username"
            value="<?= esc($remembered_username ?? old('username') ?? '') ?>"
            required placeholder="Masukkan nama pengguna"
            <?= (isset($isBlocked) && $isBlocked) ? 'disabled' : '' ?>>
        </div>
        
        <div class="mb-3">
          <label for="password" class="form-label">Kata Sandi</label>
          <div class="input-group">
            <input type="password" class="form-control" id="password" name="password" required placeholder="Masukkan kata sandi" <?= (isset($isBlocked) && $isBlocked) ? 'disabled' : '' ?>>
            <span class="input-group-text bg-white show-hide-icon" id="togglePassword">
              <i class="bi bi-eye"></i>
            </span>
          </div>
        </div>
        
        <div class="row align-items-center mb-3">
          <div class="col-7">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember"
                <?= !empty($remembered_username) ? 'checked' : '' ?>
                <?= (isset($isBlocked) && $isBlocked) ? 'disabled' : '' ?>>
              <label class="form-check-label login-inline-help" for="remember">
                Ingat saya
              </label>
            </div>
          </div>
          <div class="col-5 text-end">
            <a href="<?= base_url('forgot-password') ?>" class="login-inline-forgot">Lupa Password</a>
          </div>
        </div>
        
        <div class="d-grid">
          <button type="submit" class="btn btn-login" id="loginButton" <?= (isset($isBlocked) && $isBlocked) ? 'disabled' : '' ?>>Masuk</button>
        </div>
      </form>
      
      <div class="login-footer mt-3">
        <span>© <?= date("Y"); ?> Pengadilan Tinggi Agama Makassar</span>
      </div>
    </div>
  </div>
  <!-- KANAN: Ilustrasi/Gambar -->
  <div class="col-lg-6 login-right d-none d-lg-flex">
    <img src="<?= base_url('assets/img/Satker/PA_Bantaeng.webp') ?>" class="slider-img active" id="slide-0" alt="PA Bantaeng">
    <img src="<?= base_url('assets/img/Satker/PA_Barru.webp') ?>" class="slider-img" id="slide-1" alt="PA Barru">
    <img src="<?= base_url('assets/img/Satker/PA_Belopa.webp') ?>" class="slider-img" id="slide-2" alt="PA Belopa">
    <img src="<?= base_url('assets/img/Satker/PA_Bulukumba.webp') ?>" class="slider-img" id="slide-3" alt="PA Bulukumba">
    <img src="<?= base_url('assets/img/Satker/PA_Enrekang.webp') ?>" class="slider-img" id="slide-4" alt="PA Enrekang">
    <img src="<?= base_url('assets/img/Satker/PA_Jeneponto.webp') ?>" class="slider-img" id="slide-5" alt="PA Jeneponto">
    <img src="<?= base_url('assets/img/Satker/PA_Makale.webp') ?>" class="slider-img" id="slide-6" alt="PA Makale">
    <img src="<?= base_url('assets/img/Satker/PA_Makassar.webp') ?>" class="slider-img" id="slide-7" alt="PA Makassar">
    <img src="<?= base_url('assets/img/Satker/PA_Malili.webp') ?>" class="slider-img" id="slide-8" alt="PA Malili">
    <img src="<?= base_url('assets/img/Satker/PA_Maros.webp') ?>" class="slider-img" id="slide-9" alt="PA Maros">
    <img src="<?= base_url('assets/img/Satker/PA_Masamba.webp') ?>" class="slider-img" id="slide-10" alt="PA Masamba">
    <img src="<?= base_url('assets/img/Satker/PA_Palopo.webp') ?>" class="slider-img" id="slide-11" alt="PA Palopo">
    <img src="<?= base_url('assets/img/Satker/PA_Pangkajene.webp') ?>" class="slider-img" id="slide-12" alt="PA Pangkajene">
    <img src="<?= base_url('assets/img/Satker/PA_Parepare.webp') ?>" class="slider-img" id="slide-13" alt="PA Parepare">
    <img src="<?= base_url('assets/img/Satker/PA_Pinrang.webp') ?>" class="slider-img" id="slide-14" alt="PA Pinrang">
    <img src="<?= base_url('assets/img/Satker/PA_Selayar.webp') ?>" class="slider-img" id="slide-15" alt="PA Selayar">
    <img src="<?= base_url('assets/img/Satker/PA_Sengkang.webp') ?>" class="slider-img" id="slide-16" alt="PA Sengkang">
    <img src="<?= base_url('assets/img/Satker/PA_Sidrap.webp') ?>" class="slider-img" id="slide-17" alt="PA Sidrap">
    <img src="<?= base_url('assets/img/Satker/PA_Sinjai.webp') ?>" class="slider-img" id="slide-18" alt="PA Sinjai">
    <img src="<?= base_url('assets/img/Satker/PA_Sungguminasa.webp') ?>" class="slider-img" id="slide-19" alt="PA Sungguminasa">
    <img src="<?= base_url('assets/img/Satker/PA_Takalar.webp') ?>" class="slider-img" id="slide-20" alt="PA Takalar">
    <img src="<?= base_url('assets/img/Satker/PA_Watampone.webp') ?>" class="slider-img" id="slide-21" alt="PA Watampone">
    <img src="<?= base_url('assets/img/Satker/PA_Watansoppeng.webp') ?>" class="slider-img" id="slide-22" alt="PA Watansoppeng">
    <img src="<?= base_url('assets/img/Satker/PTA_Makassar.webp') ?>" class="slider-img" id="slide-23" alt="PTA Makassar">
  </div>
 </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/guest/HalamanLogin.js') ?>"></script>
<?php if (isset($isBlocked) && $isBlocked && $blockedUntil): ?>
<script>
  // Countdown timer untuk blocked login
  (function() {
    const blockedUntil = <?= (int) $blockedUntil ?>;
    const countdownElement = document.getElementById('countdownTimer');
    
    function updateCountdown() {
      const now = Math.floor(Date.now() / 1000);
      const remaining = blockedUntil - now;
      
      if (remaining <= 0) {
        // Block expired, reload page
        countdownElement.textContent = '0:00';
        location.reload();
        return;
      }
      
      const minutes = Math.floor(remaining / 60);
      const seconds = remaining % 60;
      countdownElement.textContent = minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
    }
    
    // Update setiap detik
    updateCountdown();
    setInterval(updateCountdown, 1000);
  })();
</script>
<?php endif; ?>

<!-- Dark Mode Toggle -->
<button id="loginDarkToggle" title="Toggle Dark Mode" aria-label="Toggle Dark Mode">
  <i class="bi bi-moon-fill" id="loginDarkIcon"></i>
</button>
<script>
(function(){
  function applyLoginDark(isDark) {
    document.documentElement.classList.toggle('login-dark', isDark);
    var icon = document.getElementById('loginDarkIcon');
    if (icon) icon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
    try { localStorage.setItem('loginDark', isDark ? '1' : '0'); } catch(e) {}
  }
  document.addEventListener('DOMContentLoaded', function(){
    var isDark = false;
    try { isDark = localStorage.getItem('loginDark') === '1'; } catch(e) {}
    applyLoginDark(isDark);
    var btn = document.getElementById('loginDarkToggle');
    if (btn) btn.addEventListener('click', function(){
      applyLoginDark(!document.documentElement.classList.contains('login-dark'));
    });
  });
})();
</script>
</body>
</html>