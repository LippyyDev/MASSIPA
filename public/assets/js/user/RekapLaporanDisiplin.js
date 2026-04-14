const DESKTOP_BREAKPOINT = 768;
const DESKTOP_PAGE_BUTTONS = 7;
const MOBILE_PAGE_BUTTONS = 3;

const isDesktop = () => window.innerWidth >= DESKTOP_BREAKPOINT;

let mobileRekapData = [];
let filteredMobileRekapData = [];
let currentMobileRekapPage = 1;
let mobileRekapPageSize = 10;
let currentMobileRekapSearch = "";

let notificationTimer = null;

function collectMobileRekapData() {
  mobileRekapData = [];
  $("#rekapTable tbody tr.data-row").each(function () {
    const $row = $(this);
    const checkedIds = JSON.parse(
      localStorage.getItem("rekap_laporan_checked_ids") || "[]"
    );
    const rowId = $row.data("id");
    const isChecked = checkedIds.includes(rowId.toString());

    mobileRekapData.push({
      id: rowId,
      no: $row.find("td:eq(0)").text().trim(),
      nama: $row.find("td:eq(1)").html().split("<br>")[0].trim(),
      nip: $row.find("td:eq(1)").html().split("<br>")[1]
        ? $row.find("td:eq(1)").html().split("<br>")[1].trim()
        : "",
      pangkat: $row.find("td:eq(2)").html().split("<br>")[0].trim(),
      golongan: $row.find("td:eq(2)").html().split("<br>")[1]
        ? $row.find("td:eq(2)").html().split("<br>")[1].trim()
        : "",
      jabatan: $row.find("td:eq(3)").text().trim(),
      satker: $row.find("td:eq(4)").text().trim(),
      terlambat: $row.find("td:eq(5)").text().trim(),
      tidak_absen_masuk: $row.find("td:eq(6)").text().trim(),
      pulang_awal: $row.find("td:eq(7)").text().trim(),
      tidak_absen_pulang: $row.find("td:eq(8)").text().trim(),
      keluar_tidak_izin: $row.find("td:eq(9)").text().trim(),
      tidak_masuk_tanpa_ket: $row.find("td:eq(10)").text().trim(),
      tidak_masuk_sakit: $row.find("td:eq(11)").text().trim(),
      tidak_masuk_kerja: $row.find("td:eq(12)").text().trim(),
      bentuk_pembinaan: $row.find("td:eq(13)").text().trim(),
      keterangan: $row.find("td:eq(14)").text().trim(),
      checked: isChecked,
    });
  });
  filteredMobileRekapData = [...mobileRekapData];
  loadMobileRekapData();
}

