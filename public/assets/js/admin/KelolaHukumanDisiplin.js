const startTime = performance.now();

let searchTimeout;
const SEARCH_DEBOUNCE = 400;
const SEARCH_MAX_RESULT = 20;

document.addEventListener("DOMContentLoaded", function () {
  const loadTime = performance.now() - startTime;

  const searchInput = document.getElementById("pegawai_search");
  const resultsDiv = document.getElementById("pegawai_results");
  const hiddenInput = document.getElementById("pegawai_id");
  const jabatanInput = document.getElementById("jabatan");

  if (searchInput) {
    searchInput.addEventListener("input", function () {
      clearTimeout(searchTimeout);
      const query = this.value.trim();

      if (query.length < 2) {
        resultsDiv.style.display = "none";
        return;
      }

      searchTimeout = setTimeout(() => {
        searchPegawai(query);
      }, SEARCH_DEBOUNCE);
    });

    document.addEventListener("click", function (e) {
      if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
        resultsDiv.style.display = "none";
      }
    });
  }

  const table = document.getElementById("hukuman-table");
  if (table) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          observer.unobserve(entry.target);
        }
      });
    });
    observer.observe(table);
  }
});

function searchPegawai(query) {
  const resultsDiv = document.getElementById("pegawai_results");
  const searchInput = document.getElementById("pegawai_search");

  resultsDiv.innerHTML =
    '<div class="p-2 text-center"><small>Mencari...</small></div>';
  resultsDiv.style.display = "block";

  fetch(window.SEARCH_PEGAWAI_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: new URLSearchParams({ search: query, [window.CSRF_TOKEN_NAME]: window.CSRF_HASH })
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.data.length > 0) {
        if (data.data.length >= SEARCH_MAX_RESULT) {
          resultsDiv.innerHTML =
            '<div class="p-2 text-muted">Terlalu banyak hasil, perjelas kata kunci...</div>';
          resultsDiv.style.display = "block";
          return;
        }
        let html = "";
        data.data.forEach((pegawai) => {
          html += `<div class="p-2 border-bottom pegawai-option" 
                                data-id="${pegawai.id}" 
                                data-jabatan="${pegawai.jabatan || ""}"
                                style="cursor: pointer; hover: background-color: #f8f9fa;">
                                <strong>${pegawai.nama}</strong> <span class="pegawai-option-nip">(${pegawai.nip ? pegawai.nip : "-"})</span><br>
                                <small class="text-muted">${pegawai.jabatan || "Jabatan tidak tersedia"}</small>
                            </div>`;
        });
        resultsDiv.innerHTML = html;
        resultsDiv.style.display = "block";

        resultsDiv.querySelectorAll(".pegawai-option").forEach((option) => {
          option.addEventListener("click", function () {
            const id = this.dataset.id;
            const jabatan = this.dataset.jabatan;
            const nama = this.querySelector("strong").textContent;

            document.getElementById("pegawai_id").value = id;
            document.getElementById("jabatan").value = jabatan;
            document.getElementById("pegawai_search").value = nama;
            resultsDiv.style.display = "none";
          });
        });
      } else {
        resultsDiv.innerHTML =
          '<div class="p-2 text-muted">Tidak ada pegawai ditemukan</div>';
        resultsDiv.style.display = "block";
      }
    })
    .catch((error) => {
      resultsDiv.innerHTML =
        '<div class="p-2 text-danger">Error saat mencari pegawai</div>';
      resultsDiv.style.display = "block";
    });
}

let validationTimeout;
function validateForm() {
  clearTimeout(validationTimeout);
  validationTimeout = setTimeout(() => {
    const form = document.querySelector("form");
    if (form) {
      const inputs = form.querySelectorAll("input[required], select[required]");
      let isValid = true;

      inputs.forEach((input) => {
        if (!input.value.trim()) {
          isValid = false;
          input.classList.add("is-invalid");
        } else {
          input.classList.remove("is-invalid");
        }
      });

      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = !isValid;
      }
    }
  }, 200);
}

