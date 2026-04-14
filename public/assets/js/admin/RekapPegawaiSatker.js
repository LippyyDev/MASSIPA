(function () {
  try {
    var mode = localStorage.getItem("theme-mode");
    if (
      mode === "dark" ||
      (!mode && window.matchMedia("(prefers-color-scheme: dark)").matches)
    ) {
      document.documentElement.classList.add("dark-mode");
    }
  } catch (e) {}
})();

const DESKTOP_BREAKPOINT = 768;
const MOBILE_PAGE_BUTTONS = 3;
let rekapDataTable = null;
let mobileSatkerData = [];
let filteredMobileSatkerData = [];
let mobileSatkerPage = 1;
const mobileSatkerPageSize = 10;

const isDesktop = () => window.innerWidth >= DESKTOP_BREAKPOINT;

function initDesktopTable() {
  if (rekapDataTable || !$("#rekapTable").length) return;
  if ($.fn.DataTable && $.fn.DataTable.ext && $.fn.DataTable.ext.pager) {
    $.fn.DataTable.ext.pager.numbers_length = 3;
  }
  rekapDataTable = $("#rekapTable").DataTable({
    paging: true,
    searching: true,
    ordering: true,
    info: false,
    autoWidth: false,
    scrollX: true,
    pageLength: 10,
    lengthChange: false,
    pagingType: "simple_numbers",
    language: {
      emptyTable: "Tidak ada data yang tersedia",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
      infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
      search: "Cari:",
      paginate: {
        first: "",
        last: "",
        next: "&gt;",
        previous: "&lt;",
      },
    },
  });
}

function collectMobileSatkerData() {
  const $rows = $("#rekapTable tbody tr");
  if (!$rows.length) return;

  mobileSatkerData = [];
  $rows.each(function () {
    const $cells = $(this).find("td");
    if ($cells.length < 14 || $cells.first().attr("colspan")) {
      return;
    }

    mobileSatkerData.push({
      no: $cells.eq(0).text().trim(),
      satker: $cells.eq(1).text().trim(),
      alamat: $cells.eq(2).text().trim(),
      total: $cells.eq(3).text().trim(),
      t: $cells.eq(4).text().trim(),
      kti: $cells.eq(5).text().trim(),
      tam: $cells.eq(6).text().trim(),
      tk: $cells.eq(7).text().trim(),
      pa: $cells.eq(8).text().trim(),
      tms: $cells.eq(9).text().trim(),
      tap: $cells.eq(10).text().trim(),
      tmk: $cells.eq(11).text().trim(),
      pembinaan: $cells.eq(12).text().trim(),
      keterangan: $cells.eq(13).text().trim(),
    });
  });

  filteredMobileSatkerData = [...mobileSatkerData];
  mobileSatkerPage = 1;
  loadMobileSatkerData();
}

function loadMobileSatkerData() {
  const start = (mobileSatkerPage - 1) * mobileSatkerPageSize;
  const end = start + mobileSatkerPageSize;
  const pageData = filteredMobileSatkerData.slice(start, end);
  renderMobileSatkerCards(pageData, start);
  updateMobileSatkerPagination();
}

