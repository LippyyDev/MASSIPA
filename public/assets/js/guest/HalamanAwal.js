const heroImages = [
  "assets/img/Satker/Landscape/PA_Bantaeng.webp",
  "assets/img/Satker/Landscape/PA_Barru.webp",
  "assets/img/Satker/Landscape/PA_Belopa.webp",
  "assets/img/Satker/Landscape/PA_Bulukumba.webp",
  "assets/img/Satker/Landscape/PA_Enrekang.webp",
  "assets/img/Satker/Landscape/PA_Jeneponto.webp",
  "assets/img/Satker/Landscape/PA_Makale.webp",
  "assets/img/Satker/Landscape/PA_Makassar.webp",
  "assets/img/Satker/Landscape/PA_Malili.webp",
  "assets/img/Satker/Landscape/PA_Maros.webp",
  "assets/img/Satker/Landscape/PA_Masamba.webp",
  "assets/img/Satker/Landscape/PA_Palopo.webp",
  "assets/img/Satker/Landscape/PA_Pangkajene.webp",
  "assets/img/Satker/Landscape/PA_Parepare.webp",
  "assets/img/Satker/Landscape/PA_Pinrang.webp",
  "assets/img/Satker/Landscape/PA_Selayar.webp",
  "assets/img/Satker/Landscape/PA_Sengkang.webp",
  "assets/img/Satker/Landscape/PA_SidenrengRappang.webp",
  "assets/img/Satker/Landscape/PA_Sinjai.webp",
  "assets/img/Satker/Landscape/PA_Sungguminasa.webp",
  "assets/img/Satker/Landscape/PA_Takalar.webp",
  "assets/img/Satker/Landscape/PA_Watampone.webp",
  "assets/img/Satker/Landscape/PA_Watansoppeng.webp",
  "assets/img/Satker/Landscape/PTA_Makassar.webp",
];

heroImages.forEach(function (src) {
  const img = new Image();
  img.src = window.location.origin + "/" + src;
});

function prefersReducedMotion() {
  return (
    typeof window !== "undefined" &&
    window.matchMedia &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches
  );
}

function isMobileDevice() {
  return (
    typeof window !== "undefined" &&
    window.matchMedia &&
    (window.matchMedia("(max-width: 768px)").matches ||
      /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
        navigator.userAgent,
      ))
  );
}

function initHeroTypingAnimation() {
  const title1 = document.getElementById("typing-title-1");
  const title2 = document.getElementById("typing-title-2");
  const heroButtons = document.querySelectorAll(".hero-buttons .btn");

  if (title1 && title2) {
    const text1 =
      '<span class="fw-normal" style="color: rgba(255, 255, 255, 0.9);">Manajemen</span> <span class="fw-bold" style="color: #ffffff;">Sarana</span><br><span class="fw-normal d-block" style="color: rgba(255, 255, 255, 0.9);">Disiplin <span class="fw-bold" style="color: #ffffff;">Hakim & Pegawai</span></span>';
    const text2 =
      '<span class="fw-normal" style="color: rgba(255, 255, 255, 0.9);">Satuan Kerja Wilayah</span> <span class="fw-bold" style="color: #ffffff;">Pengadilan Tinggi Agama Makassar</span>';

    // Hide buttons initially using CSS
    if (heroButtons.length > 0) {
      heroButtons.forEach((btn) => {
        btn.style.opacity = "0";
        btn.style.transform = "translateY(30px)";
        btn.style.transition = "opacity 0.7s ease-out, transform 0.7s ease-out";
      });
    }

    function typeHTML(element, htmlString, speed, callback) {
      element.innerHTML = "";
      let i = 0;
      let isTag = false;
      let text = "";

      function typeChar() {
        if (i < htmlString.length) {
          const char = htmlString.charAt(i);
          if (char === "<") isTag = true;
          if (char === ">") isTag = false;

          text += char;
          element.innerHTML =
            text +
            (i < htmlString.length - 1
              ? '<span class="typing-cursor">|</span>'
              : "");

          i++;
          if (isTag) {
            typeChar(); // Skip delay for HTML tags
          } else {
            setTimeout(typeChar, speed);
          }
        } else {
          element.innerHTML = text; // Remove cursor when done
          if (callback) callback();
        }
      }
      typeChar();
    }

    setTimeout(() => {
      typeHTML(title1, text1, 40, () => {
        setTimeout(() => {
          typeHTML(title2, text2, 30, () => {
            if (heroButtons.length > 0) {
              heroButtons.forEach((btn, index) => {
                setTimeout(() => {
                  btn.style.opacity = "1";
                  btn.style.transform = "translateY(0)";
                }, index * 100);
              });
            }
          });
        }, 300); // Small pause before second title
      });
    }, 500); // Initial delay
  }
}

