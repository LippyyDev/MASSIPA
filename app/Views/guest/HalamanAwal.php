<!--
    Author: MUHAMMAD ALIF QADRI 2025
    Licensed to: PTA MAKASSAR DAN SELURUH JAJARANNYA
    Copyright (c) 2025
-->
<!DOCTYPE html>
<html lang="id" class="light-style layout-navbar-fixed layout-wide" dir="ltr" data-theme="theme-default">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, minimum-scale=1.0, maximum-scale=5.0">
    <title>MASSIPA</title>
    <meta name="description" content="MASSIPA - Sistem Manajemen Kedisiplinan Hakim & Pegawai Lingkungan Pengadilan Tinggi Agama Makassar, Sulawesi Selatan">
    <meta name="keywords" content="Sistem Disiplin Hakim, Pengadilan, Laporan Kedisiplinan">
    <meta name="author" content="Sistem Disiplin Hakim">
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=Poppins:wght@300;400;500;600;700;800&family=Orbitron:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <?php $halamanAwalCssPath = FCPATH . 'assets/css/guest/HalamanAwal.css'; ?>
    <link rel="preload" as="style" href="<?= base_url('assets/css/guest/HalamanAwal.css') . (is_file($halamanAwalCssPath) ? ('?v=' . filemtime($halamanAwalCssPath)) : '') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/guest/HalamanAwal.css') . (is_file($halamanAwalCssPath) ? ('?v=' . filemtime($halamanAwalCssPath)) : '') ?>">
    <link rel="preload" as="image" href="<?= base_url('assets/img/ptamks.webp') ?>">