function renderMobileSatkerCards(data, startIndex) {
  const $container = $("#mobileRekapSatkerCards");
  if (!$container.length) return;

  if (!data.length) {
    $container.html(
      '<div class="text-center text-muted py-4">Tidak ada data</div>'
    );
    const $paginationWrapper = $("#mobileRekapSatkerPagination");
    if ($paginationWrapper.length) {
      $paginationWrapper.hide();
      $paginationWrapper.find(".mobile-rekap-page-item").remove();
    }
    $("#mobileRekapSatkerPrev").prop("disabled", true);
    $("#mobileRekapSatkerNext").prop("disabled", true);
    return;
  }

  const metrics = ["t", "kti", "tam", "tk", "pa", "tms", "tap", "tmk"];
  let html = "";

  data.forEach((item, idx) => {
    const no = startIndex + idx + 1;
    html += `
            <div class="border rounded mb-3 p-3 shadow-sm" style="background:#fff;">
                <div class="fw-bold mb-1 d-flex justify-content-between align-items-center">
                    <span>No. ${no} - ${item.satker}</span>
                    <span class="badge" style="background-color: #7c3aed; color: #fff;">Pegawai ${
                      item.total || "-"
                    }</span>
                </div>
                <div><b>Alamat:</b> ${item.alamat || "-"}</div>
                <div class="mt-2"><b>Akumulasi Pelanggaran:</b></div>
                <div class="row g-2 mt-1">
                    ${metrics
                      .map(
                        (label) => `
                        <div class="col-3 text-center">
                            <div class="small text-muted">${label}</div>
                            <div class="fw-bold">${item[label] || "-"}</div>
                        </div>
                    `
                      )
                      .join("")}
                </div>
                ${
                  item.pembinaan && item.pembinaan !== "-"
                    ? `
                    <div class="mt-2"><b>Bentuk Pembinaan:</b> ${item.pembinaan}</div>
                `
                    : ""
                }
                ${
                  item.keterangan && item.keterangan !== "-"
                    ? `
                    <div class="mt-1"><b>Keterangan:</b> ${item.keterangan}</div>
                `
                    : ""
                }
            </div>
        `;
  });

  $container.html(html);
}

function updateMobileSatkerPagination() {
  const $pagination = $("#mobileRekapSatkerPagination");
  if (!$pagination.length) return;
  const $paginationList = $pagination.find(".pagination");
  const $nextItem = $("#mobileRekapSatkerNext").closest(".page-item");

  const total = filteredMobileSatkerData.length;
  const totalPages = Math.ceil(total / mobileSatkerPageSize);

  if (totalPages <= 1) {
    $paginationList.find(".mobile-rekap-page-item").remove();
    $pagination.hide();
    return;
  }
  $("#mobileRekapSatkerPrev").prop("disabled", mobileSatkerPage === 1);
  $("#mobileRekapSatkerNext").prop("disabled", mobileSatkerPage >= totalPages);
  $paginationList.find(".mobile-rekap-page-item").remove();

  let startPage = Math.max(1, mobileSatkerPage - 1);
  let endPage = Math.min(totalPages, startPage + (MOBILE_PAGE_BUTTONS - 1));

  if (endPage - startPage < MOBILE_PAGE_BUTTONS - 1) {
    startPage = Math.max(1, endPage - (MOBILE_PAGE_BUTTONS - 1));
  }

  let pageNumbersHtml = "";
  for (let i = startPage; i <= endPage; i++) {
    const activeClass = i === mobileSatkerPage ? "active" : "";
    pageNumbersHtml += `
      <li class="page-item mobile-rekap-page-item ${activeClass}">
        <button class="page-link mobile-rekap-page" type="button" data-page="${i}">${i}</button>
      </li>
    `;
  }
  if ($nextItem.length) {
    $nextItem.before(pageNumbersHtml);
  } else {
    $paginationList.append(pageNumbersHtml);
  }
  $(".mobile-rekap-page")
    .off("click")
    .on("click", function () {
      const targetPage = parseInt($(this).data("page"), 10);
      if (!isNaN(targetPage) && targetPage !== mobileSatkerPage) {
        mobileSatkerPage = targetPage;
        loadMobileSatkerData();
      }
    });

  $pagination.show();
}