function initGsapLandingAnimations() {
  // Disable GSAP animations on mobile devices
  if (isMobileDevice()) {
    const nav = document.querySelector(".navbar-floating");
    if (nav) {
      function updateNavbarOnScroll() {
        if (window.scrollY > 40) {
          nav.classList.add("is-scrolled");
        } else {
          nav.classList.remove("is-scrolled");
        }
      }
      window.addEventListener("scroll", updateNavbarOnScroll, {
        passive: true,
      });
      updateNavbarOnScroll();
    }
    return;
  }

  if (typeof gsap !== "undefined" && typeof ScrollTrigger !== "undefined") {
    gsap.registerPlugin(ScrollTrigger);

    const prefersReducedMotion =
      window.matchMedia &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    if (prefersReducedMotion) {
      const nav = document.querySelector(".navbar-floating");
      if (nav) {
        function updateNavbarOnScroll() {
          if (window.scrollY > 40) {
            nav.classList.add("is-scrolled");
          } else {
            nav.classList.remove("is-scrolled");
          }
        }
        window.addEventListener("scroll", updateNavbarOnScroll, {
          passive: true,
        });
        updateNavbarOnScroll();
      }
      return;
    }

    const heroImage = document.querySelector(".hero-image");
    const heroGlassButton = document.querySelector(".hero-glass-button"); // Keep this if it's used elsewhere in GSAP animations

    // Fallback if elements not found (heroTitles is not defined here, assuming it was a typo for heroContent or similar)
    // The typing animation is now handled by initHeroTypingAnimation()
    // If heroButtons need GSAP animation *outside* the typing, they would be handled here.
    // Since the instruction implies vanilla JS for buttons on mobile, and GSAP for desktop,
    // and the typing animation is now separate, we remove the GSAP button animations from here.

    if (heroImage) {
      gsap.from(heroImage, {
        scale: 0.9,
        opacity: 0,
        duration: 1,
        delay: 0.3,
        ease: "power2.out",
      });
    }

    const tentangSection = document.getElementById("tentang");
    if (tentangSection) {
      const tentangElements = tentangSection.querySelectorAll(".js-reveal");
      const tentangImage = tentangSection.querySelector(".about-image");

      gsap.from(tentangElements, {
        y: 60,
        opacity: 0,
        duration: 0.8,
        stagger: 0.2,
        ease: "power3.out",
        scrollTrigger: {
          trigger: tentangSection,
          start: "top 80%",
          end: "bottom 20%",
          toggleActions: "play none none none",
        },
      });

      if (tentangImage) {
        gsap.set(tentangImage, { opacity: 0, scale: 0.95 });

        gsap.to(tentangImage, {
          scale: 1,
          opacity: 1,
          duration: 1,
          ease: "power2.out",
          scrollTrigger: {
            trigger: tentangImage,
            start: "top 85%",
            toggleActions: "play none none none",
          },
        });
      }
    }

    const keunggulanCards = document.querySelectorAll(".bento-card");
    if (keunggulanCards.length > 0) {
      keunggulanCards.forEach((card, index) => {
        gsap.from(card, {
          y: 40,
          opacity: 0,
          duration: 0.8,
          ease: "power3.out",
          scrollTrigger: {
            trigger: card,
            start: "top 85%",
            toggleActions: "play none none none",
          },
          delay: (index % 3) * 0.1,
        });

        const cardBody = card.children;
        if (cardBody.length > 0) {
          gsap.from(cardBody, {
            y: 20,
            opacity: 0,
            duration: 0.6,
            stagger: 0.1,
            delay: 0.2 + (index % 3) * 0.1,
            ease: "power2.out",
            scrollTrigger: {
              trigger: card,
              start: "top 85%",
              toggleActions: "play none none none",
            },
          });
        }
      });
    }

    const keunggulanHeading = document.querySelector(
      "#keunggulan .section-heading",
    );
    if (keunggulanHeading) {
      gsap.from(keunggulanHeading, {
        y: 40,
        opacity: 0,
        duration: 0.8,
        ease: "power3.out",
        scrollTrigger: {
          trigger: keunggulanHeading,
          start: "top 90%",
          toggleActions: "play none none none",
        },
      });
    }

    const statCards = document.querySelectorAll(".stat-glass-card");
    if (statCards.length > 0) {
      statCards.forEach((card, index) => {
        gsap.from(card, {
          y: 50,
          opacity: 0,
          scale: 0.9,
          duration: 0.7,
          ease: "back.out(1.4)",
          scrollTrigger: {
            trigger: card,
            start: "top 85%",
            toggleActions: "play none none none",
          },
          delay: index * 0.1,
        });

        const statIcon = card.querySelector(".stat-icon");
        if (statIcon) {
          gsap.from(statIcon, {
            scale: 0,
            rotation: -180,
            duration: 0.6,
            ease: "back.out(2)",
            scrollTrigger: {
              trigger: card,
              start: "top 85%",
              toggleActions: "play none none none",
            },
            delay: index * 0.1 + 0.2,
          });
        }
      });
    }

    const statHeading = document.querySelector("#statistik .section-heading");
    const statSubtitle = document.querySelector("#statistik .section-subtitle");
    if (statHeading) {
      gsap.from(statHeading, {
        y: 40,
        opacity: 0,
        duration: 0.8,
        ease: "power3.out",
        scrollTrigger: {
          trigger: statHeading,
          start: "top 90%",
          toggleActions: "play none none none",
        },
      });
    }
    if (statSubtitle) {
      gsap.from(statSubtitle, {
        y: 30,
        opacity: 0,
        duration: 0.7,
        delay: 0.2,
        ease: "power3.out",
        scrollTrigger: {
          trigger: statSubtitle,
          start: "top 90%",
          toggleActions: "play none none none",
        },
      });
    }

    const faqSection = document.getElementById("faq");
    if (faqSection) {
      const faqHeading = faqSection.querySelector(".section-heading");
      const faqIllustration = faqSection.querySelector(".faq-illustration");
      const faqItems = faqSection.querySelectorAll(".faq-minimal-item");

      if (faqHeading) {
        gsap.from(faqHeading, {
          y: 40,
          opacity: 0,
          duration: 0.8,
          ease: "power3.out",
          scrollTrigger: {
            trigger: faqHeading,
            start: "top 90%",
            toggleActions: "play none none none",
          },
        });
      }

      if (faqIllustration) {
        gsap.from(faqIllustration, {
          scale: 0.8,
          opacity: 0,
          rotation: -10,
          duration: 0.9,
          ease: "back.out(1.7)",
          scrollTrigger: {
            trigger: faqIllustration,
            start: "top 85%",
            toggleActions: "play none none none",
          },
        });
      }

      if (faqItems.length > 0) {
        faqItems.forEach((item, index) => {
          gsap.from(item, {
            x: index % 2 === 0 ? -30 : 30,
            opacity: 0,
            duration: 0.6,
            ease: "power2.out",
            scrollTrigger: {
              trigger: item,
              start: "top 85%",
              toggleActions: "play none none none",
            },
            delay: index * 0.1,
          });
        });
      }
    }

    const hubungiSection = document.getElementById("hubungi");
    if (hubungiSection) {
      const hubungiIllustration = hubungiSection.querySelector(
        ".contact-illustration",
      );
      const hubungiElements = hubungiSection.querySelectorAll(".js-reveal");
      const hubungiMapCard = hubungiSection.querySelector(".contact-map-card");

      if (hubungiIllustration) {
        gsap.from(hubungiIllustration, {
          scale: 0.9,
          opacity: 0,
          y: 40,
          duration: 1,
          ease: "power3.out",
          scrollTrigger: {
            trigger: hubungiIllustration,
            start: "top 90%",
            toggleActions: "play none none none",
          },
        });
      }

      if (hubungiElements.length > 0) {
        gsap.from(hubungiElements, {
          y: 30,
          opacity: 0,
          duration: 0.7,
          stagger: 0.15,
          ease: "power3.out",
          scrollTrigger: {
            trigger: hubungiSection,
            start: "top 80%",
            toggleActions: "play none none none",
          },
        });
      }

      if (hubungiMapCard) {
        gsap.from(hubungiMapCard, {
          scale: 0.95,
          opacity: 0,
          y: 40,
          duration: 0.9,
          ease: "power3.out",
          scrollTrigger: {
            trigger: hubungiMapCard,
            start: "top 85%",
            toggleActions: "play none none none",
          },
        });
      }
    }

    const nav = document.querySelector(".navbar-floating");
    if (nav) {
      function updateNavbarOnScroll() {
        if (window.scrollY > 40) {
          nav.classList.add("is-scrolled");
        } else {
          nav.classList.remove("is-scrolled");
        }
      }
      window.addEventListener("scroll", updateNavbarOnScroll, {
        passive: true,
      });
      updateNavbarOnScroll();
    }
  } else {
    const nav = document.querySelector(".navbar-floating");
    if (nav) {
      function updateNavbarOnScroll() {
        if (window.scrollY > 40) {
          nav.classList.add("is-scrolled");
        } else {
          nav.classList.remove("is-scrolled");
        }
      }
      window.addEventListener("scroll", updateNavbarOnScroll, {
        passive: true,
      });
      updateNavbarOnScroll();
    }
  }
}