function renderMobileRekapCards(data, startIndex) {
  const container = $("#mobileCardsContainerRekap");
  if (!container.length) return;

  if (data.length === 0) {
    container.html(
      '<div class="text-center text-muted py-4">Belum ada data</div>'
    );
    const $paginationWrapper = $("#mobilePaginationRekap");
    if ($paginationWrapper.length) {
      $paginationWrapper.hide();
      $paginationWrapper.find(".mobile-rekap-page-item").remove();
    }
    $("#mobilePrevRekap").prop("disabled", true);
    $("#mobileNextRekap").prop("disabled", true);
    return;
  }

  let html = "";
  data.forEach((row, idx) => {
    const no = startIndex + idx + 1;
    const namaNip =
      row.nama +
      (row.nip
        ? '<br><small class="text-muted">NIP: ' + row.nip + "</small>"
        : "");
    const pangkatGol =
      row.pangkat +
      (row.golongan
        ? '<br><small class="text-muted">' + row.golongan + "</small>"
        : "");

    const namaParsed = namaNip.includes("<br>")
      ? namaNip.split("<br>")[0].trim()
      : namaNip.split("NIP:")[0].trim();
    const nipParsed = namaNip.includes("NIP:")
      ? namaNip.includes("<br>")
        ? namaNip
            .split("<br>")[1]
            .replace(/<small[^>]*>|<\/small>/g, "")
            .trim()
        : "NIP: " + namaNip.split("NIP:")[1].trim()
      : "";

    html += `
            <div class="border rounded mb-3 p-3 shadow-sm" style="background:#fff;">
                <div class="fw-bold mb-1 d-flex justify-content-between align-items-center">
                    <span>No. ${no} - ${namaParsed}</span>
                    <div class="form-check">
                        <input class="form-check-input mobile-checkbox-rekap" type="checkbox" value="${
                          row.id
                        }" ${row.checked ? "checked" : ""} id="mobileCheck${
      row.id
    }">
                    </div>
                </div>
                ${
                  nipParsed ? `<div class="mb-1"><b>${nipParsed}</b></div>` : ""
                }
                <div class="mb-1"><b>Pangkat / Golongan:</b> ${pangkatGol}</div>
                <div class="mb-1"><b>Jabatan:</b> ${row.jabatan}</div>
                <div class="mb-2"><b>Satuan Kerja:</b> ${row.satker}</div>
                <div class="border-top pt-2 mt-2">
                    <div class="fw-bold mb-2" style="font-size: 0.85rem;">Akumulasi Pelanggaran</div>
                    <div class="row g-2">
                        <div class="col-3 text-center">
                            <div class="small text-muted">t</div>
                            <div class="fw-bold">${row.terlambat}</div>
                        </div>
                        <div class="col-3 text-center">
                            <div class="small text-muted">tam</div>
                            <div class="fw-bold">${row.tidak_absen_masuk}</div>
                        </div>
                        <div class="col-3 text-center">
                            <div class="small text-muted">pa</div>
                            <div class="fw-bold">${row.pulang_awal}</div>
                        </div>
                        <div class="col-3 text-center">
                            <div class="small text-muted">tap</div>
                            <div class="fw-bold">${row.tidak_absen_pulang}</div>
                        </div>
                        <div class="col-3 text-center">
                            <div class="small text-muted">kti</div>
                            <div class="fw-bold">${row.keluar_tidak_izin}</div>
                        </div>
                        <div class="col-3 text-center">
                            <div class="small text-muted">tk</div>
                            <div class="fw-bold">${
                              row.tidak_masuk_tanpa_ket
                            }</div>
                        </div>
                        <div class="col-3 text-center">
                            <div class="small text-muted">tms</div>
                            <div class="fw-bold">${row.tidak_masuk_sakit}</div>
                        </div>
                        <div class="col-3 text-center">
                            <div class="small text-muted">tmk</div>
                            <div class="fw-bold">${row.tidak_masuk_kerja}</div>
                        </div>
                    </div>
                </div>
                ${
                  row.bentuk_pembinaan
                    ? `
                <div class="mt-2"><b>Bentuk Pembinaan:</b> ${row.bentuk_pembinaan}</div>
                `
                    : ""
                }
                ${
                  row.keterangan
                    ? `
                <div class="mt-1"><b>Keterangan:</b> ${row.keterangan}</div>
                `
                    : ""
                }
            </div>
        `;
  });

  container.html(html);
}

function loadMobileRekapData() {
  const start = (currentMobileRekapPage - 1) * mobileRekapPageSize;
  const end = start + mobileRekapPageSize;
  const pageData = filteredMobileRekapData.slice(start, end);

  renderMobileRekapCards(pageData, start);
  updateMobileRekapPagination();
}

function updateMobileRekapPagination() {
  const $paginationWrapper = $("#mobilePaginationRekap");
  if (!$paginationWrapper.length) return;
  const $paginationList = $paginationWrapper.find(".pagination");
  const $nextItem = $("#mobileNextRekap").closest(".page-item");
  const total = filteredMobileRekapData.length;
  const totalPages = Math.ceil(total / mobileRekapPageSize);

  if (totalPages <= 1) {
    $paginationList.find(".mobile-rekap-page-item").remove();
    $paginationWrapper.hide();
    $("#mobilePrevRekap").prop("disabled", true);
    $("#mobileNextRekap").prop("disabled", true);
    return;
  }

  $paginationWrapper.show();
  $("#mobilePrevRekap").prop("disabled", currentMobileRekapPage === 1);
  $("#mobileNextRekap").prop("disabled", currentMobileRekapPage >= totalPages);

  let startPage = Math.max(1, currentMobileRekapPage - 1);
  let endPage = Math.min(totalPages, startPage + (MOBILE_PAGE_BUTTONS - 1));

  if (endPage - startPage < MOBILE_PAGE_BUTTONS - 1) {
    startPage = Math.max(1, endPage - (MOBILE_PAGE_BUTTONS - 1));
  }

  $paginationList.find(".mobile-rekap-page-item").remove();

  let pageNumbersHtml = "";
  for (let i = startPage; i <= endPage; i++) {
    const activeClass = i === currentMobileRekapPage ? "active" : "";
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
      if (!isNaN(targetPage) && targetPage !== currentMobileRekapPage) {
        currentMobileRekapPage = targetPage;
        loadMobileRekapData();
      }
    });
}