$(document).ready(function () {
  function fixAriaHidden() {
    $(".modal").each(function () {
      var modal = $(this);
      modal.removeAttr("aria-hidden");
      modal.find('[tabindex="-1"]').removeAttr("tabindex");
      modal
        .find("input:not(:focus), select:not(:focus), textarea:not(:focus)")
        .blur();
    });
    if (!$(document.activeElement).is("input, select, textarea")) {
      $(document.activeElement).blur();
    }
  }

  fixAriaHidden();
  setInterval(fixAriaHidden, 100);

  var observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (
        mutation.type === "attributes" &&
        mutation.attributeName === "aria-hidden"
      ) {
        $(mutation.target).removeAttr("aria-hidden");
      }
    });
  });

  $(".modal").each(function () {
    observer.observe(this, {
      attributes: true,
      attributeFilter: ["aria-hidden"],
    });
  });

  $(document).on("hidden.bs.modal", function (e) {
    var modal = $(e.target);
    modal.removeAttr("aria-hidden");
    modal.find('[tabindex="-1"]').removeAttr("tabindex");
    modal
      .find("input:not(:focus), select:not(:focus), textarea:not(:focus)")
      .blur();
    if (!$(document.activeElement).is("input, select, textarea")) {
      $(document.activeElement).blur();
    }

    setTimeout(function () {
      fixAriaHidden();
    }, 200);
  });

  $(document).on("show.bs.modal", function (e) {
    var modal = $(e.target);
    modal.removeAttr("aria-hidden");
  });

  $("#filterRekapPegawaiModal").on("hidden.bs.modal", function (e) {
    var modal = $(this);
    modal.removeAttr("aria-hidden");
    modal.find('[tabindex="-1"]').removeAttr("tabindex");
    modal
      .find("input:not(:focus), select:not(:focus), textarea:not(:focus)")
      .blur();

    setTimeout(function () {
      modal.removeAttr("aria-hidden");
      fixAriaHidden();
    }, 100);
  });

  $("#filterRekapPegawaiModal").on("show.bs.modal", function (e) {
    var modal = $(this);
    modal.removeAttr("aria-hidden");
  });

  $(document).on("click", '[data-bs-dismiss="modal"]', function () {
    var modal = $(this).closest(".modal");
    setTimeout(function () {
      modal.removeAttr("aria-hidden");
      modal.find('[tabindex="-1"]').removeAttr("tabindex");
      modal
        .find("input:not(:focus), select:not(:focus), textarea:not(:focus)")
        .blur();
      if (!$(document.activeElement).is("input, select, textarea")) {
        $(document.activeElement).blur();
      }
      fixAriaHidden();
    }, 100);
  });

  $(document).on("click", ".modal-backdrop", function () {
    var modal = $(".modal.show");
    setTimeout(function () {
      modal.removeAttr("aria-hidden");
      modal.find('[tabindex="-1"]').removeAttr("tabindex");
      modal
        .find("input:not(:focus), select:not(:focus), textarea:not(:focus)")
        .blur();
      if (!$(document.activeElement).is("input, select, textarea")) {
        $(document.activeElement).blur();
      }
      fixAriaHidden();
    }, 100);
  });

  if (isDesktop()) {
    initDesktopTable();
  } else {
    collectMobileSatkerData();
  }

  const $searchInput = $("#mobileSearchRekapSatker");
  if ($searchInput.length) {
    let debounceTimer;
    $searchInput.on("input", function () {
      const term = $(this).val().toLowerCase();
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(function () {
        if (!term) {
          filteredMobileSatkerData = [...mobileSatkerData];
        } else {
          filteredMobileSatkerData = mobileSatkerData.filter(
            (item) =>
              (item.satker && item.satker.toLowerCase().includes(term)) ||
              (item.alamat && item.alamat.toLowerCase().includes(term))
          );
        }
        mobileSatkerPage = 1;
        loadMobileSatkerData();
      }, 250);
    });

    $("#mobileRekapSatkerPrev").on("click", function () {
      if (mobileSatkerPage > 1) {
        mobileSatkerPage--;
        loadMobileSatkerData();
      }
    });

    $("#mobileRekapSatkerNext").on("click", function () {
      const totalPages = Math.ceil(
        filteredMobileSatkerData.length / mobileSatkerPageSize
      );
      if (mobileSatkerPage < totalPages) {
        mobileSatkerPage++;
        loadMobileSatkerData();
      }
    });
  }

  $(window).on("resize", function () {
    if (isDesktop()) {
      if (!rekapDataTable) {
        initDesktopTable();
      }
    } else {
      if (!mobileSatkerData.length) {
        collectMobileSatkerData();
      }
    }
  });
});
