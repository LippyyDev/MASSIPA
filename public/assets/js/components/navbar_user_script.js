document.addEventListener("DOMContentLoaded", function () {
  const btn = document.getElementById("toggleDarkMode");
  const html = document.documentElement;
  const toggleSwitch = btn.querySelector(".toggle-switch");
  let themeSwitchTimeout = null;

  function startThemeSwitchTransition() {
    if (!html.classList.contains("theme-switching")) {
      html.classList.add("theme-switching");
    }
    if (themeSwitchTimeout) {
      clearTimeout(themeSwitchTimeout);
    }
    themeSwitchTimeout = setTimeout(function () {
      html.classList.remove("theme-switching");
      themeSwitchTimeout = null;
    }, 400);
  }
  function setToggleState() {
    const isDark = html.classList.contains("dark-mode");
    toggleSwitch.classList.toggle("dark", isDark);
  }
  function applyInitialMode() {
    let mode = localStorage.getItem("theme-mode");
    if (
      mode === "dark" ||
      (!mode && window.matchMedia("(prefers-color-scheme: dark)").matches)
    ) {
      html.classList.add("dark-mode");
    } else {
      html.classList.remove("dark-mode");
    }
    setToggleState();
  }
  applyInitialMode();
  btn &&
    btn.addEventListener("click", function () {
      startThemeSwitchTransition();
      const isDark = html.classList.toggle("dark-mode");
      localStorage.setItem("theme-mode", isDark ? "dark" : "light");
      setToggleState();
    });
  const sidebarToggle = document.getElementById("sidebarToggle");
  const sidebar = document.getElementById("sidebar");
  let sidebarBackdrop = null;
  function removeSidebarBackdrop() {
    if (sidebarBackdrop) {
      sidebarBackdrop.remove();
      sidebarBackdrop = null;
    }
  }
  function showSidebarBackdrop() {
    removeSidebarBackdrop();
    sidebarBackdrop = document.createElement("div");
    sidebarBackdrop.id = "sidebar-backdrop";
    sidebarBackdrop.style.position = "fixed";
    sidebarBackdrop.style.top = "0";
    sidebarBackdrop.style.left = "0";
    sidebarBackdrop.style.width = "100vw";
    sidebarBackdrop.style.height = "100vh";
    sidebarBackdrop.style.background = "rgba(0,0,0,0.3)";
    sidebarBackdrop.style.zIndex = "1049";
    sidebarBackdrop.onclick = function () {
      sidebar.classList.remove("show");
      removeSidebarBackdrop();
    };
    document.body.appendChild(sidebarBackdrop);
  }
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener("click", function () {
      if (sidebar.classList.contains("show")) {
        sidebar.classList.remove("show");
        removeSidebarBackdrop();
      } else {
        sidebar.classList.add("show");
        showSidebarBackdrop();
      }
    });
  }
  if (sidebar) {
    sidebar.querySelectorAll("a").forEach(function (link) {
      link.addEventListener("click", function () {
        if (window.innerWidth <= 991) {
          sidebar.classList.remove("show");
          removeSidebarBackdrop();
        }
      });
    });
  }
  window.addEventListener("resize", function () {
    if (window.innerWidth > 991) {
      sidebar.classList.remove("show");
      removeSidebarBackdrop();
    }
  });
  const greetingText = document.getElementById("greetingText");
  if (greetingText && window.namaLengkap) {
    const now = new Date();
    const hour = now.getHours();
    let greet = "Selamat Pagi";
    if (hour >= 4 && hour < 11) greet = "Selamat Pagi";
    else if (hour >= 11 && hour < 15) greet = "Selamat Siang";
    else if (hour >= 15 && hour < 18) greet = "Selamat Sore";
    else greet = "Selamat Malam";
    greetingText.textContent = greet + ", " + window.namaLengkap;
  }
});