document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("form");
  if (form) {
    const inputs = form.querySelectorAll("input, select");
    inputs.forEach((input) => {
      input.addEventListener("input", validateForm);
      input.addEventListener("change", validateForm);
    });
  }

  // Validasi ukuran file PDF maksimal 1MB
  const MAX_PDF_SIZE = 1 * 1024 * 1024; // 1MB
  document.addEventListener("change", function (e) {
    if (e.target && e.target.type === "file") {
      const file = e.target.files[0];
      if (file && file.size > MAX_PDF_SIZE) {
        Swal.fire({
          icon: "warning",
          title: "File Terlalu Besar!",
          text: "Ukuran file berkas PDF maksimal adalah 1 MB. Silakan pilih file yang lebih kecil.",
          confirmButtonColor: "#7c3aed",
          confirmButtonText: "Mengerti",
        });
        e.target.value = "";
      }
    }
  });

  // Validasi tanggal: tanggal_berakhir tidak boleh lebih kecil dari tanggal_mulai
  function validateTanggalRange(mulaiVal, berakhirVal) {
    if (mulaiVal && berakhirVal && berakhirVal < mulaiVal) {
      Swal.fire({
        icon: "error",
        title: "Tanggal Tidak Valid!",
        text: "Tanggal Berakhir tidak boleh lebih kecil dari Tanggal Mulai.",
        confirmButtonColor: "#7c3aed",
        confirmButtonText: "Mengerti",
      });
      return false;
    }
    return true;
  }

  // Cegah submit form tambah jika tanggal tidak valid
  const allForms = document.querySelectorAll("form:not(#formEditHukuman)");
  allForms.forEach(function (formEl) {
    formEl.addEventListener("submit", function (e) {
      const mulai = formEl.querySelector('[name="tanggal_mulai"]');
      const berakhir = formEl.querySelector('[name="tanggal_berakhir"]');
      if (mulai && berakhir) {
        if (!validateTanggalRange(mulai.value, berakhir.value)) {
          e.preventDefault();
          e.stopPropagation();
        }
      }
    });
  });

  // Cegah submit form edit jika tanggal tidak valid
  const formEdit = document.getElementById("formEditHukuman");
  if (formEdit) {
    formEdit.addEventListener("submit", function (e) {
      const mulai = document.getElementById("edit_tanggal_mulai");
      const berakhir = document.getElementById("edit_tanggal_berakhir");
      if (mulai && berakhir) {
        if (!validateTanggalRange(mulai.value, berakhir.value)) {
          e.preventDefault();
          e.stopPropagation();
        }
      }
    });
  }
});

function updateJabatan() {}

const HUKUMAN_PER_PAGE = 10;
let hukumanCurrentPage = 1;

function escapeHtml(text) {
  return text.replace(/[&<>"]/g, function (c) {
    return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" }[c];
  });
}
function formatDate(dateStr) {
  const d = new Date(dateStr);
  if (isNaN(d)) return "-";
  return d.toLocaleDateString("id-ID");
}
if (!window.BASE_URL) {
  window.BASE_URL = window.location.origin + "/";
}

window.showEditHukumanModal = function (id) {
  document.getElementById("formEditHukuman").reset();
  document.getElementById("edit_file_sk_link").innerHTML = "";

  document.getElementById("modalEditHukumanLabel").innerHTML =
    '<span class="spinner-border spinner-border-sm"></span> Memuat...';

  var modal = document.getElementById("modalEditHukuman");
  modal.style.display = "block";
  modal.style.position = "fixed";
  modal.style.top = "0";
  modal.style.left = "0";
  modal.style.width = "100%";
  modal.style.height = "100%";
  modal.style.zIndex = "9999";
  modal.style.backgroundColor = "rgba(0,0,0,0.5)";
  modal.style.overflowY = "auto";
  modal.classList.add("show");
  modal.setAttribute("aria-modal", "true");
  modal.removeAttribute("aria-hidden");
  document.body.classList.add("modal-open");

  if (!document.querySelector(".modal-backdrop")) {
    var backdrop = document.createElement("div");
    backdrop.className = "modal-backdrop fade show";
    backdrop.style.cssText =
      "position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9998;";
    document.body.appendChild(backdrop);
  }

  var modalDialog = modal.querySelector(".modal-dialog");
  if (modalDialog) {
    modalDialog.style.margin = "10px auto";
    modalDialog.style.maxWidth = "95%";
    modalDialog.style.position = "relative";
    modalDialog.style.zIndex = "10000";
  }

  fetch(`${window.BASE_URL}admin/getHukumanDisiplinDetailAjax/${id}`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: new URLSearchParams({ [window.CSRF_TOKEN_NAME]: window.CSRF_HASH })
  })
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) {
        document.getElementById("modalEditHukumanLabel").innerText =
          "Edit Hukuman Disiplin";
        Swal.fire("Gagal", data.message || "Data tidak ditemukan", "error");
        return;
      }
      const d = data.data;
      document.getElementById("modalEditHukumanLabel").innerText =
        "Edit Hukuman Disiplin";
      document.getElementById("edit_id").value = d.id;
      document.getElementById("edit_pegawai_search").value = d.nama || "";
      document.getElementById("edit_pegawai_id").value = d.pegawai_id || "";
      let pegOpt = '<option value="">- Pilih Pegawai -</option>';
      data.list_pegawai.forEach((p) => {
        pegOpt += `<option value="${p.id}" data-jabatan="${escapeHtml(p.jabatan || "")}" ${p.id == d.pegawai_id ? "selected" : ""}>${escapeHtml(p.nama)}</option>`;
      });
      document.getElementById("edit_pegawai_id").innerHTML = pegOpt;
      document.getElementById("edit_jabatan").value = d.jabatan || "";
      document.getElementById("edit_no_sk").value = d.no_sk || "";
      document.getElementById("edit_tanggal_mulai").value =
        d.tanggal_mulai || "";
      document.getElementById("edit_tanggal_berakhir").value =
        d.tanggal_berakhir || "";
      document.getElementById("edit_hukuman_dijatuhkan").value =
        d.hukuman_dijatuhkan || "";
      document.getElementById("edit_peraturan_dilanggar").value =
        d.peraturan_dilanggar || "";
      document.getElementById("edit_keterangan").value = d.keterangan || "";
      if (d.file_sk) {
        document.getElementById("edit_file_sk_link").innerHTML =
          `<a href='${window.BASE_URL}admin/kelola_hukuman_disiplin/getFile/${encodeURIComponent(d.file_sk)}' target='_blank' class='btn btn-sm btn-info'><i class='bi bi-download'></i> Download SK Lama</a>`;
      }
    });

  document.getElementById("edit_pegawai_id").onchange = function () {
    const sel = this;
    const jabatan =
      sel.options[sel.selectedIndex]?.getAttribute("data-jabatan") || "";
    document.getElementById("edit_jabatan").value = jabatan;
  };
};