document.addEventListener("DOMContentLoaded", function () {
  let animated = false;
  const statFormatter =
    typeof Intl !== "undefined" && Intl.NumberFormat
      ? new Intl.NumberFormat("id-ID")
      : null;

  function formatStatNumber(n) {
    if (statFormatter) return statFormatter.format(n);
    return String(n);
  }

  function animateCount(el, target, duration = 1200) {
    if (!Number.isFinite(target)) {
      el.textContent = "0";
      return;
    }
    let start = 0;
    let startTime = null;
    function step(ts) {
      if (!startTime) startTime = ts;
      let progress = Math.min((ts - startTime) / duration, 1);
      const current = Math.floor(progress * (target - start) + start);
      el.textContent = formatStatNumber(current);
      if (progress < 1) requestAnimationFrame(step);
      else el.textContent = formatStatNumber(target);
    }
    requestAnimationFrame(step);
  }
  function startAnimStat() {
    if (animated) return;
    document.querySelectorAll(".stat-angka").forEach(function (el) {
      const raw = el.getAttribute("data-count");
      const target = Number.parseInt(raw ?? "0", 10);
      animateCount(el, Number.isFinite(target) ? target : 0);
    });
    animated = true;
  }

  function initStatPointerGlow() {
    const statSection = document.getElementById("statistik");
    if (!statSection) return;
    if (isMobileDevice()) return;

    const supportsFinePointer =
      typeof window !== "undefined" &&
      window.matchMedia &&
      window.matchMedia("(hover: hover) and (pointer: fine)").matches;
    if (!supportsFinePointer) return;

    let rect = null;
    let raf = 0;

    function updateRect() {
      rect = statSection.getBoundingClientRect();
    }

    function setVars(clientX, clientY) {
      if (!rect) updateRect();
      if (!rect || rect.width <= 0 || rect.height <= 0) return;
      const x = Math.max(0, Math.min(clientX - rect.left, rect.width));
      const y = Math.max(0, Math.min(clientY - rect.top, rect.height));
      const px = Math.round((x / rect.width) * 100);
      const py = Math.round((y / rect.height) * 100);
      statSection.style.setProperty("--mx", px + "%");
      statSection.style.setProperty("--my", py + "%");
    }

    statSection.addEventListener("mouseenter", function () {
      statSection.classList.add("is-pointer-glow");
      updateRect();
    });

    statSection.addEventListener("mouseleave", function () {
      statSection.classList.remove("is-pointer-glow");
    });

    statSection.addEventListener(
      "mousemove",
      function (e) {
        if (raf) return;
        raf = window.requestAnimationFrame(function () {
          raf = 0;
          setVars(e.clientX, e.clientY);
        });
      },
      { passive: true },
    );

    window.addEventListener("resize", updateRect);
  }

  function initAboutRandomGlow() {
    const about = document.getElementById("tentang");
    if (!about) return;
    if (prefersReducedMotion()) return;
    if (isMobileDevice()) return;

    let layer = about.querySelector(".about-random-glow-layer");
    if (!layer) {
      layer = document.createElement("div");
      layer.className = "about-random-glow-layer";

      const colors = [
        "radial-gradient(circle at 30% 30%, rgba(139,92,246,0.85), rgba(139,92,246,0) 62%)",
        "radial-gradient(circle at 30% 30%, rgba(236,72,153,0.55), rgba(236,72,153,0) 64%)",
        "radial-gradient(circle at 30% 30%, rgba(167,139,250,0.55), rgba(167,139,250,0) 66%)",
      ];

      colors.forEach((bg) => {
        const blob = document.createElement("div");
        blob.className = "about-random-glow-blob";
        blob.style.backgroundImage = bg;
        layer.appendChild(blob);
      });

      about.insertBefore(layer, about.firstChild);
    }

    const blobs = Array.prototype.slice.call(
      layer.querySelectorAll(".about-random-glow-blob"),
    );
    if (!blobs.length) return;

    let rect = null;
    let rafId = 0;
    let lastTs = 0;
    let running = false;

    const state = blobs.map((_, idx) => ({
      x: -200,
      y: -200,

      vx: (idx === 0 ? 12 : 8) * (Math.random() > 0.5 ? 1 : -1),
      vy: (idx === 0 ? 10 : 7) * (Math.random() > 0.5 ? 1 : -1),
      s: idx === 0 ? 1.12 : 1.0,
      o: 0.55,
    }));

    function updateRect() {
      rect = about.getBoundingClientRect();
    }

    function rand(min, max) {
      return min + Math.random() * (max - min);
    }

    function clamp(v, min, max) {
      return Math.max(min, Math.min(v, max));
    }

    function step(ts) {
      if (!running) return;
      if (!rect) updateRect();
      if (!rect || rect.width <= 0 || rect.height <= 0) {
        rafId = window.requestAnimationFrame(step);
        return;
      }

      const dt = lastTs ? Math.min((ts - lastTs) / 1000, 0.05) : 0.016;
      lastTs = ts;

      const w = rect.width;
      const h = rect.height;

      const minX = -0.25 * w;
      const maxX = 0.85 * w;
      const minY = -0.25 * h;
      const maxY = 0.85 * h;

      if (!rect) updateRect();
      if (!rect || rect.width <= 0 || rect.height <= 0) return;

      state.forEach((st, idx) => {
        const ax = rand(-14, 14);
        const ay = rand(-12, 12);

        st.vx += ax * dt;
        st.vy += ay * dt;

        st.vx = clamp(st.vx, -22, 22);
        st.vy = clamp(st.vy, -18, 18);

        st.x += st.vx * (38 * dt);
        st.y += st.vy * (38 * dt);

        if (st.x < minX || st.x > maxX) st.vx *= -0.92;
        if (st.y < minY || st.y > maxY) st.vy *= -0.92;
        st.x = clamp(st.x, minX, maxX);
        st.y = clamp(st.y, minY, maxY);

        st.s += rand(-0.08, 0.08) * dt;
        st.o += rand(-0.12, 0.12) * dt;

        const baseS = idx === 0 ? 1.12 : 1.0;
        st.s = clamp(st.s, baseS * 0.92, baseS * 1.22);
        st.o = clamp(st.o, 0.35, 0.72);

        const blob = blobs[idx];
        blob.style.transform = `translate3d(${Math.round(st.x)}px, ${Math.round(
          st.y,
        )}px, 0) scale(${st.s.toFixed(2)})`;
        blob.style.opacity = st.o.toFixed(2);
      });

      rafId = window.requestAnimationFrame(step);
    }

    function start() {
      if (running) return;
      running = true;
      updateRect();
      lastTs = 0;
      rafId = window.requestAnimationFrame(step);
    }

    function stop() {
      if (!running) return;
      running = false;
      if (rafId) window.cancelAnimationFrame(rafId);
      rafId = 0;
    }

    if ("IntersectionObserver" in window) {
      const io = new IntersectionObserver(
        (entries) => {
          const e = entries && entries[0];
          if (!e) return;
          if (e.isIntersecting) start();
          else stop();
        },
        { threshold: 0.12 },
      );
      io.observe(about);
    } else {
      start();
    }

    window.addEventListener("resize", updateRect);
  }
  if ("IntersectionObserver" in window) {
    let observer = new IntersectionObserver(
      function (entries) {
        if (entries[0].isIntersecting) {
          startAnimStat();
          observer.disconnect();
        }
      },
      { threshold: 0.3 },
    );
    let statSection = document.getElementById("statistik");
    if (statSection) observer.observe(statSection);
  } else {
    startAnimStat();
  }

  function initGlobalCursorGlow() {
    if (isMobileDevice()) return;

    let rafGlobal = 0;
    window.addEventListener(
      "mousemove",
      function (e) {
        if (rafGlobal) return;
        rafGlobal = window.requestAnimationFrame(function () {
          rafGlobal = 0;
          document.body.style.setProperty("--mouse-x", e.clientX + "px");
          document.body.style.setProperty("--mouse-y", e.clientY + "px");
        });
      },
      { passive: true },
    );
  }

  initStatPointerGlow();
  initAboutRandomGlow();
  initHeroTypingAnimation();
  initGsapLandingAnimations();
  initGlobalCursorGlow();
});
