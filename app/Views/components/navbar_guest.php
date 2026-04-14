<!-- Anti-flash dark mode: apply BEFORE render -->
<script>
  (function(){
    try {
      if (localStorage.getItem('guestDark') === '1') {
        document.documentElement.classList.add('guest-dark');
      }
    } catch(e) {}
  })();
</script>
<nav class="navbar navbar-expand-lg navbar-floating landing-navbar">
    <div class="container-fluid landing-navbar__inner px-xl-5">
        <div class="d-flex align-items-center" style="gap:0;">
            <a class="navbar-brand p-0 me-4" href="#beranda" style="margin-left:0;">
                <img src="<?= base_url('assets/img/logo_landscape.webp') ?>" alt="Logo" style="height:36px; width:auto;">
            </a>
        </div>
        <button class="navbar-toggler border-0 shadow-none px-1 py-1 d-lg-none navbar-burger-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMobileMenu"
            aria-controls="offcanvasMobileMenu" aria-label="Toggle navigation">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
            </svg>
        </button>

        <!-- Desktop Navigation -->
        <div class="collapse navbar-collapse d-none d-lg-flex w-100" id="navbarNav">
            <ul class="navbar-nav mx-auto align-items-center" style="gap: 1.5rem;">
                <li class="nav-item"><a class="nav-link fw-medium nav-link-ungu text-dark" href="#beranda" style="font-size: 0.95rem;">Beranda</a></li>
                <li class="nav-item"><a class="nav-link fw-medium nav-link-ungu text-dark" href="#tentang" style="font-size: 0.95rem;">Tentang</a></li>
                <li class="nav-item"><a class="nav-link fw-medium nav-link-ungu text-dark" href="#keunggulan" style="font-size: 0.95rem;">Keunggulan</a></li>
                <li class="nav-item"><a class="nav-link fw-medium nav-link-ungu text-dark" href="#faq" style="font-size: 0.95rem;">FAQ</a></li>
                <li class="nav-item"><a class="nav-link fw-medium nav-link-ungu text-dark" href="#hubungi" style="font-size: 0.95rem;">Hubungi Kami</a></li>
            </ul>
            <div class="d-flex align-items-center mt-4 mt-lg-0 pb-3 pb-lg-0" style="gap: 1.25rem;">
                <div class="d-none d-lg-block" style="width: 1px; height: 24px; background-color: #d1d5db; margin-right: 0.25rem;"></div>
                <!-- Dark Mode Toggle (Desktop) -->
                <button id="guestDarkToggle" type="button" title="Toggle Dark Mode"
                  style="background: none; border: 1.5px solid rgba(111,66,193,0.25); border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: rgb(111,66,193); font-size: 1rem; transition: all 0.2s ease; flex-shrink:0;">
                  <i class="bi bi-moon-fill" id="darkModeIconDesktop"></i>
                </button>
                <a class="fw-semibold text-dark text-decoration-none btn-hover-scale" href="<?= base_url('login') ?>" style="font-size: 0.9rem; transition: transform 0.2s, color 0.2s;">Masuk</a>
                <a class="btn rounded-pill bg-white text-dark d-flex align-items-center justify-content-center btn-hover-shadow" href="https://drive.google.com/drive/folders/1aHAf9B97UprGSdkAVtuuGagN5jr2urFk?usp=sharing" target="_blank" style="padding: 0.45rem 1.25rem; font-weight: 500; font-size: 0.9rem; border: 1px solid #d1d5db; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s;">Download</a>
            </div>
        </div>

        <!-- Mobile Offcanvas Navigation -->
        <div class="offcanvas offcanvas-end d-lg-none" tabindex="-1" id="offcanvasMobileMenu" aria-labelledby="offcanvasMobileMenuLabel" style="width: 200px !important; --bs-offcanvas-width: 200px !important; max-width: 60vw;">
            <style>
                #offcanvasMobileMenu .nav-link-ungu::after {
                    display: none !important;
                }
                #offcanvasMobileMenu .nav-link-ungu {
                    padding: 0.6rem 0 !important;
                    display: inline-block;
                }
                #offcanvasMobileMenu .nav-item {
                    width: fit-content;
                }
            </style>
            <div class="offcanvas-header pt-4 px-4 pb-0">
                <img src="<?= base_url('assets/img/logo_landscape.webp') ?>" alt="Logo" style="height:28px; width:auto;" id="offcanvasMobileMenuLabel">
            </div>
            <div class="offcanvas-body px-4 py-4 d-flex flex-column">
                <ul class="navbar-nav flex-column mb-auto" style="gap: 0.25rem;">
                    <li class="nav-item"><a class="nav-link fw-medium nav-link-ungu text-dark" href="#beranda" style="font-size: 1rem;">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium nav-link-ungu text-dark" href="#tentang" style="font-size: 1rem;">Tentang</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium nav-link-ungu text-dark" href="#keunggulan" style="font-size: 1rem;">Keunggulan</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium nav-link-ungu text-dark" href="#faq" style="font-size: 1rem;">FAQ</a></li>
                    <li class="nav-item"><a class="nav-link fw-medium nav-link-ungu text-dark" href="#hubungi" style="font-size: 1rem;">Hubungi Kami</a></li>
                </ul>
                <div class="d-flex flex-column mt-5" style="gap: 1rem;">
                    <!-- Dark Mode Toggle (Mobile) -->
                    <button id="guestDarkToggleMobile" type="button"
                      style="background: none; border: 1.5px solid rgba(111,66,193,0.3); border-radius: 20px; padding: 0.45rem 1rem; display: flex; align-items: center; gap: 0.6rem; cursor: pointer; color: rgb(111,66,193); font-size: 0.95rem; font-weight: 600; font-family: 'Plus Jakarta Sans', sans-serif; transition: all 0.2s;">
                      <i class="bi bi-moon-fill" id="darkModeIconMobile"></i>
                      <span id="darkModeLabel">Dark Mode</span>
                    </button>
                    <a class="btn rounded-pill bg-white text-dark d-flex align-items-center justify-content-center w-100 btn-hover-shadow" href="<?= base_url('login') ?>" style="padding: 0.5rem 1rem; font-weight: 500; font-size: 0.95rem; border: 1px solid #d1d5db; transition: all 0.2s;">Masuk</a>
                    <a class="btn rounded-pill text-white d-flex align-items-center justify-content-center w-100 btn-hover-shine" href="https://drive.google.com/drive/folders/1aHAf9B97UprGSdkAVtuuGagN5jr2urFk?usp=sharing" target="_blank" style="padding: 0.5rem 1rem; font-weight: 500; font-size: 0.95rem; background-color: #8b5cf6; border: none; box-shadow: 0 4px 12px rgba(139, 92, 246, 0.25); transition: all 0.2s;">Download Apps</a>
                </div>
            </div>
        </div>
    </div>