$(document).on("change", ".mobile-checkbox-rekap", function () {
  const checkbox = this;
  const rowId = checkbox.value;
  const checkedIds = JSON.parse(
    localStorage.getItem("rekap_laporan_checked_ids") || "[]"
  );

  if (checkbox.checked) {
    if (!checkedIds.includes(rowId)) {
      checkedIds.push(rowId);
    }
  } else {
    const index = checkedIds.indexOf(rowId);
    if (index > -1) {
      checkedIds.splice(index, 1);
    }
  }

  localStorage.setItem("rekap_laporan_checked_ids", JSON.stringify(checkedIds));

  updateBadgeTerpilih();
  updateToggleCheckAllBtn();
});

$(document).on("click", ".border.rounded.mb-3.p-3.shadow-sm", function (e) {
  if (
    $(e.target).is('input[type="checkbox"]') ||
    $(e.target).closest('input[type="checkbox"]').length > 0 ||
    $(e.target).is("button") ||
    $(e.target).closest("button").length > 0 ||
    $(e.target).is("a") ||
    $(e.target).closest("a").length > 0 ||
    $(e.target).is("input") ||
    $(e.target).is("select") ||
    $(e.target).is("textarea") ||
    $(e.target).closest("input, select, textarea").length > 0
  ) {
    return;
  }

  const $checkbox = $(this).find(".mobile-checkbox-rekap");
  if ($checkbox.length > 0) {
    const newState = !$checkbox.is(":checked");
    $checkbox.prop("checked", newState).trigger("change");
  }
});

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

  $("#filterRekapLaporanModal").on("hidden.bs.modal", function (e) {
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

  $("#filterRekapLaporanModal").on("show.bs.modal", function (e) {
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
    if ($.fn.DataTable && $.fn.DataTable.ext && $.fn.DataTable.ext.pager) {
      $.fn.DataTable.ext.pager.numbers_length = DESKTOP_PAGE_BUTTONS;
    }
    $("#rekapTable").DataTable({
      paging: true,
      searching: true,
      ordering: true,
      info: false,
      autoWidth: false,
      scrollX: true,
      pageLength: -1,
      lengthChange: true,
      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, "Semua"],
      ],
      pagingType: "full_numbers",
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
      drawCallback: function () {
        setTimeout(function () {
          loadCheckboxState();
        }, 100);
      },
    });
  } else {
    collectMobileRekapData();
  }

  $("#filter_golongan").select2({
    closeOnSelect: false,
    width: "100%",
    placeholder: "Pilih golongan...",
    theme: "bootstrap-5",
  });
  $("#filter_jabatan").select2({
    closeOnSelect: false,
    width: "100%",
    placeholder: "Pilih jabatan...",
    theme: "bootstrap-5",
  });
  setTimeout(function () {
    $("#filterForm").addClass("visible");
  }, 120);

  if (isDesktop()) {
    checkAndResetOnPeriodChange();
  }

  updateToggleCheckAllBtn();
  updateBadgeTerpilih();

  if (isDesktop()) {
    $('#exportForm input[type="checkbox"][name="selected[]"]').each(
      function () {
        highlightRow(this);
      }
    );
  }

  let debounceTimer;
  $("#mobileSearchRekap").on("input", function () {
    const searchTerm = $(this).val().toLowerCase();
    currentMobileRekapSearch = searchTerm;
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(function () {
      if (searchTerm === "") {
        filteredMobileRekapData = [...mobileRekapData];
      } else {
        filteredMobileRekapData = mobileRekapData.filter((row) => {
          return (
            (row.nama && row.nama.toLowerCase().includes(searchTerm)) ||
            (row.nip && row.nip.toLowerCase().includes(searchTerm))
          );
        });
      }
      currentMobileRekapPage = 1;
      loadMobileRekapData();
    }, 300);
  });

  $("#mobilePrevRekap").on("click", function () {
    if (currentMobileRekapPage > 1) {
      currentMobileRekapPage--;
      loadMobileRekapData();
    }
  });

  $("#mobileNextRekap").on("click", function () {
    const totalPages = Math.ceil(
      filteredMobileRekapData.length / mobileRekapPageSize
    );
    if (currentMobileRekapPage < totalPages) {
      currentMobileRekapPage++;
      loadMobileRekapData();
    }
  });

  $(window).on("resize", function () {
    if (!isDesktop() && mobileRekapData.length === 0) {
      collectMobileRekapData();
    }
  });

  $("#filterForm").on("submit", function () {
    if (!isDesktop()) {
      setTimeout(function () {
        if (!isDesktop()) {
          collectMobileRekapData();
        }
      }, 500);
    }
  });
});

