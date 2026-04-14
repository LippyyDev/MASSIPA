const bulanLabels = window.BULAN_LABELS || [];
const satkerData = window.SATKER_TREND || [];
const pegawaiData = window.PEGAWAI_TREND || [];
const pendingData = window.PENDING_TREND || [];
const arsipData = window.ARSIP_TREND || [];

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

function makeSparkline(ctx, data, color) {
  return new Chart(ctx, {
    type: "line",
    data: {
      labels: bulanLabels,
      datasets: [
        {
          data: data,
          borderColor: color,
          backgroundColor: color.replace("1)", "0.1)"),
          borderWidth: 2.5,
          pointRadius: 0,
          pointHoverRadius: 0,
          fill: true,
          tension: 0.8,
          cubicInterpolationMode: "monotone",
          borderJoinStyle: "round",
          borderCapStyle: "round",
        },
      ],
    },
    options: {
      plugins: {
        legend: { display: false },
        tooltip: { enabled: false },
      },
      scales: {
        x: {
          display: false,
          grid: { display: false },
        },
        y: {
          display: false,
          grid: { display: false },
        },
      },
      elements: {
        line: {
          borderJoinStyle: "round",
          borderCapStyle: "round",
        },
        point: {
          hoverRadius: 0,
          radius: 0,
        },
      },
      responsive: true,
      maintainAspectRatio: false,
      animation: {
        duration: 0,
      },
      interaction: {
        intersect: false,
        mode: "index",
      },
      layout: {
        padding: {
          top: 5,
          bottom: 5,
        },
      },
    },
  });
}

document.addEventListener("DOMContentLoaded", function () {
  makeSparkline(
    document.getElementById("satkerChart").getContext("2d"),
    satkerData,
    "rgba(124, 58, 237, 1)",
  );
  makeSparkline(
    document.getElementById("pegawaiChart").getContext("2d"),
    pegawaiData,
    "rgba(167, 139, 250, 1)",
  );
  makeSparkline(
    document.getElementById("pendingChart").getContext("2d"),
    pendingData,
    "rgba(245, 158, 66, 1)",
  );
  makeSparkline(
    document.getElementById("arsipChart").getContext("2d"),
    arsipData,
    "rgba(34, 197, 94, 1)",
  );

  const ctxLaporan = document.getElementById("chartLaporanPerBulan");
  if (ctxLaporan) {
    const bulanLabelsLaporan = [
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
    const dataLaporanPerBulan = window.LAPORAN_PER_BULAN || [
      0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
    ];

    const chartLaporan = new Chart(ctxLaporan.getContext("2d"), {
      type: "bar",
      data: {
        labels: bulanLabelsLaporan,
        datasets: [
          {
            label: "Jumlah Laporan",
            data: dataLaporanPerBulan,
            backgroundColor: "rgba(124, 58, 237, 0.7)",
            borderColor: "rgba(124, 58, 237, 1)",
            borderWidth: 1.5,
            borderRadius: 6,
            borderSkipped: false,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          title: { display: false },
        },
        scales: {
          x: {
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
          },
        },
      },
    });

    window.addEventListener("resize", () => {
      chartLaporan.resize();
    });
  }

  initializeLaporanCarousel();
  initializeHukumanCarousel();

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

let currentLaporanIndex = 0;
let laporanItems = [];
let totalLaporan = 0;

function initializeLaporanCarousel() {
  laporanItems = document.querySelectorAll(".laporan-item");
  totalLaporan = laporanItems.length;

  if (totalLaporan > 1) {
    setInterval(autoRotateLaporan, 5000);
  }

  updateCarouselButtons();
}

function showLaporan(index) {
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

  laporanItems[index].classList.add("active");

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

function updateCarouselButtons() {
  const prevBtn = document.querySelector(".prev-btn");
  const nextBtn = document.querySelector(".next-btn");

  if (prevBtn) {
    prevBtn.disabled = totalLaporan <= 1;
  }
  if (nextBtn) {
    nextBtn.disabled = totalLaporan <= 1;
  }
}

let currentHukumanIndex = 0;
let hukumanItems = [];
let totalHukuman = 0;

function initializeHukumanCarousel() {
  hukumanItems = document.querySelectorAll(".hukuman-item");
  totalHukuman = hukumanItems.length;

  if (totalHukuman > 1) {
    setInterval(autoRotateHukuman, 6000);
  }

  updateHukumanCarouselButtons();
}

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

window.showLaporan = showLaporan;
window.nextLaporan = nextLaporan;
window.prevLaporan = prevLaporan;
window.goToLaporan = goToLaporan;
window.showHukuman = showHukuman;
window.nextHukuman = nextHukuman;
window.prevHukuman = prevHukuman;
window.goToHukuman = goToHukuman;