</head>
<body>
    <?= view('components/navbar_guest') ?>
    <main class="landing-main">
        <section id="beranda" class="landing-hero d-flex align-items-center">
            <img
                src="<?= base_url('assets/img/ptamks.webp') ?>"
                alt="Pengadilan Tinggi Agama Makassar"
                style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;object-position:center 20%;z-index:0;"
                loading="eager"
                decoding="async"
            >
            <div class="hero-overlay"></div>
            <div class="container" style="position:relative; z-index:2;">
                    <div class="col-12 col-md-10 col-lg-8 me-auto text-start js-hero-content d-flex flex-column justify-content-start align-items-start" style="padding-bottom: 2rem;">
                        <h1 class="mb-1 home-title text-start w-100" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: clamp(1.8rem, 4vw, 2.8rem); line-height: 1.2; text-shadow: 0 3px 15px rgba(0,0,0,0.6);" id="typing-title-1">
                            <span class="typing-text-wrapper" style="min-height: 2.4em; display: block;"></span>
                        </h1>
                        <h2 class="mb-4 home-title mt-3 text-start w-100" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: clamp(1rem, 2vw, 1.4rem); text-shadow: 0 2px 10px rgba(0,0,0,0.6);" id="typing-title-2">
                        </h2>
                        
                        <!-- CTA Buttons -->
                        <div class="hero-buttons d-flex mt-4 gap-3 w-100 js-hero-cta">
                            <a href="<?= base_url('login') ?>" class="btn btn-shine d-inline-flex align-items-center justify-content-center fw-semibold text-white px-4 py-3" style="background-color: var(--accent-1); font-family: 'Plus Jakarta Sans', sans-serif; line-height: 1; border-radius: 8px; font-size: 1.05rem; box-shadow: var(--shadow-glow); border: 1px solid var(--accent-1); text-decoration: none;">
                                <i class="bi bi-browser-edge me-2"></i> Gunakan Web
                            </a>
                            <a href="https://drive.google.com/drive/folders/1aHAf9B97UprGSdkAVtuuGagN5jr2urFk?usp=sharing" target="_blank" class="btn btn-shine d-inline-flex align-items-center justify-content-center fw-semibold text-white px-4 py-3" style="background-color: rgba(255,255,255,0.05); font-family: 'Plus Jakarta Sans', sans-serif; line-height: 1; border-radius: 8px; font-size: 1.05rem; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.4); text-decoration: none;">
                                <i class="bi bi-download me-2"></i> Download App
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </section>

        <!-- Satker Marquee Section -->
        <div class="satker-marquee-container" style="overflow: hidden; white-space: nowrap; background: transparent; padding: 1.5rem 0; margin-top: 4rem; margin-bottom: 2rem;">
            <div class="satker-marquee" style="display: inline-block; white-space: nowrap; animation: marquee-scroll 45s linear infinite;">
                <span class="marquee-text" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.55rem; font-weight: 700; color: #8C52FE; letter-spacing: 0.05em; margin: 0 1rem;">
                    Bantaeng &nbsp;&nbsp;&nbsp;&nbsp; Barru &nbsp;&nbsp;&nbsp;&nbsp; Belopa &nbsp;&nbsp;&nbsp;&nbsp; Bulukumba &nbsp;&nbsp;&nbsp;&nbsp; Enrekang &nbsp;&nbsp;&nbsp;&nbsp; Jeneponto &nbsp;&nbsp;&nbsp;&nbsp; Makale &nbsp;&nbsp;&nbsp;&nbsp; Makassar &nbsp;&nbsp;&nbsp;&nbsp; Malili &nbsp;&nbsp;&nbsp;&nbsp; Maros &nbsp;&nbsp;&nbsp;&nbsp; Masamba &nbsp;&nbsp;&nbsp;&nbsp; Palopo &nbsp;&nbsp;&nbsp;&nbsp; Pangkajene &nbsp;&nbsp;&nbsp;&nbsp; Parepare &nbsp;&nbsp;&nbsp;&nbsp; Pinrang &nbsp;&nbsp;&nbsp;&nbsp; Selayar &nbsp;&nbsp;&nbsp;&nbsp; Sengkang &nbsp;&nbsp;&nbsp;&nbsp; Sidrap &nbsp;&nbsp;&nbsp;&nbsp; Sinjai &nbsp;&nbsp;&nbsp;&nbsp; Sungguminasa &nbsp;&nbsp;&nbsp;&nbsp; Takalar &nbsp;&nbsp;&nbsp;&nbsp; Watampone &nbsp;&nbsp;&nbsp;&nbsp; Watansoppeng
                </span>
                <span class="marquee-text" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.55rem; font-weight: 700; color: #8C52FE; letter-spacing: 0.05em; margin: 0 1rem;">
                    Bantaeng &nbsp;&nbsp;&nbsp;&nbsp; Barru &nbsp;&nbsp;&nbsp;&nbsp; Belopa &nbsp;&nbsp;&nbsp;&nbsp; Bulukumba &nbsp;&nbsp;&nbsp;&nbsp; Enrekang &nbsp;&nbsp;&nbsp;&nbsp; Jeneponto &nbsp;&nbsp;&nbsp;&nbsp; Makale &nbsp;&nbsp;&nbsp;&nbsp; Makassar &nbsp;&nbsp;&nbsp;&nbsp; Malili &nbsp;&nbsp;&nbsp;&nbsp; Maros &nbsp;&nbsp;&nbsp;&nbsp; Masamba &nbsp;&nbsp;&nbsp;&nbsp; Palopo &nbsp;&nbsp;&nbsp;&nbsp; Pangkajene &nbsp;&nbsp;&nbsp;&nbsp; Parepare &nbsp;&nbsp;&nbsp;&nbsp; Pinrang &nbsp;&nbsp;&nbsp;&nbsp; Selayar &nbsp;&nbsp;&nbsp;&nbsp; Sengkang &nbsp;&nbsp;&nbsp;&nbsp; Sidrap &nbsp;&nbsp;&nbsp;&nbsp; Sinjai &nbsp;&nbsp;&nbsp;&nbsp; Sungguminasa &nbsp;&nbsp;&nbsp;&nbsp; Takalar &nbsp;&nbsp;&nbsp;&nbsp; Watampone &nbsp;&nbsp;&nbsp;&nbsp; Watansoppeng
                </span>
            </div>
            <style>
                @keyframes marquee-scroll {
                    0% { transform: translateX(0); }
                    100% { transform: translateX(-50%); }
                }
                .satker-marquee:hover {
                    animation-play-state: paused;
                }
            </style>
        </div>

        <section id="tentang" class="landing-section landing-section--about py-2 js-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-12 text-center py-5">
                        <div class="mb-5 d-flex align-items-center justify-content-center gap-3">
                            <span style="height: 1px; width: 60px; background-color: rgb(111, 66, 193);"></span>
                            <span class="fw-bold text-uppercase" style="color: rgb(111, 66, 193); font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: 0.15em; font-size: 1.1rem;">TENTANG MASSIPA</span>
                            <span style="height: 1px; width: 60px; background-color: rgb(111, 66, 193);"></span>
                        </div>
                        <p class="section-paragraph js-reveal" style="font-size: 2.2rem; line-height: 1.5; color: #5a6270; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 400;">
                            Aplikasi <strong style="color: rgb(111, 66, 193); font-weight: 700;">MASSIPA</strong> adalah sistem <strong style="color: #3f4254; font-weight: 700;">Manajemen Sarana Disiplin</strong> terpadu bagi <br>
                            <strong style="color: #212529; font-weight: 800; font-size: 2.6rem;">Pengadilan Tinggi Agama Makassar,</strong> <br>
                            yang dirancang secara khusus agar seluruh Satuan Kerja dapat melakukan <br>
                            <span style="color: rgb(111, 66, 193); font-weight: 600; text-decoration: underline; text-underline-offset: 6px; text-decoration-thickness: 1.5px;">rekapitulasi</span>, <span style="color: rgb(111, 66, 193); font-weight: 600; text-decoration: underline; text-underline-offset: 6px; text-decoration-thickness: 1.5px;">monitoring</span>, dan
                            menjaga <strong style="color: rgb(111, 66, 193); font-weight: 600; text-decoration: underline; text-underline-offset: 6px; text-decoration-thickness: 1.5px;">integritas</strong> <br> 
                            secara <span class="badge rounded-pill" style="background-color: rgb(111, 66, 193); font-weight: 600; font-size: 1.3rem; padding: 0.4em 0.8em; transform: translateY(-4px);">Terpusat</span>
                        </p>
                    </div>
                </div>
            </div>
        </section>
        
        <section id="statistik" class="landing-section landing-section--stats py-5 js-section">
            <div class="container">
                <div class="row g-4 justify-content-center">
                    <div class="col-md-3 col-6">
                        <div class="stat-glass-card p-4 text-center h-100">
                            <div class="stat-angka" data-count="<?= $stat_satker ?? 0; ?>">0</div>
                            <div class="fw-semibold mt-2 stat-label">Satuan Kerja</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-glass-card p-4 text-center h-100">
                            <div class="stat-angka" data-count="<?= $stat_pegawai ?? 0; ?>">0</div>
                            <div class="fw-semibold mt-2 stat-label">Total Pegawai</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-glass-card p-4 text-center h-100">
                            <div class="stat-angka" data-count="<?= $stat_laporan ?? 0; ?>">0</div>
                            <div class="fw-semibold mt-2 stat-label">Total Kedisiplinan</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-glass-card p-4 text-center h-100">
                            <div class="stat-angka" data-count="<?= $stat_mutasi ?? 0; ?>">0</div>
                            <div class="fw-semibold mt-2 stat-label">Total Mutasi</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section id="keunggulan" class="landing-section landing-section--features py-5 js-section">
            <div class="container">
                <div class="row justify-content-center mb-5">
                    <div class="col-12 text-center">
                        <div class="mb-4 d-flex align-items-center justify-content-center gap-3">
                            <span style="height: 1px; width: 60px; background-color: rgb(111, 66, 193);"></span>
                            <span class="fw-bold text-uppercase" style="color: rgb(111, 66, 193); font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: 0.15em; font-size: 1.1rem;">KEUNGGULAN</span>
                            <span style="height: 1px; width: 60px; background-color: rgb(111, 66, 193);"></span>
                        </div>
                    </div>
                </div>

                <div class="row g-4 bento-grid">
                    <div class="col-lg-8">
                        <div class="bento-card p-5 h-100 d-flex flex-column js-reveal" style="background: #ffffff;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="bento-number">01</div>
                                <div class="bento-icon anim-pulse"><i class="bi bi-file-earmark-check"></i></div>
                            </div>
                            <h3 class="bento-title mt-4 mb-3">Format Data yang Lebih Standar <span style="color: rgb(111, 66, 193);">dan Seragam</span></h3>
                            <p class="bento-desc mt-auto mb-0">Semua proses pencatatan dibuat konsisten dengan format yang baku, sehingga input lebih rapi, mudah divalidasi, dan meminimalkan perbedaan antar satuan kerja.</p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="bento-card p-5 h-100 d-flex flex-column js-reveal" style="background: #ffffff;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="bento-number">02</div>
                                <div class="bento-icon anim-rock"><i class="bi bi-shield-check"></i></div>
                            </div>
                            <h3 class="bento-title mt-4 mb-3">Lebih Transparansi & Akuntabilitas</h3>
                            <p class="bento-desc mt-auto mb-0">Riwayat dan status penanganan tersaji jelas dan dapat ditelusuri konsep akuntabilitasnya.</p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="bento-card p-5 h-100 d-flex flex-column js-reveal" style="background: #ffffff;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="bento-number">03</div>
                                <div class="bento-icon anim-slide-x"><i class="bi bi-list-check"></i></div>
                            </div>
                            <h3 class="bento-title mt-4 mb-3">Kelola Laporan Jauh Lebih Mudah</h3>
                            <p class="bento-desc mt-auto mb-0">Laporan masuk terpusat dan tertata, memudahkan review dan pemantauan.</p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="bento-card p-5 h-100 d-flex flex-column js-reveal" style="background: #ffffff;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="bento-number">04</div>
                                <div class="bento-icon anim-search"><i class="bi bi-search"></i></div>
                            </div>
                            <h3 class="bento-title mt-4 mb-3">Pencarian Data Jadi Lebih Cepat</h3>
                            <p class="bento-desc mt-auto mb-0">Pencarian berbasis kata kunci dan filter mempercepat menemukan data.</p>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="bento-card p-5 h-100 d-flex flex-column js-reveal" style="background: #ffffff;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="bento-number">05</div>
                                <div class="bento-icon anim-bounce-up"><i class="bi bi-file-earmark-arrow-up"></i></div>
                            </div>
                            <h3 class="bento-title mt-4 mb-3">Rekap & Ekspor Secara Otomatis</h3>
                            <p class="bento-desc mt-auto mb-0">Rekapitulasi dapat dibuat otomatis dan diekspor mendadak saat dibutuhkan secara rapi.</p>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="bento-card p-5 h-100 js-reveal d-flex flex-column flex-lg-row align-items-lg-center gap-4" style="background: #ffffff;">
                            <div class="flex-grow-1" style="flex: 1;">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div class="bento-number">06</div>
                                    <div class="bento-icon anim-ring d-lg-none"><i class="bi bi-bell-fill"></i></div>
                                </div>
                                <h3 class="bento-title mb-0">Notifikasi Masuk<br><span style="color: rgb(111, 66, 193);">Secara Otomatis</span></h3>
                            </div>
                            <!-- Centered Icon (Desktop Only) -->
                            <div class="d-none d-lg-flex justify-content-center flex-grow-1 my-4 my-lg-0" style="flex: 1;">
                                <div class="bento-icon anim-ring"><i class="bi bi-bell-fill" style="font-size: 3.5rem;"></i></div>
                            </div>
                            <!-- Right aligned text -->
                            <div class="text-lg-end flex-grow-1" style="flex: 1;">
                                <p class="bento-desc mb-0 pb-0">Sistem memberikan notifikasi otomatis ketika ada laporan masuk atau pembaruan status melalui email atau di dalam sistem itu sendiri.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="faq" class="landing-section landing-section--faq py-5 js-section">
            <div class="container">
                <div class="row justify-content-center mb-5">
                    <div class="col-12 text-center">
                        <div class="mb-4 d-flex align-items-center justify-content-center gap-3 js-reveal">
                            <span style="height: 1px; width: 60px; background-color: rgb(111, 66, 193);"></span>
                            <h2 class="fw-bold text-uppercase mb-0" style="color: rgb(111, 66, 193); font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: 0.15em; font-size: 1.1rem;">FAQ</h2>
                            <span style="height: 1px; width: 60px; background-color: rgb(111, 66, 193);"></span>
                        </div>
                    </div>
                </div>
                <div class="row g-4 g-lg-5">
                    <div class="col-md-6 js-reveal">
                        <div class="faq-minimal-item h-100 rounded-4 d-flex flex-column p-4 p-lg-5" style="background: #ffffff; border: 1.5px solid rgba(111, 66, 193, 0.22); box-shadow: 0 8px 32px rgba(111, 66, 193, 0.08), 0 0 0 0.5px rgba(111, 66, 193, 0.06); position: relative; overflow: hidden;">
                            <div class="d-flex align-items-center gap-4 mb-4">
                                <div class="faq-icon text-center flex-shrink-0" style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, rgba(111,66,193,0.12), rgba(167,139,250,0.18)); color: rgb(111, 66, 193); line-height: 52px; font-weight: 800; font-size: 1.6rem; font-family: 'Plus Jakarta Sans', sans-serif; box-shadow: 0 4px 16px rgba(111, 66, 193, 0.18); border: 1px solid rgba(111,66,193,0.15);">Q</div>
                                <h4 class="mb-0" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: #1e1e2d; font-size: 1.35rem; letter-spacing: -0.02em; line-height: 1.4;">Apa itu MASSIPA?</h4>
                            </div>
                            <p class="mb-0 mt-auto" style="font-family: 'Plus Jakarta Sans', sans-serif; line-height: 1.7; font-size: 1.05rem; color: #5a6270;">MASSIPA adalah aplikasi web untuk manajemen, pelaporan, dan monitoring kedisiplinan pegawai & hakim di lingkungan peradilan agama.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6 js-reveal">
                        <div class="faq-minimal-item h-100 rounded-4 d-flex flex-column p-4 p-lg-5" style="background: #ffffff; border: 1.5px solid rgba(111, 66, 193, 0.22); box-shadow: 0 8px 32px rgba(111, 66, 193, 0.08), 0 0 0 0.5px rgba(111, 66, 193, 0.06); position: relative; overflow: hidden;">
                            <div class="d-flex align-items-center gap-4 mb-4">
                                <div class="faq-icon text-center flex-shrink-0" style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, rgba(111,66,193,0.12), rgba(167,139,250,0.18)); color: rgb(111, 66, 193); line-height: 52px; font-weight: 800; font-size: 1.6rem; font-family: 'Plus Jakarta Sans', sans-serif; box-shadow: 0 4px 16px rgba(111, 66, 193, 0.18); border: 1px solid rgba(111,66,193,0.15);">Q</div>
                                <h4 class="mb-0" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: #1e1e2d; font-size: 1.35rem; letter-spacing: -0.02em; line-height: 1.4;">Siapa saja yang dapat mengakses MASSIPA?</h4>
                            </div>
                            <p class="mb-0 mt-auto" style="font-family: 'Plus Jakarta Sans', sans-serif; line-height: 1.7; font-size: 1.05rem; color: #5a6270;">MASSIPA dapat diakses oleh admin serta pihak yang berwenang dari masing-masing Satuan Kerja di lingkungan Pengadilan Tinggi Agama Makassar.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6 js-reveal">
                        <div class="faq-minimal-item h-100 rounded-4 d-flex flex-column p-4 p-lg-5" style="background: #ffffff; border: 1.5px solid rgba(111, 66, 193, 0.22); box-shadow: 0 8px 32px rgba(111, 66, 193, 0.08), 0 0 0 0.5px rgba(111, 66, 193, 0.06); position: relative; overflow: hidden;">
                            <div class="d-flex align-items-center gap-4 mb-4">
                                <div class="faq-icon text-center flex-shrink-0" style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, rgba(111,66,193,0.12), rgba(167,139,250,0.18)); color: rgb(111, 66, 193); line-height: 52px; font-weight: 800; font-size: 1.6rem; font-family: 'Plus Jakarta Sans', sans-serif; box-shadow: 0 4px 16px rgba(111, 66, 193, 0.18); border: 1px solid rgba(111,66,193,0.15);">Q</div>
                                <h4 class="mb-0" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: #1e1e2d; font-size: 1.35rem; letter-spacing: -0.02em; line-height: 1.4;">Apakah data di MASSIPA aman?</h4>
                            </div>
                            <p class="mb-0 mt-auto" style="font-family: 'Plus Jakarta Sans', sans-serif; line-height: 1.7; font-size: 1.05rem; color: #5a6270;">Ya, data Anda dilindungi dengan enkripsi dan standar keamanan tinggi, serta hanya dapat diakses oleh pihak yang berwenang.</p>
                        </div>
                    </div>
                    
                    <div class="col-md-6 js-reveal">
                        <div class="faq-minimal-item h-100 rounded-4 d-flex flex-column p-4 p-lg-5" style="background: #ffffff; border: 1.5px solid rgba(111, 66, 193, 0.22); box-shadow: 0 8px 32px rgba(111, 66, 193, 0.08), 0 0 0 0.5px rgba(111, 66, 193, 0.06); position: relative; overflow: hidden;">
                            <div class="d-flex align-items-center gap-4 mb-4">
                                <div class="faq-icon text-center flex-shrink-0" style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, rgba(111,66,193,0.12), rgba(167,139,250,0.18)); color: rgb(111, 66, 193); line-height: 52px; font-weight: 800; font-size: 1.6rem; font-family: 'Plus Jakarta Sans', sans-serif; box-shadow: 0 4px 16px rgba(111, 66, 193, 0.18); border: 1px solid rgba(111,66,193,0.15);">Q</div>
                                <h4 class="mb-0" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: #1e1e2d; font-size: 1.35rem; letter-spacing: -0.02em; line-height: 1.4;">Apakah laporan bisa diekspor?</h4>
                            </div>
                            <p class="mb-0 mt-auto" style="font-family: 'Plus Jakarta Sans', sans-serif; line-height: 1.7; font-size: 1.05rem; color: #5a6270;">Ya, laporan rekap dapat diekspor ke format PDF dan Word secara otomatis melalui fitur ekspor di sistem.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section id="hubungi" class="landing-section landing-section--contact py-5 js-section">
            <div class="container">

                <div class="row justify-content-center mb-5">
                    <div class="col-12 text-center">
                        <div class="mb-4 d-flex align-items-center justify-content-center gap-3 js-reveal">
                            <span style="height: 1px; width: 60px; background-color: rgb(111, 66, 193);"></span>
                            <h2 class="fw-bold text-uppercase mb-0" style="color: rgb(111, 66, 193); font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: 0.15em; font-size: 1.1rem;">HUBUNGI KAMI</h2>
                            <span style="height: 1px; width: 60px; background-color: rgb(111, 66, 193);"></span>
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center align-items-stretch g-5">
                    <div class="col-lg-6 mb-4 mb-lg-0 js-reveal">
                        <div class="contact-info-card p-4 p-md-5 rounded-4 h-100" style="background: linear-gradient(135deg, #ffffff 0%, #fbfbff 100%); border: 1px solid rgba(111, 66, 193, 0.12); box-shadow: 0 10px 40px rgba(0,0,0,0.04); position: relative; overflow: hidden;">
                            <h3 class="mb-4" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: #1e1e2d; font-size: clamp(1.6rem, 2.5vw, 2.2rem); line-height: 1.25; letter-spacing: -0.02em; position: relative; z-index: 2;">Butuh bantuan atau konsultasi penggunaan <span style="color: rgb(111, 66, 193);">MASSIPA?</span></h3>
                            <p class="mb-5" style="font-family: 'Plus Jakarta Sans', sans-serif; color: #5a6270; font-size: 1.1rem; line-height: 1.7; position: relative; z-index: 2;">
                                Silakan hubungi kami melalui kontak di bawah ini atau kunjungi langsung kantor kami. Tim kami siap membantu Anda terkait teknis pelaporan, monitoring, maupun kendala sistem operasional aplikasi.
                            </p>
                            
                            <div class="d-flex flex-column gap-0 text-start position-relative z-index-2">
                                <div class="d-flex align-items-center gap-4 group-hover-lift">
                                    <div class="contact-icon d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, rgba(111,66,193,0.12), rgba(167,139,250,0.18)); color: rgb(111, 66, 193); font-size: 1.5rem; box-shadow: 0 4px 16px rgba(111, 66, 193, 0.15); border: 1px solid rgba(111,66,193,0.12); transition: all 0.3s ease;">
                                        <i class="bi bi-envelope-check-fill"></i>
                                    </div>
                                    <div>
                                        <div style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.85rem; font-weight: 700; color: #a1a5b7; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Email Support</div>
                                        <a href="mailto:admin@pta-makassar.go.id" class="text-decoration-none" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.15rem; font-weight: 700; color: #1e1e2d; transition: color 0.2s ease;">admin@pta-makassar.go.id</a>
                                    </div>
                                </div>
                                <hr class="contact-item-divider">
                                <div class="d-flex align-items-center gap-4 group-hover-lift">
                                    <div class="contact-icon d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, rgba(111,66,193,0.12), rgba(167,139,250,0.18)); color: rgb(111, 66, 193); font-size: 1.5rem; box-shadow: 0 4px 16px rgba(111, 66, 193, 0.15); border: 1px solid rgba(111,66,193,0.12); transition: all 0.3s ease;">
                                        <i class="bi bi-telephone-inbound-fill"></i>
                                    </div>
                                    <div>
                                        <div style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.85rem; font-weight: 700; color: #a1a5b7; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Pusat Panggilan</div>
                                        <a href="tel:0411452653" class="text-decoration-none" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.15rem; font-weight: 700; color: #1e1e2d; transition: color 0.2s ease;">(0411) 452653</a>
                                    </div>
                                </div>
                                <hr class="contact-item-divider">
                                <div class="d-flex align-items-center gap-4 group-hover-lift">
                                    <div class="contact-icon d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, rgba(111,66,193,0.12), rgba(167,139,250,0.18)); color: rgb(111, 66, 193); font-size: 1.5rem; box-shadow: 0 4px 16px rgba(111, 66, 193, 0.15); border: 1px solid rgba(111,66,193,0.12); transition: all 0.3s ease;">
                                        <i class="bi bi-geo-alt-fill"></i>
                                    </div>
                                    <div>
                                        <div style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.85rem; font-weight: 700; color: #a1a5b7; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;">Alamat Kantor</div>
                                        <div style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 1.1rem; font-weight: 700; color: #1e1e2d; line-height: 1.4;">Jln. A.P. Pettarani No.66<br>Makassar, Sulawesi Selatan</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-4 mb-lg-0 js-reveal">
                        <div class="contact-info-card p-4 p-md-5 rounded-4 h-100 d-flex flex-column" style="background: linear-gradient(135deg, #ffffff 0%, #fbfbff 100%); position: relative; overflow: hidden;">
                            <h3 class="mb-4 flex-shrink-0" style="font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 800; color: #1e1e2d; font-size: clamp(1.4rem, 2vw, 1.8rem); line-height: 1.25; letter-spacing: -0.02em; position: relative; z-index: 2;">Lokasi <span style="color: rgb(111, 66, 193);">Kami</span></h3>
                            
                            <div class="rounded-4 overflow-hidden flex-grow-1 position-relative w-100" style="min-height: 400px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border: 1px solid rgba(111, 66, 193, 0.08);">
                                <iframe title="mapsPTA" src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d557.5456817946458!2d119.43765305337593!3d-5.14646199127723!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x2be642757e8e67c7!2sPengadilan%20Tinggi%20Agama%20Makassar%20(PTA%20Makassar)!5e0!3m2!1sen!2sid!4v1635146763585!5m2!1sen!2sid" style="border:0; position: absolute; top: 0; left: 0; width: 100%; height: 100%;" allowfullscreen="" loading="lazy"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <footer class="landing-footer" style="background:#232336; color:#e0e6ed; padding: 32px 0 12px 0; border-top-left-radius: 32px; border-top-right-radius: 32px;">
        <div class="container text-center">
            <div class="landing-footer__details">
                <img src="<?= base_url('assets/img/logo_landscape.webp') ?>" alt="Logo" loading="lazy" decoding="async" style="height:38px; width:auto; margin-bottom:10px;">
                <div style="font-size:0.97rem; color:#c4b5fd; margin-bottom:8px; display:flex; flex-wrap:wrap; justify-content:center; align-items:center; gap: 0.5rem 1.5rem;">
                    <span style="display:inline-flex; align-items:center; gap:0.5rem;">
                        <span style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:50%; background:rgba(139,92,246,0.18); color:#a78bfa; font-size:0.85rem;">
                            <i class="bi bi-geo-alt-fill"></i>
                        </span>
                        <span style="color:#ddd6fe;">Jln. A.P. Pettarani No.66 Makassar</span>
                    </span>
                    <span style="display:inline-flex; align-items:center; gap:0.5rem;">
                        <span style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:50%; background:rgba(139,92,246,0.18); color:#a78bfa; font-size:0.85rem;">
                            <i class="bi bi-telephone-fill"></i>
                        </span>
                        <a href="tel:(0411)452653" class="footer-link" style="color:#ddd6fe; text-decoration:none;">(0411) 452653</a>
                    </span>
                    <span style="display:inline-flex; align-items:center; gap:0.5rem;">
                        <span style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:50%; background:rgba(139,92,246,0.18); color:#a78bfa; font-size:0.85rem;">
                            <i class="bi bi-envelope-fill"></i>
                        </span>
                        <a href="mailto:admin@pta-makassar.go.id" class="footer-link" style="color:#ddd6fe; text-decoration:none;">admin@pta-makassar.go.id</a>
                    </span>
                    <span style="display:inline-flex; align-items:center; gap:0.5rem;">
                        <span style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:50%; background:rgba(139,92,246,0.18); color:#a78bfa; font-size:0.85rem;">
                            <i class="bi bi-whatsapp"></i>
                        </span>
                        <a href="https://wa.me/628114450855" class="footer-link" style="color:#ddd6fe; text-decoration:none;" target="_blank">0811 445 0855</a>
                    </span>
                    <span style="display:inline-flex; align-items:center; gap:0.5rem;">
                        <span style="display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:50%; background:rgba(139,92,246,0.18); color:#a78bfa; font-size:0.85rem;">
                            <i class="bi bi-globe2"></i>
                        </span>
                        <a href="https://www.pta-makassar.go.id" class="footer-link" style="color:#ddd6fe; text-decoration:none;" target="_blank">www.pta-makassar.go.id</a>
                    </span>
                </div>
            </div>
            <div class="landing-footer__copyright">© 2025 Sistem Disiplin Hakim. All Rights Reserved.</div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <?php $halamanAwalJsPath = FCPATH . 'assets/js/guest/HalamanAwal.js'; ?>
    <script src="<?= base_url('assets/js/guest/HalamanAwal.js') . (is_file($halamanAwalJsPath) ? ('?v=' . filemtime($halamanAwalJsPath)) : '') ?>" defer></script>
</body>
</html>