function submitExport(action) {
  const bulan = document.querySelector('[name="bulan"]')?.value || "";
  const tahun = document.querySelector('[name="tahun"]')?.value || "";

  // ambil dari localStorage
  const checkedIds = JSON.parse(
    localStorage.getItem("rekap_laporan_checked_ids") || "[]"
  );

  if (checkedIds.length === 0) {
    Swal.fire({
      icon: "warning",
      title: "Tidak ada data terpilih",
      text: "Silakan centang minimal satu data yang ingin diekspor!",
      timer: 2000,
      showConfirmButton: false,
    });
    return;
  }

  // bikin query string yang juga memuat selected[]
  const params = new URLSearchParams();
  params.set("bulan", bulan);
  params.set("tahun", tahun);
  checkedIds.forEach((id) => params.append("selected[]", id));

  const actionWithParams =
    action + (action.includes("?") ? "&" : "?") + params.toString();

  // tetap kirim POST (biar browser normal tetap aman)
  const form = document.createElement("form");
  form.method = "POST";
  form.action = actionWithParams;

  // body POST tetap dikirim juga (double safety)
  const addHidden = (name, value) => {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = name;
    input.value = value;
    form.appendChild(input);
  };

  addHidden("bulan", bulan);
  addHidden("tahun", tahun);
  checkedIds.forEach((id) => addHidden("selected[]", id));

  document.body.appendChild(form);
  form.submit();
  form.remove();
}

function checkAll(status) {
  const checkboxes = document.querySelectorAll(
    '#exportForm input[type="checkbox"][name="selected[]"]'
  );
  checkboxes.forEach((cb) => {
    cb.checked = status;
    saveCheckboxState();
  });
}

function saveCheckboxState() {
  const checkboxes = document.querySelectorAll(
    '#exportForm input[type="checkbox"][name="selected[]"]'
  );
  let checkedIds = JSON.parse(
    localStorage.getItem("rekap_laporan_checked_ids") || "[]"
  );
  checkboxes.forEach((cb) => {
    if (cb.checked && !checkedIds.includes(cb.value)) {
      checkedIds.push(cb.value);
    } else if (!cb.checked && checkedIds.includes(cb.value)) {
      checkedIds = checkedIds.filter((id) => id !== cb.value);
    }
  });
  localStorage.setItem("rekap_laporan_checked_ids", JSON.stringify(checkedIds));
}

function loadCheckboxState() {
  const checkedIds = JSON.parse(
    localStorage.getItem("rekap_laporan_checked_ids") || "[]"
  );
  const checkboxes = document.querySelectorAll(
    '#exportForm input[type="checkbox"][name="selected[]"]'
  );
  checkboxes.forEach((cb) => {
    cb.checked = checkedIds.includes(cb.value);
  });
}

function clearSavedState() {
  localStorage.removeItem("rekap_laporan_checked_ids");
  const checkboxes = document.querySelectorAll(
    '#exportForm input[type="checkbox"][name="selected[]"]'
  );
  checkboxes.forEach((cb) => (cb.checked = false));
  $('#exportForm input[type="checkbox"][name="selected[]"]').each(function () {
    highlightRow(this);
  });
  showClearNotification();
  setTimeout(() => {
    updateBadgeTerpilih();
  }, 3200);
}

function checkAndResetOnPeriodChange() {
  if (!isDesktop()) return;

  const currentBulan = $("#bulan").val();
  const currentTahun = $("#tahun").val();

  const savedBulan = localStorage.getItem("rekap_laporan_last_bulan");
  const savedTahun = localStorage.getItem("rekap_laporan_last_tahun");

  if (savedBulan !== null && savedTahun !== null) {
    if (savedBulan !== currentBulan || savedTahun !== currentTahun) {
      localStorage.removeItem("rekap_laporan_checked_ids");
      const checkboxes = document.querySelectorAll(
        '#exportForm input[type="checkbox"][name="selected[]"]'
      );
      checkboxes.forEach((cb) => (cb.checked = false));
      updateBadgeTerpilih();
    }
  }

  localStorage.setItem("rekap_laporan_last_bulan", currentBulan);
  localStorage.setItem("rekap_laporan_last_tahun", currentTahun);
}