window.closeEditHukumanModal = function () {
  var modal = document.getElementById("modalEditHukuman");
  if (modal) {
    modal.style.display = "none";
    modal.classList.remove("show");
    modal.removeAttribute("aria-modal");
    modal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("modal-open");

    var backdrop = document.querySelector(".modal-backdrop");
    if (backdrop) {
      backdrop.remove();
    }
  }
};

let editSearchTimeout;
const EDIT_SEARCH_DEBOUNCE = 400;

document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("edit_pegawai_search");
  const resultsDiv = document.getElementById("edit_pegawai_results");
  const hiddenInput = document.getElementById("edit_pegawai_id");
  const jabatanInput = document.getElementById("edit_jabatan");
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      clearTimeout(editSearchTimeout);
      const query = this.value.trim();
      if (query.length < 2) {
        resultsDiv.style.display = "none";
        return;
      }
      editSearchTimeout = setTimeout(() => {
        searchPegawaiEdit(query);
      }, EDIT_SEARCH_DEBOUNCE);
    });
    document.addEventListener("click", function (e) {
      if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
        resultsDiv.style.display = "none";
      }
    });
  }
});

function searchPegawaiEdit(query) {
  const resultsDiv = document.getElementById("edit_pegawai_results");
  const searchInput = document.getElementById("edit_pegawai_search");
  resultsDiv.innerHTML =
    '<div class="p-2 text-center"><small>Mencari...</small></div>';
  resultsDiv.style.display = "block";
  fetch(window.SEARCH_PEGAWAI_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
    body: new URLSearchParams({ search: query, [window.CSRF_TOKEN_NAME]: window.CSRF_HASH })
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.data.length > 0) {
        let html = "";
        data.data.forEach((pegawai) => {
          html += `<div class="p-2 border-bottom pegawai-option-edit" 
                                data-id="${pegawai.id}" 
                                data-jabatan="${pegawai.jabatan || ""}"
                                data-nama="${pegawai.nama || ""}"
                                style="cursor: pointer; hover: background-color: #f8f9fa;">
                                <strong>${pegawai.nama}</strong> <span class="pegawai-option-nip">(${pegawai.nip ? pegawai.nip : "-"})</span><br>
                                <small class="text-muted">${pegawai.jabatan || "Jabatan tidak tersedia"}</small>
                            </div>`;
        });
        resultsDiv.innerHTML = html;
        resultsDiv.style.display = "block";
        resultsDiv
          .querySelectorAll(".pegawai-option-edit")
          .forEach((option) => {
            option.addEventListener("click", function () {
              const id = this.dataset.id;
              const jabatan = this.dataset.jabatan;
              const nama = this.dataset.nama;
              document.getElementById("edit_pegawai_id").value = id;
              document.getElementById("edit_jabatan").value = jabatan;
              document.getElementById("edit_pegawai_search").value = nama;
              resultsDiv.style.display = "none";
            });
          });
      } else {
        resultsDiv.innerHTML =
          '<div class="p-2 text-muted">Tidak ada pegawai ditemukan</div>';
        resultsDiv.style.display = "block";
      }
    })
    .catch((error) => {
      resultsDiv.innerHTML =
        '<div class="p-2 text-danger">Error saat mencari pegawai</div>';
      resultsDiv.style.display = "block";
    });
}