</nav>
<script>
(function () {
  /* ---------- Dark Mode Toggle ---------- */
  function applyDark(isDark) {
    var html = document.documentElement;
    if (isDark) {
      html.classList.add('guest-dark');
    } else {
      html.classList.remove('guest-dark');
    }
    // Update icon & label
    var iconD = document.getElementById('darkModeIconDesktop');
    var iconM = document.getElementById('darkModeIconMobile');
    var label = document.getElementById('darkModeLabel');
    if (iconD) { iconD.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill'; }
    if (iconM) { iconM.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill'; }
    if (label) { label.textContent = isDark ? 'Light Mode' : 'Dark Mode'; }
    try { localStorage.setItem('guestDark', isDark ? '1' : '0'); } catch(e) {}
  }

  function initDarkToggle() {
    var isDark = false;
    try { isDark = localStorage.getItem('guestDark') === '1'; } catch(e) {}
    applyDark(isDark);

    ['guestDarkToggle', 'guestDarkToggleMobile'].forEach(function(id) {
      var btn = document.getElementById(id);
      if (btn) {
        btn.addEventListener('click', function() {
          var nowDark = !document.documentElement.classList.contains('guest-dark');
          applyDark(nowDark);
        });
      }
    });
  }

  document.addEventListener('DOMContentLoaded', initDarkToggle);

  /* ---------- Scroll Spy ---------- */
  function getNavOffset() {
    var nav = document.querySelector(".navbar-floating");
    return (nav && nav.offsetHeight ? nav.offsetHeight : 0) + 70;
  }

  function setActive(hash) {
    document.querySelectorAll(".nav-link-ungu").forEach(function (link) {
      link.classList.remove("active", "nav-active");
      if (link.getAttribute("href") === hash) {
          link.classList.add("active", "nav-active");
      }
    });
  }

  function initScrollSpy() {
    var links = Array.prototype.slice
      .call(document.querySelectorAll('.nav-link-ungu[href^="#"]'))
      .filter(function (a) {
        var h = a.getAttribute("href");
        return h && h.length > 1;
      });

    var items = links
      .map(function (a) {
        var hash = a.getAttribute("href");
        var sec = document.querySelector(hash);
        return sec ? { hash: hash, sec: sec } : null;
      })
      .filter(Boolean);

    if (!items.length) return;

    function getCurrentHash() {
      var navOffset = getNavOffset();
      var probeY = window.scrollY + navOffset + Math.round(window.innerHeight * 0.25);

      var current = items[0].hash;
      for (var i = 0; i < items.length; i++) {
        var top = items[i].sec.getBoundingClientRect().top + window.scrollY;
        if (top <= probeY) current = items[i].hash;
      }

      var scrollBottom = window.scrollY + window.innerHeight;
      var docHeight =
        (document.documentElement && document.documentElement.scrollHeight) ||
        document.body.scrollHeight;
      if (scrollBottom >= docHeight - 2) current = items[items.length - 1].hash;

      return current;
    }

    var ticking = false;
    function onScroll() {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(function () {
        setActive(getCurrentHash());
        ticking = false;
      });
    }

    var initialHash = window.location.hash;
    if (initialHash && document.querySelector(initialHash)) setActive(initialHash);
    else setActive(getCurrentHash());

    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("resize", onScroll);
    window.addEventListener("hashchange", function () {
      var h = window.location.hash || getCurrentHash();
      setActive(h);
    });

    // Smooth scroll and offcanvas dismiss handler
    links.forEach(function(link) {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        var hash = this.getAttribute("href");
        var targetSection = document.querySelector(hash);
        
        if (targetSection) {
          var targetTop = targetSection.getBoundingClientRect().top + window.scrollY - getNavOffset() + 20;
          window.scrollTo({
            top: targetTop,
            behavior: 'smooth'
          });
          
          if(history.pushState) {
              history.pushState(null, null, hash);
          } else {
              window.location.hash = hash;
          }
          setActive(hash);

          // Close offcanvas if mobile menu is open
          var offcanvasEl = document.getElementById('offcanvasMobileMenu');
          if (offcanvasEl && offcanvasEl.classList.contains('show')) {
            var instance = bootstrap.Offcanvas.getInstance(offcanvasEl) || new bootstrap.Offcanvas(offcanvasEl);
            instance.hide();
          }
        }
      });
    });
  }

  document.addEventListener("DOMContentLoaded", initScrollSpy);
})();
</script>