function showClearNotification() {
  const notifPopup = document.getElementById("notifDataTerpilih");
  const notifText = document.getElementById("notifTextTerpilih");

  if (notifPopup && notifText) {
    const iconElement = notifPopup.querySelector("i");
    if (iconElement) {
      iconElement.className = "fas fa-trash-alt me-2";
    }
    notifText.textContent = "Seluruh data ceklis terhapus";

    if (notificationTimer) {
      clearTimeout(notificationTimer);
      notificationTimer = null;
    }

    notifPopup.classList.remove("show");
    notifPopup.style.display = "block";
    setTimeout(() => {
      notifPopup.classList.add("show");

      notificationTimer = setTimeout(() => {
        hideNotification();
        if (iconElement) {
          iconElement.className = "fas fa-check-circle me-2";
        }
        notificationTimer = null;
      }, 3000);
    }, 10);
  }
}

function toggleCheckAll() {
  if (isDesktop()) {
    const checkboxes = document.querySelectorAll(
      '#rekapTable tbody tr.data-row input[type="checkbox"][name="selected[]"]'
    );
    const checked = Array.from(checkboxes).filter((cb) => cb.checked).length;
    if (checked === checkboxes.length && checkboxes.length > 0) {
      checkboxes.forEach((cb) => (cb.checked = false));
    } else {
      checkboxes.forEach((cb) => (cb.checked = true));
    }
    saveCheckboxState();
    updateToggleCheckAllBtn();
    updateBadgeTerpilih();
    $('#exportForm input[type="checkbox"][name="selected[]"]').each(
      function () {
        highlightRow(this);
      }
    );
  } else {
    const checkedIds = JSON.parse(
      localStorage.getItem("rekap_laporan_checked_ids") || "[]"
    );
    const allFilteredIds = filteredMobileRekapData.map((row) =>
      row.id.toString()
    );
    const allChecked =
      allFilteredIds.length > 0 &&
      allFilteredIds.every((id) => checkedIds.includes(id));

    if (allChecked) {
      allFilteredIds.forEach((id) => {
        const index = checkedIds.indexOf(id);
        if (index > -1) checkedIds.splice(index, 1);
      });
    } else {
      allFilteredIds.forEach((id) => {
        if (!checkedIds.includes(id)) checkedIds.push(id);
      });
    }

    localStorage.setItem(
      "rekap_laporan_checked_ids",
      JSON.stringify(checkedIds)
    );
    collectMobileRekapData();
    updateToggleCheckAllBtn();
    updateBadgeTerpilih();
  }
}

function updateToggleCheckAllBtn() {
  const btn = document.getElementById("toggleCheckAllBtn");
  const btnMobile = document.getElementById("toggleCheckAllBtnMobile");

  if (isDesktop()) {
    if (!btn) return;
    const checkboxes = document.querySelectorAll(
      '#rekapTable tbody tr.data-row input[type="checkbox"][name="selected[]"]'
    );
    const checked = Array.from(checkboxes).filter((cb) => cb.checked).length;
    if (checked === checkboxes.length && checkboxes.length > 0) {
      btn.innerHTML = '<i class="bi bi-x-square"></i> Uncheck Semua';
      btn.classList.remove("btn-outline-primary");
      btn.classList.add("btn-outline-secondary");
    } else {
      btn.innerHTML = '<i class="bi bi-check2-square"></i> Pilih Semua';
      btn.classList.remove("btn-outline-secondary");
      btn.classList.add("btn-outline-primary");
    }
  } else {
    const checkedIds = JSON.parse(
      localStorage.getItem("rekap_laporan_checked_ids") || "[]"
    );
    const allFilteredIds = filteredMobileRekapData.map((row) =>
      row.id.toString()
    );
    const allChecked =
      allFilteredIds.length > 0 &&
      allFilteredIds.every((id) => checkedIds.includes(id));

    if (btn) {
      if (allChecked) {
        btn.innerHTML = '<i class="bi bi-x-square"></i> Uncheck Semua';
        btn.classList.remove("btn-outline-primary");
        btn.classList.add("btn-outline-secondary");
      } else {
        btn.innerHTML = '<i class="bi bi-check2-square"></i> Pilih Semua';
        btn.classList.remove("btn-outline-secondary");
        btn.classList.add("btn-outline-primary");
      }
    }

    if (btnMobile) {
      if (allChecked) {
        btnMobile.innerHTML = '<i class="bi bi-x-square"></i>';
        btnMobile.classList.remove("btn-outline-primary");
        btnMobile.classList.add("btn-outline-secondary");
      } else {
        btnMobile.innerHTML = '<i class="bi bi-check2-square"></i>';
        btnMobile.classList.remove("btn-outline-secondary");
        btnMobile.classList.add("btn-outline-primary");
      }
    }
  }
}