document.addEventListener("show.bs.modal", function (e) {
  if (e.target && e.target.id === "modalEditHukuman") {
    const nama = e.target.querySelector("#edit_pegawai_search");
    const id = e.target.querySelector("#edit_pegawai_id").value;
  }
});

document.addEventListener("DOMContentLoaded", function () {
  document.addEventListener("click", function (e) {
    if (
      e.target.classList.contains("btn-close") ||
      e.target.classList.contains("modal-backdrop") ||
      e.target.getAttribute("data-bs-dismiss") === "modal" ||
      e.target.classList.contains("btn-secondary")
    ) {
      closeEditHukumanModal();
    }
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      closeEditHukumanModal();
    }
  });
});

$(document).ready(function () {
  var flashMsg = $("body").data("flash-msg");
  var flashType = $("body").data("flash-type");

  if (flashMsg) {
    Swal.fire({
      icon: flashType === "success" ? "success" : "error",
      title: flashType === "success" ? "Berhasil" : "Gagal",
      text: flashMsg,
      timer: 2500,
      showConfirmButton: false,
    });
  }
});

window.applyDarkModeToCards = function () {
  var isDarkMode =
    $("body").attr("data-bs-theme") === "dark" ||
    $("html").attr("data-bs-theme") === "dark" ||
    $("body").hasClass("dark-mode") ||
    $("body").hasClass("dark");

  if (isDarkMode) {
    $(".mobile-hukuman-card").css({
      background: "#232336",
      "border-color": "#2a2a3f",
      color: "#fff",
    });

    $(".mobile-hukuman-card .card-title").css("color", "#fff");
    $(".mobile-hukuman-card .card-content").css("color", "#fff");
    $(".mobile-hukuman-card .card-label").css("color", "#fff");
    $(".mobile-hukuman-card .card-value").css("color", "#fff");

    $(".mobile-pengajuan-card").css({
      background: "#232336",
      "border-color": "#2a2a3f",
      color: "#fff",
    });

    $(".mobile-pengajuan-card .card-title").css("color", "#fff");
    $(".mobile-pengajuan-card .card-content").css("color", "#fff");
    $(".mobile-pengajuan-card .card-label").css("color", "#fff");
    $(".mobile-pengajuan-card .card-value").css("color", "#fff");
  } else {
    $(".mobile-hukuman-card").css({
      background: "#fff",
      "border-color": "#dee2e6",
      color: "#212529",
    });

    $(".mobile-hukuman-card .card-title").css("color", "#212529");
    $(".mobile-hukuman-card .card-content").css("color", "#212529");
    $(".mobile-hukuman-card .card-label").css("color", "#212529");
    $(".mobile-hukuman-card .card-value").css("color", "#212529");

    $(".mobile-pengajuan-card").css({
      background: "#fff",
      "border-color": "#dee2e6",
      color: "#212529",
    });

    $(".mobile-pengajuan-card .card-title").css("color", "#212529");
    $(".mobile-pengajuan-card .card-content").css("color", "#212529");
    $(".mobile-pengajuan-card .card-label").css("color", "#212529");
    $(".mobile-pengajuan-card .card-value").css("color", "#212529");
  }
};

$(function () {
  var $collapseTambah = $(".collapse-form");
  var $chevronTambah = $("#chevronTambahHukuman");
  var openedTambah = false;
  $("#toggleTambahHukuman").on("click", function () {
    if (openedTambah) {
      $collapseTambah.slideUp(200);
      $chevronTambah.removeClass("bi-chevron-up").addClass("bi-chevron-down");
    } else {
      $collapseTambah.slideDown(200);
      $chevronTambah.removeClass("bi-chevron-down").addClass("bi-chevron-up");
    }
    openedTambah = !openedTambah;
  });
  var $collapsePengajuan = $(".collapse-pengajuan");
  var $chevronPengajuan = $("#chevronPengajuanHukuman");
  var openedPengajuan = false;
  $("#togglePengajuanHukuman").on("click", function () {
    if (openedPengajuan) {
      $collapsePengajuan.slideUp(200);
      $chevronPengajuan
        .removeClass("bi-chevron-up")
        .addClass("bi-chevron-down");
    } else {
      $collapsePengajuan.slideDown(200);
      $chevronPengajuan
        .removeClass("bi-chevron-down")
        .addClass("bi-chevron-up");
    }
    openedPengajuan = !openedPengajuan;
  });
});

