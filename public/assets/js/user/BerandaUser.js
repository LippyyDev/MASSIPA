$(document).ready(function () {
  const mainContent = document.querySelector(".main-content");
  function handleResize() {
    if (window.innerWidth <= 991) {
      mainContent.style.marginLeft = "0";
    } else {
      mainContent.style.marginLeft = "240px";
    }
  }
  window.addEventListener("resize", handleResize);
  handleResize();
});

function getStatusBadgeColor(status) {
  switch (status) {
    case "terkirim":
      return "warning";
    case "dilihat":
      return "info";
    case "diterima":
      return "success";
    case "ditolak":
      return "danger";
    default:
      return "secondary";
  }
}

function getStatusIndo(status) {
  switch (status) {
    case "terkirim":
      return "Terkirim";
    case "dilihat":
      return "Dilihat";
    case "diterima":
      return "Diterima";
    case "ditolak":
      return "Ditolak";
    default:
      return "Tidak Diketahui";
  }
}

function getBulanIndo(bulan) {
  const namaBulan = [
    "",
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember",
  ];
  return namaBulan[bulan];
}

const bulanLabels = [
  "Jan",
  "Feb",
  "Mar",
  "Apr",
  "Mei",
  "Jun",
  "Jul",
  "Agu",
  "Sep",
  "Okt",
  "Nov",
  "Des",
];
const pegawaiData = window.pegawai_trend || [];
const kedisiplinanData = window.kedisiplinan_trend || [];
const laporanData = window.laporan_trend || [];
const hukumanData = window.hukuman_trend || [];

function makeSparkline(ctx, data, color) {
  return new Chart(ctx, {
    type: "line",
    data: {
      labels: bulanLabels,
      datasets: [
        {
          data: data,
          borderColor: color,
          backgroundColor: "rgba(124, 58, 237, 0.08)",
          borderWidth: 2,
          pointRadius: 0,
          fill: true,
          tension: 0.4,
        },
      ],
    },
    options: {
      plugins: { legend: { display: false } },
      scales: { x: { display: false }, y: { display: false } },
      elements: { line: { borderJoinStyle: "round" } },
      responsive: true,
      maintainAspectRatio: false,
      animation: {
        duration: 0,
      },
    },
  });
}
document.addEventListener("DOMContentLoaded", function () {
  const validPegawaiData =
    pegawaiData.length > 0 ? pegawaiData : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
  const validKedisiplinanData =
    kedisiplinanData.length > 0
      ? kedisiplinanData
      : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
  const validLaporanData =
    laporanData.length > 0 ? laporanData : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
  const validHukumanData =
    hukumanData.length > 0 ? hukumanData : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

  makeSparkline(
    document.getElementById("pegawaiChart"),
    validPegawaiData,
    "#7c3aed",
  );
  makeSparkline(
    document.getElementById("kedisiplinanChart"),
    validKedisiplinanData,
    "#5b21b6",
  );
  makeSparkline(
    document.getElementById("laporanChart"),
    validLaporanData,
    "#22c55e",
  );
  makeSparkline(
    document.getElementById("hukumanChart"),
    validHukumanData,
    "#dc2626",
  );

  const grafikPegawaiKategori = window.grafikPegawaiKategori;
  const bulanLabels = [
    "Jan",
    "Feb",
    "Mar",
    "Apr",
    "Mei",
    "Jun",
    "Jul",
    "Agu",
    "Sep",
    "Okt",
    "Nov",
    "Des",
  ];

  const ctx = document.getElementById("grafikPegawaiKategori");
  if (ctx && grafikPegawaiKategori) {
    const ctx = document
      .getElementById("grafikPegawaiKategori")
      .getContext("2d");

    const kategoriList = ["t", "tam", "pa", "tap", "kti", "tk", "tms", "tmk"];
    const kategoriLabel = {
      t: "T: Terlambat",
      tam: "TAM: Tidak Absen Masuk",
      pa: "PA: Pulang Awal",
      tap: "TAP: Tidak Absen Pulang",
      kti: "KTI: Keluar Tidak Izin",
      tk: "TK: Tidak Masuk Tanpa Ket",
      tms: "TMS: Tidak Masuk Sakit",
      tmk: "TMK: Tidak Masuk Kerja",
    };

    const warnaUngu = [
      "rgba(124, 58, 237, 0.3)",
      "rgba(124, 58, 237, 0.4)",
      "rgba(124, 58, 237, 0.5)",
      "rgba(124, 58, 237, 0.6)",
      "rgba(124, 58, 237, 0.7)",
      "rgba(124, 58, 237, 0.8)",
      "rgba(124, 58, 237, 0.9)",
      "rgba(124, 58, 237, 1.0)",
    ];

    const datasets = kategoriList.map((k, i) => ({
      label: kategoriLabel[k],
      data: grafikPegawaiKategori[k] || [],
      backgroundColor: warnaUngu[i],
      borderColor: "rgba(124, 58, 237, 1)",
      borderWidth: 1,
      borderRadius: 4,
      borderSkipped: false,
    }));

    const chartPegawaiKategori = new Chart(ctx, {
      type: "bar",
      data: {
        labels: bulanLabels,
        datasets: datasets,
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: true,
            position: "top",
            labels: {
              usePointStyle: true,
              padding: 15,
              font: {
                size: 11,
              },
            },
          },
          title: { display: false },
        },
        scales: {
          x: {
            stacked: false,
            grid: {
              display: false,
            },
            ticks: {
              font: {
                size: 12,
              },
            },
          },
          y: {
            stacked: false,
            beginAtZero: true,
            stepSize: 1,
            grid: {
              color: "rgba(0, 0, 0, 0.05)",
              drawBorder: false,
            },
            ticks: {
              font: {
                size: 12,
              },
              callback: function (value, index, values) {
                if (Math.floor(value) === value) {
                  return value;
                }
                return "";
              },
            },
          },
        },
        animation: {
          duration: 0,
        },
        layout: {
          padding: {
            top: 10,
            bottom: 10,
            left: 20,
            right: 20,
          },
        },
        maintainAspectRatio: false,
      },
    });

    window.addEventListener("resize", () => {
      chartPegawaiKategori.resize();
    });
  }

  window.showHukuman = showHukuman;
  window.nextHukuman = nextHukuman;
  window.prevHukuman = prevHukuman;
  window.goToHukuman = goToHukuman;
  window.autoRotateHukuman = autoRotateHukuman;
  window.updateHukumanCarouselButtons = updateHukumanCarouselButtons;

  initSwipeGesture("hukumanCarousel", prevHukuman, nextHukuman);
  initSwipeGesture("laporanCarousel", prevLaporan, nextLaporan);
});

