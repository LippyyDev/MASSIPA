<!-- Anti-flash dark mode -->
<script>(function(){try{if(localStorage.getItem('loginDark')==='1')document.documentElement.classList.add('login-dark');}catch(e){}})();</script>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - MASSIPA</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">
    <link rel="shortcut icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/guest/HalamanLogin.css') ?>">
</head>
<body>
<div class="container-fluid p-0">
    <div class="row g-0 split-login">
        <div class="col-lg-6 col-12 login-left">
            <div class="login-card">
                <img src="<?= base_url('assets/img/logo_landscape.webp') ?>" alt="Logo" class="login-logo">

                <div class="login-header">
                    <h1 class="login-title">Reset Password</h1>
                    <p class="subtitle">Buat password baru untuk akun <?= esc($email ?? '') ?>.</p>
                </div>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('errors')): ?>
                    <?php $errors = session()->getFlashdata('errors'); ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php foreach ($errors as $err): ?>
                            <div><?= esc($err) ?></div>
                        <?php endforeach; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= base_url('reset-password/submit') ?>" autocomplete="off">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= esc($token ?? '') ?>">
                    <div class="mb-3">
                        <label for="password" class="form-label">Password Baru</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required minlength="8" placeholder="Minimal 8 karakter">
                            <button class="input-group-text bg-white" type="button" data-toggle-password="password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required minlength="8" placeholder="Ulangi password baru">
                            <button class="input-group-text bg-white" type="button" data-toggle-password="password_confirm">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-login">Simpan Password</button>
                    </div>
                    <div class="small text-muted mt-2">Gunakan kombinasi huruf, angka, dan simbol agar lebih aman.</div>
                </form>

                <div class="login-footer mt-3 text-center">
                    <div><a href="<?= base_url('forgot-password') ?>" class="login-inline-forgot">Kembali ke Lupa Password</a></div>
                    <div class="mt-1">© <?= date('Y'); ?> Pengadilan Tinggi Agama Makassar</div>
                </div>
            </div>
        </div>

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
<script src="<?= base_url('assets/js/guest/ResetPassword.js') ?>"></script>

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