$(document).ready(function () {
  $(".modal").removeAttr("aria-hidden");

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

  $(document).on("click", '[data-bs-toggle="modal"]', function (e) {
    var target = $(this).data("bs-target");
    var modal = $(target);

    if (modal.length > 0) {
      modal.css({
        display: "block",
        position: "fixed",
        top: "0",
        left: "0",
        width: "100%",
        height: "100%",
        "z-index": "9999",
        "background-color": "rgba(0,0,0,0.5)",
        "overflow-y": "auto",
      });
      modal.addClass("show");
      modal.attr("aria-modal", "true");
      modal.removeAttr("aria-hidden");
      $("body").addClass("modal-open");

      if ($(".modal-backdrop").length === 0) {
        $("body").append(
          '<div class="modal-backdrop fade show" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9998;"></div>',
        );
      }

      modal.find(".modal-dialog").css({
        margin: "10px auto",
        "max-width": "95%",
        position: "relative",
        "z-index": "10000",
      });
    }
  });

  $(document).on(
    "click",
    '[data-bs-dismiss="modal"], .btn-close',
    function (e) {
      var modal = $(this).closest(".modal");
      if (modal.length > 0) {
        modal.removeClass("show");
        modal.css("display", "none");
        modal.removeAttr("aria-modal");
        $("body").removeClass("modal-open");
        $(".modal-backdrop").remove();
      }
    },
  );

  $(document).on("click", ".modal-backdrop", function (e) {
    var modal = $(".modal.show");
    if (modal.length > 0) {
      modal.removeClass("show");
      modal.css("display", "none");
      modal.removeAttr("aria-modal");
      $("body").removeClass("modal-open");
      $(this).remove();
    }
  });
});