$(document).on(
  "change",
  '#exportForm input[type="checkbox"][name="selected[]"]',
  function () {
    saveCheckboxState();
    updateToggleCheckAllBtn();
    updateBadgeTerpilih();
  }
);

function updateBadgeTerpilih() {
  const checkedIds = JSON.parse(
    localStorage.getItem("rekap_laporan_checked_ids") || "[]"
  );
  const checked = checkedIds.length;

  const notifPopup = document.getElementById("notifDataTerpilih");
  const notifText = document.getElementById("notifTextTerpilih");

  if (notifPopup && notifText) {
    if (checked > 0) {
      notifText.textContent = checked + " data terpilih";
      showNotification();
    } else {
      hideNotification();
    }
  }
}

function showNotification() {
  const notifPopup = document.getElementById("notifDataTerpilih");
  if (notifPopup) {
    if (notificationTimer) {
      clearTimeout(notificationTimer);
      notificationTimer = null;
    }

    notifPopup.classList.remove("show");
    notifPopup.style.display = "block";
    setTimeout(() => {
      notifPopup.classList.add("show");

      notificationTimer = setTimeout(() => {
        hideNotification();
        notificationTimer = null;
      }, 3000);
    }, 10);
  }
}

function hideNotification() {
  const notifPopup = document.getElementById("notifDataTerpilih");
  if (notifPopup) {
    if (notificationTimer) {
      clearTimeout(notificationTimer);
      notificationTimer = null;
    }

    notifPopup.classList.remove("show");
    setTimeout(() => {
      if (!notifPopup.classList.contains("show")) {
        notifPopup.style.display = "none";
      }
    }, 300);
  }
}
function highlightRow(checkbox) {
  const tr = checkbox.closest("tr");
  if (checkbox.checked) {
    tr.style.background = "#ede9fe";
  } else {
    tr.style.background = "";
  }
}

$(document).ready(function () {
  if ($("#filter_golongan_mobile").length) {
    $("#filter_golongan_mobile").select2({
      closeOnSelect: false,
      width: "100%",
      placeholder: "Pilih golongan...",
      theme: "bootstrap-5",
      dropdownParent: $("#filterRekapLaporanModal"),
    });
  }

  if ($("#filter_jabatan_mobile").length) {
    $("#filter_jabatan_mobile").select2({
      closeOnSelect: false,
      width: "100%",
      placeholder: "Pilih jabatan...",
      theme: "bootstrap-5",
      dropdownParent: $("#filterRekapLaporanModal"),
    });
  }

  $("#filterRekapLaporanModal").on("show.bs.modal", function () {
    if ($("#bulan").length) {
      $("#bulan_mobile").val($("#bulan").val());
    }
    if ($("#tahun").length) {
      $("#tahun_mobile").val($("#tahun").val());
    }

    if ($("#filter_golongan").length) {
      const selectedGol = $("#filter_golongan").val() || [];
      $("#filter_golongan_mobile").val(selectedGol).trigger("change");
    }

    if ($("#filter_jabatan").length) {
      const selectedJab = $("#filter_jabatan").val() || [];
      $("#filter_jabatan_mobile").val(selectedJab).trigger("change");
    }
  });

  $("#btnFilterMobile").on("click", function (e) {
    e.preventDefault();

    const bulan = $("#bulan_mobile").val();
    const tahun = $("#tahun_mobile").val();
    const golongan = $("#filter_golongan_mobile").val() || [];
    const jabatan = $("#filter_jabatan_mobile").val() || [];

    let url = window.location.pathname + "?bulan=" + bulan + "&tahun=" + tahun;

    if (golongan.length > 0 && golongan[0] !== "") {
      golongan.forEach(function (gol) {
        url += "&golongan[]=" + encodeURIComponent(gol);
      });
    }

    if (jabatan.length > 0 && jabatan[0] !== "") {
      jabatan.forEach(function (jab) {
        url += "&jabatan[]=" + encodeURIComponent(jab);
      });
    }

    var modalElement = document.getElementById("filterRekapLaporanModal");
    if (modalElement) {
      var modal = bootstrap.Modal.getInstance(modalElement);
      if (modal) {
        modal.hide();
      } else {
        $("#filterRekapLaporanModal").modal("hide");
      }
    }

    window.location.href = url;
  });
});