function initSwipeGesture(carouselId, prevFunc, nextFunc) {
  const carousel = document.getElementById(carouselId);
  if (!carousel) return;

  let touchStartX = 0;
  let touchCurrentX = 0;
  let isSwiping = false;

  carousel.addEventListener(
    "touchstart",
    (e) => {
      touchStartX = e.touches[0].clientX;
      touchCurrentX = touchStartX;
      isSwiping = true;

      const activeItem = carousel.querySelector(".active");
      if (activeItem) {
        activeItem.style.transition = "none";
        activeItem.style.zIndex = "5";
      }
    },
    { passive: true },
  );

  carousel.addEventListener(
    "touchmove",
    (e) => {
      if (!isSwiping) return;
      touchCurrentX = e.touches[0].clientX;
      const diff = touchCurrentX - touchStartX;

      const activeItem = carousel.querySelector(".active");
      if (activeItem) {
        const scale = Math.max(0.95, 1 - Math.abs(diff) / 2000);
        activeItem.style.transform = `translateX(${diff * 0.8}px) scale(${scale})`;
        activeItem.style.opacity = Math.max(0.5, 1 - Math.abs(diff) / 600);
      }
    },
    { passive: true },
  );

  carousel.addEventListener(
    "touchend",
    (e) => {
      if (!isSwiping) return;
      isSwiping = false;
      const diff = touchCurrentX - touchStartX;

      const activeItem = carousel.querySelector(".active");
      if (activeItem) {
        activeItem.style.transition =
          "opacity 0.4s ease, transform 0.4s cubic-bezier(0.25, 1, 0.5, 1)";
        activeItem.style.transform = "";
        activeItem.style.opacity = "";
        activeItem.style.zIndex = "";

        setTimeout(() => {
          if (activeItem) activeItem.style.transition = "";
        }, 400);
      }

      const swipeThreshold = 50;
      if (Math.abs(diff) > swipeThreshold) {
        if (diff < 0) {
          nextFunc();
        } else {
          prevFunc();
        }
      }
    },
    { passive: true },
  );
}