$(document).ready(function () {
  if (window.innerWidth >= 768) {
    $("#admin-hukuman-table").DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: window.getHukumanDisiplinAjaxDataTablesUrl,
        type: "POST",
        dataSrc: function (json) {
          if (json && json.data) {
            return json.data;
          } else {
            return [];
          }
        },
        error: function (xhr, error, thrown) {
          setTimeout(function () {
            location.reload();
          }, 2000);
        },
      },
      columns: [
        {
          data: null,
          render: function (data, type, row, meta) {
            return meta.row + meta.settings._iDisplayStart + 1;
          },
          orderable: false,
        },
        { data: "nama" },
        { data: "jabatan" },
        {
          data: "no_sk",
          render: function (data, type, row) {
            if (row.file_sk) {
              return `<a href='${window.location.origin}/admin/kelola_hukuman_disiplin/getFile/${encodeURIComponent(row.file_sk)}' target='_blank' style='color:inherit;text-decoration:none;'>${data}</a> <a href='${window.location.origin}/admin/kelola_hukuman_disiplin/getFile/${encodeURIComponent(row.file_sk)}' target='_blank'><i class='bi bi-file-earmark-pdf-fill text-danger'></i></a>`;
            } else {
              return data;
            }
          },
        },
        {
          data: null,
          render: function (data, type, row) {
            return `${moment(row.tanggal_mulai).format("DD-MM-YYYY")} s/d ${moment(row.tanggal_berakhir).format("DD-MM-YYYY")}`;
          },
        },
        { data: "hukuman_dijatuhkan" },
        { data: "peraturan_dilanggar" },
        { data: "keterangan" },
        {
          data: null,
          orderable: false,
          render: function (data, type, row) {
            let aksi = "";
            if (row.file_sk)
              aksi += `<a href='${window.location.origin}/admin/kelola_hukuman_disiplin/getFile/${encodeURIComponent(row.file_sk)}' target='_blank' class='btn btn-info btn-sm btn-action me-1'><i class='bi bi-file-earmark-pdf-fill'></i></a>`;
            aksi += `<button type='button' class='btn btn-warning btn-sm btn-action' onclick='showEditHukumanModal(${row.id})'><i class='bi bi-pencil-square'></i></button>`;
            aksi += `<a href='${window.location.origin}/admin/deleteHukumanDisiplin/${row.id}' class='btn btn-danger btn-sm btn-action btn-delete'><i class='bi bi-trash'></i></a>`;
            return aksi;
          },
        },
      ],
      lengthMenu: [10, 25, 50],
      responsive: true,
      info: false,
      language: {
        search: "Cari:",
        lengthMenu: "Tampil _MENU_ baris",
        paginate: { previous: "&lt;", next: "&gt;" },
      },
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-12"p>>',
      createdRow: function (row, data, dataIndex) {
        const labels = [
          "No",
          "Nama Pegawai",
          "Jabatan",
          "No SK",
          "Periode",
          "Hukuman",
          "Peraturan",
          "Keterangan",
          "Aksi",
        ];
        $(row)
          .find("td")
          .each(function (i) {
            $(this).attr("data-label", labels[i]);
          });
      },
    });
  } else {
    loadMobileHukumanCards();
  }

  var mobileHukumanPage = 1;
  var mobileHukumanPageSize = 10;
  var mobileHukumanTotal = 0;
  var mobileHukumanData = [];
  var filteredMobileHukumanData = [];

  function loadMobileHukumanCards() {
    $.ajax({
      url: window.getHukumanDisiplinAjaxDataTablesUrl,
      type: "POST",
      data: {
        length: 1000,
        start: 0,
      },
      success: function (response) {
        if (response.data) {
          mobileHukumanData = response.data;
          mobileHukumanTotal = response.recordsFiltered || response.data.length;
          filteredMobileHukumanData = mobileHukumanData;
          mobileHukumanPage = 1;
          renderMobileHukumanCards();
        }
      },
      error: function () {
        $("#mobileHukumanCards").html(
          '<div class="text-center py-3">Gagal memuat data</div>',
        );
      },
    });
  }

  function renderMobileHukumanCards() {
    var start = (mobileHukumanPage - 1) * mobileHukumanPageSize;
    var end = start + mobileHukumanPageSize;
    var pageData = filteredMobileHukumanData.slice(start, end);

    if (pageData.length === 0) {
      $("#mobileHukumanCards").html(
        '<div class="text-center py-3">Tidak ada data</div>',
      );
      $("#mobilePagination").hide();
      return;
    }

    window.generateMobileHukumanCards(pageData, start);
    updateMobileHukumanPagination();

    if (typeof window.applyDarkModeToCards === "function") {
      window.applyDarkModeToCards();
    }
  }

  function updateMobileHukumanPagination() {
    var total = filteredMobileHukumanData.length;
    var totalPages = Math.ceil(total / mobileHukumanPageSize);

    if (totalPages <= 1) {
      $("#mobilePagination").hide();
      return;
    }

    $("#mobilePagination").show();

    var pageNumbersHtml = "";
    var startPage = Math.max(1, mobileHukumanPage - 1);
    var endPage = Math.min(totalPages, startPage + 2);

    if (endPage - startPage < 2) {
      startPage = Math.max(1, endPage - 2);
    }

    var maxPages = Math.min(3, totalPages);
    var actualStart = Math.max(1, mobileHukumanPage - 1);
    var actualEnd = Math.min(totalPages, actualStart + maxPages - 1);

    if (actualEnd - actualStart < maxPages - 1) {
      actualStart = Math.max(1, actualEnd - maxPages + 1);
    }

    for (var i = actualStart; i <= actualEnd; i++) {
      var activeClass = i === mobileHukumanPage ? "active" : "";
      pageNumbersHtml +=
        '<li class="page-item"><button class="page-link page-number ' +
        activeClass +
        '" data-page="' +
        i +
        '">' +
        i +
        "</button></li>";
    }

    $("#mobilePageNumbers").html(pageNumbersHtml);

    $("#mobilePrev").prop("disabled", mobileHukumanPage === 1);
    $("#mobileNext").prop("disabled", mobileHukumanPage >= totalPages);

    $(".page-number")
      .off("click")
      .on("click", function () {
        mobileHukumanPage = parseInt($(this).data("page"));
        renderMobileHukumanCards();
      });
  }

  $(document).on("click", "#mobilePrev:not(:disabled)", function (e) {
    e.preventDefault();
    if (mobileHukumanPage > 1) {
      mobileHukumanPage--;
      renderMobileHukumanCards();
    }
  });

  $(document).on("click", "#mobileNext:not(:disabled)", function (e) {
    e.preventDefault();
    var totalPages = Math.ceil(
      filteredMobileHukumanData.length / mobileHukumanPageSize,
    );
    if (mobileHukumanPage < totalPages) {
      mobileHukumanPage++;
      renderMobileHukumanCards();
    }
  });

  window.generateMobileHukumanCards = function (data, startIndex) {
    var cardsHtml = "";
    var start = startIndex || 0;

    var isDarkMode =
      $("body").attr("data-bs-theme") === "dark" ||
      $("html").attr("data-bs-theme") === "dark" ||
      $("body").hasClass("dark-mode") ||
      $("body").hasClass("dark");

    var cardStyle = isDarkMode
      ? "background: #232336 !important; border-color: #2a2a3f !important; color: #fff !important;"
      : "background: #fff !important; border-color: #dee2e6 !important; color: #212529 !important;";

    var textStyle = isDarkMode
      ? "color: #fff !important;"
      : "color: #212529 !important;";

    data.forEach(function (row, index) {
      var no = start + index + 1;
      var periode =
        moment(row.tanggal_mulai).format("DD-MM-YYYY") +
        " s/d " +
        moment(row.tanggal_berakhir).format("DD-MM-YYYY");
      var noSkHtml = row.file_sk
        ? `<a href='${window.location.origin}/admin/kelola_hukuman_disiplin/getFile/${encodeURIComponent(row.file_sk)}' target='_blank' style='color:inherit;text-decoration:none;'>${row.no_sk}</a> <a href='${window.location.origin}/admin/kelola_hukuman_disiplin/getFile/${encodeURIComponent(row.file_sk)}' target='_blank'><i class='bi bi-file-earmark-pdf-fill text-danger'></i></a>`
        : row.no_sk;

      var statusBadge = "";
      if (row.status) {
        if (row.status == "pending") {
          statusBadge =
            '<span class="badge bg-warning text-dark">PENDING</span>';
        } else if (row.status == "approved") {
          statusBadge = '<span class="badge bg-success">DISETUJUI</span>';
        } else if (row.status == "rejected") {
          statusBadge = '<span class="badge bg-danger">DITOLAK</span>';
        } else {
          statusBadge = '<span class="badge bg-secondary">-</span>';
        }
      }

      cardsHtml += `
                <div class="border rounded p-3 mb-3 mobile-hukuman-card" style="${cardStyle}">
                    <div class="fw-bold mb-1 card-title d-flex justify-content-between align-items-center" style="${textStyle}">
                        <span>No. ${no} - ${row.nama}</span>
                        ${statusBadge}
                    </div>
                    <div class="small mb-2 card-content" style="${textStyle}">
                        <div class="card-row"><span class="card-label" style="font-weight: bold !important; ${textStyle} display: inline-block; min-width: 80px;">Jabatan:</span> <span class="card-value" style="${textStyle}">${row.jabatan}</span></div>
                        <div class="card-row"><span class="card-label" style="font-weight: bold !important; ${textStyle} display: inline-block; min-width: 80px;">No SK:</span> <span class="card-value" style="${textStyle}">${noSkHtml}</span></div>
                        <div class="card-row"><span class="card-label" style="font-weight: bold !important; ${textStyle} display: inline-block; min-width: 80px;">Periode:</span> <span class="card-value" style="${textStyle}">${periode}</span></div>
                        <div class="card-row"><span class="card-label" style="font-weight: bold !important; ${textStyle} display: inline-block; min-width: 80px;">Hukuman:</span> <span class="card-value" style="${textStyle}">${row.hukuman_dijatuhkan}</span></div>
                        <div class="card-row"><span class="card-label" style="font-weight: bold !important; ${textStyle} display: inline-block; min-width: 80px;">Peraturan:</span> <span class="card-value" style="${textStyle}">${row.peraturan_dilanggar}</span></div>
                        <div class="card-row"><span class="card-label" style="font-weight: bold !important; ${textStyle} display: inline-block; min-width: 80px;">Keterangan:</span> <span class="card-value" style="${textStyle}">${row.keterangan || "-"}</span></div>
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        ${row.file_sk ? `<a href='${window.location.origin}/admin/kelola_hukuman_disiplin/getFile/${encodeURIComponent(row.file_sk)}' target='_blank' class='btn btn-info btn-sm btn-action' title='Lihat File'><i class='bi bi-file-earmark-pdf-fill'></i></a>` : ""}
                        <button type="button" class="btn btn-warning btn-sm btn-action" onclick="showEditHukumanModal(${row.id})" title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button type="button" class="btn btn-danger btn-sm btn-action" onclick="deleteHukumanDisiplin(${row.id})" title="Hapus">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            `;
    });

    $("#mobileHukumanCards").html(cardsHtml);
  };

  $("#search_mobile_hukuman").on("input", function () {
    var searchTerm = $(this).val().toLowerCase();
    filteredMobileHukumanData = mobileHukumanData.filter(function (row) {
      return (
        row.nama.toLowerCase().includes(searchTerm) ||
        (row.jabatan && row.jabatan.toLowerCase().includes(searchTerm)) ||
        (row.no_sk && row.no_sk.toLowerCase().includes(searchTerm))
      );
    });
    mobileHukumanPage = 1;
    renderMobileHukumanCards();
  });
});

function deleteHukumanDisiplin(id) {
  Swal.fire({
    title: "Yakin hapus data ini?",
    text: "Data yang dihapus tidak dapat dikembalikan!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Ya, Hapus",
    cancelButtonText: "Batal",
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href =
        window.location.origin + "/admin/deleteHukumanDisiplin/" + id;
    }
  });
}

var isSubmitting = false;

$(document).ready(function () {
  $(document).on("click", ".btn-approve", function (e) {
    e.preventDefault();
    var url = $(this).attr("href");
    Swal.fire({
      title: "Setujui pengajuan ini?",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Ya, Setujui",
      cancelButtonText: "Batal",
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = url;
      }
    });
  });
  $(document).on("click", ".btn-reject", function (e) {
    e.preventDefault();
    var url = $(this).attr("href");
    Swal.fire({
      title: "Tolak pengajuan ini?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, Tolak",
      cancelButtonText: "Batal",
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = url;
      }
    });
  });
  $(document).on("click", ".btn-delete", function (e) {
    e.preventDefault();
    var url = $(this).attr("href");
    Swal.fire({
      title: "Yakin hapus data ini?",
      text: "Data yang dihapus tidak dapat dikembalikan!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, Hapus",
      cancelButtonText: "Batal",
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = url;
      }
    });
  });

  $(document).on(
    "click",
    'button[onclick*="showEditHukumanModal"]',
    function (e) {
      e.preventDefault();
      var onclick = $(this).attr("onclick");
      var id = onclick.match(/showEditHukumanModal\((\d+)\)/)[1];

      if (typeof showEditHukumanModal === "function") {
        showEditHukumanModal(id);
      } else {
        var modal = $("#modalEditHukuman");
        if (modal.length > 0) {
          modal.css({
            display: "block",
            position: "fixed",
            top: "0",
            left: "0",
            width: "100%",
            height: "100%",
            "z-index": "9999",
            "background-color": "rgba(0,0,0,0.5)",
            "overflow-y": "auto",
          });
          modal.addClass("show");
          modal.attr("aria-modal", "true");
          modal.removeAttr("aria-hidden");
          $("body").addClass("modal-open");

          if ($(".modal-backdrop").length === 0) {
            $("body").append(
              '<div class="modal-backdrop fade show" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9998;"></div>',
            );
          }

          modal.find(".modal-dialog").css({
            margin: "10px auto",
            "max-width": "95%",
            position: "relative",
            "z-index": "10000",
          });
        }
      }
    },
  );

  $(document).on("click", "#modalEditHukuman .btn-secondary", function () {
    var modal = $("#modalEditHukuman");
    modal.css("display", "none");
    modal.removeClass("show");
    modal.removeAttr("aria-modal");
    modal.attr("aria-hidden", "true");
    $("body").removeClass("modal-open");
    $(".modal-backdrop").remove();
  });

  $(document).on("click", "[data-bs-theme-value]", function () {
    setTimeout(function () {
      if (typeof window.applyDarkModeToCards === "function") {
        window.applyDarkModeToCards();
      }
    }, 100);
  });

  var observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (
        mutation.type === "attributes" &&
        mutation.attributeName === "data-bs-theme"
      ) {
        setTimeout(function () {
          if (typeof window.applyDarkModeToCards === "function") {
            window.applyDarkModeToCards();
          }
        }, 100);
      }
    });
  });

  observer.observe(document.body, {
    attributes: true,
    attributeFilter: ["data-bs-theme"],
  });

  $(document).ready(function () {
    setTimeout(function () {
      if (typeof window.applyDarkModeToCards === "function") {
        window.applyDarkModeToCards();
      }
    }, 500);
  });

  $(document).on("click", "#togglePengajuanHukuman", function () {
    setTimeout(function () {
      if (typeof window.applyDarkModeToCards === "function") {
        window.applyDarkModeToCards();
      }
    }, 300);
  });

  $("#exportPdfBtn").on("click", function (e) {
    var isPublic = $("#exportPublic").is(":checked");
    if (isPublic) {
      this.href =
        window.location.origin + "/admin/exportHukumanDisiplinPdf?public=1";
    } else {
      this.href = window.location.origin + "/admin/exportHukumanDisiplinPdf";
    }
  });

  $("#exportWordBtn").on("click", function (e) {
    var isPublic = $("#exportPublic").is(":checked");
    if (isPublic) {
      this.href =
        window.location.origin + "/admin/exportHukumanDisiplinWord?public=1";
    } else {
      this.href = window.location.origin + "/admin/exportHukumanDisiplinWord";
    }
  });

  $("#formEditHukuman").on("submit", function (e) {
    e.preventDefault();

    if (isSubmitting) {
      return false;
    }

    isSubmitting = true;

    var formData = new FormData(this);

    $.ajax({
      url: window.location.origin + "/admin/updateHukumanDisiplin",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        isSubmitting = false;

        if (response.success) {
          var modal = $("#modalEditHukuman");
          modal.css("display", "none");
          modal.removeClass("show");
          modal.removeAttr("aria-modal");
          modal.attr("aria-hidden", "true");
          $("body").removeClass("modal-open");

          $(".modal-backdrop").remove();

          Swal.fire("Berhasil", response.message, "success").then(() => {
            location.reload();
          });
        } else {
          Swal.fire(
            "Error",
            response.message || "Gagal mengupdate data",
            "error",
          );
        }
      },
      error: function (xhr, status, error) {
        isSubmitting = false;
        Swal.fire("Error", "Gagal mengupdate data", "error");
      },
    });
  });
});