// Hukuman Carousel Functions
let currentHukumanIndex = 0;
let hukumanItems = [];
let totalHukuman = 0;

document.addEventListener("DOMContentLoaded", function () {
  hukumanItems = document.querySelectorAll(".hukuman-item");
  totalHukuman = hukumanItems.length;

  if (totalHukuman > 1) {
    setInterval(autoRotateHukuman, 6000);
  }

  updateHukumanCarouselButtons();
});

function showHukuman(index) {
  hukumanItems.forEach((item) => {
    item.classList.remove("active", "prev");
  });

  const hukumanCarousel = document.getElementById("hukumanCarousel");
  const hukumanCard = hukumanCarousel ? hukumanCarousel.closest(".card") : null;
  if (hukumanCard) {
    hukumanCard.querySelectorAll(".indicator").forEach((indicator) => {
      indicator.classList.remove("active");
    });
  }

  hukumanItems[index].classList.add("active");

  const indicators = hukumanCard
    ? hukumanCard.querySelectorAll(".indicator")
    : [];
  if (indicators[index]) {
    indicators[index].classList.add("active");
  }

  currentHukumanIndex = index;

  updateHukumanCarouselButtons();
}

function nextHukuman() {
  const nextIndex = (currentHukumanIndex + 1) % totalHukuman;
  showHukuman(nextIndex);
}

function prevHukuman() {
  const prevIndex = (currentHukumanIndex - 1 + totalHukuman) % totalHukuman;
  showHukuman(prevIndex);
}

function goToHukuman(index) {
  showHukuman(index);
}

function autoRotateHukuman() {
  if (totalHukuman > 1) {
    nextHukuman();
  }
}

function updateHukumanCarouselButtons() {
  const hukumanCarousel = document.getElementById("hukumanCarousel");
  const hukumanCard = hukumanCarousel ? hukumanCarousel.closest(".card") : null;
  if (hukumanCard) {
    const prevBtn = hukumanCard.querySelector(".prev-btn");
    const nextBtn = hukumanCard.querySelector(".next-btn");

    if (prevBtn) {
      prevBtn.disabled = totalHukuman <= 1;
    }
    if (nextBtn) {
      nextBtn.disabled = totalHukuman <= 1;
    }
  }
}

let currentLaporanIndex = 0;
let laporanItems = [];
let totalLaporan = 0;

document.addEventListener("DOMContentLoaded", function () {
  laporanItems = document.querySelectorAll(".laporan-item");
  totalLaporan = laporanItems.length;

  if (totalLaporan > 1) {
    setInterval(autoRotateLaporan, 5000);
  }

  updateCarouselButtons();
});

function showLaporan(index) {
  if (index < 0 || index >= totalLaporan) {
    console.error("Invalid index:", index, "totalLaporan:", totalLaporan);
    return;
  }

  laporanItems.forEach((item) => {
    item.classList.remove("active", "prev");
  });

  const laporanCarousel = document.getElementById("laporanCarousel");
  const laporanCard = laporanCarousel ? laporanCarousel.closest(".card") : null;
  if (laporanCard) {
    laporanCard.querySelectorAll(".indicator").forEach((indicator) => {
      indicator.classList.remove("active");
    });
  }

  if (laporanItems[index]) {
    laporanItems[index].classList.add("active");
  }

  const indicators = laporanCard
    ? laporanCard.querySelectorAll(".indicator")
    : [];
  if (indicators[index]) {
    indicators[index].classList.add("active");
  }

  currentLaporanIndex = index;

  updateCarouselButtons();
}

function nextLaporan() {
  const nextIndex = (currentLaporanIndex + 1) % totalLaporan;
  showLaporan(nextIndex);
}

function prevLaporan() {
  const prevIndex = (currentLaporanIndex - 1 + totalLaporan) % totalLaporan;
  showLaporan(prevIndex);
}

function goToLaporan(index) {
  showLaporan(index);
}

function autoRotateLaporan() {
  if (totalLaporan > 1) {
    nextLaporan();
  }
}

function updateCarouselButtons() {}

window.showLaporan = showLaporan;
window.nextLaporan = nextLaporan;
window.prevLaporan = prevLaporan;
window.goToLaporan = goToLaporan;
