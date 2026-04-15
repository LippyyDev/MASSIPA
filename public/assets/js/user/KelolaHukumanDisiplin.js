window.SEARCH_PEGAWAI_URL =
  window.SEARCH_PEGAWAI_URL ||
  BASE_URL + "user/kelola_hukuman_disiplin/searchPegawaiAjax";

document.addEventListener("DOMContentLoaded", function () {
  var $collapse = $(".collapse-form");
  var $chevron = $("#chevronTambahHukuman");
  var opened = false;
  $("#toggleTambahHukuman").on("click", function () {
    if (opened) {
      $collapse.slideUp(200);
      $chevron.removeClass("bi-chevron-up").addClass("bi-chevron-down");
    } else {
      $collapse.slideDown(200);
      $chevron.removeClass("bi-chevron-down").addClass("bi-chevron-up");
    }
    opened = !opened;
  });
  const form = document.querySelector("form");
  if (form) {
    const inputs = form.querySelectorAll("input[required], select[required]");
    form.addEventListener("submit", function (e) {
      let valid = true;
      inputs.forEach((input) => {
        if (!input.value.trim()) {
          input.classList.add("is-invalid");
          valid = false;
        } else {
          input.classList.remove("is-invalid");
        }
      });
      if (!valid) e.preventDefault();
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
  function validateTanggalRange(formEl) {
    const mulai = formEl.querySelector('[name="tanggal_mulai"]');
    const berakhir = formEl.querySelector('[name="tanggal_berakhir"]');
    if (mulai && berakhir && mulai.value && berakhir.value) {
      if (berakhir.value < mulai.value) {
        Swal.fire({
          icon: "error",
          title: "Tanggal Tidak Valid!",
          text: "Tanggal Berakhir tidak boleh lebih kecil dari Tanggal Mulai.",
          confirmButtonColor: "#7c3aed",
          confirmButtonText: "Mengerti",
        });
        return false;
      }
    }
    return true;
  }

  const allForms = document.querySelectorAll("form");
  allForms.forEach(function (formEl) {
    formEl.addEventListener("submit", function (e) {
      if (!validateTanggalRange(formEl)) {
        e.preventDefault();
        e.stopPropagation();
      }
    });
  });
  window.updateJabatanSatker = function () {
    var select = document.getElementById("pegawai_id");
    var jabatan =
      select.options[select.selectedIndex].getAttribute("data-jabatan") || "";
    var satker =
      select.options[select.selectedIndex].getAttribute("data-satker") || "";
    document.getElementById("jabatan").value = jabatan;
    document.getElementById("satker").value = satker;
  };
  const searchInput = document.getElementById("pegawai_search");
  const resultsDiv = document.getElementById("pegawai_results");
  const hiddenInput = document.getElementById("pegawai_id");
  const jabatanInput = document.getElementById("jabatan");
  let searchTimeout;
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      clearTimeout(searchTimeout);
      const query = this.value.trim();
      if (query.length < 2) {
        resultsDiv.style.display = "none";
        return;
      }
      searchTimeout = setTimeout(() => {
        fetch(
          window.SEARCH_PEGAWAI_URL,
          {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: new URLSearchParams({ search: query, [window.CSRF_TOKEN_NAME]: window.CSRF_HASH })
          }
        )
          .then((res) => res.json())
          .then((data) => {
            if (data.success && data.data.length > 0) {
              let html = "";
              data.data.forEach((pegawai) => {
                html += `<div class="p-2 border-bottom pegawai-option" data-id="${
                  pegawai.id
                }" data-jabatan="${
                  pegawai.jabatan || ""
                }" style="cursor:pointer;">
                                    <strong>${
                                      pegawai.nama
                                    }</strong> <span class="pegawai-option-nip">(${
                                      pegawai.nip || "-"
                                    })</span><br><small class="text-muted">${
                                      pegawai.jabatan || "-"
                                    }</small>
                                </div>`;
              });
              resultsDiv.innerHTML = html;
              resultsDiv.style.display = "block";
              resultsDiv
                .querySelectorAll(".pegawai-option")
                .forEach((option) => {
                  option.addEventListener("click", function () {
                    const id = this.dataset.id;
                    const jabatan = this.dataset.jabatan;
                    const nama = this.querySelector("strong").textContent;
                    hiddenInput.value = id;
                    jabatanInput.value = jabatan;
                    searchInput.value = nama;
                    resultsDiv.style.display = "none";
                  });
                });
            } else {
              resultsDiv.innerHTML =
                '<div class="p-2 text-muted">Tidak ada pegawai ditemukan</div>';
              resultsDiv.style.display = "block";
            }
          });
      }, 400);
    });
    document.addEventListener("click", function (e) {
      if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
        resultsDiv.style.display = "none";
      }
    });
  }
});

$(document).ready(function () {
  if (window.innerWidth >= 768) {
    $("#user-hukuman-table").DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url:
          window.getHukumanDisiplinAjaxDataTablesUserUrl,
        type: "POST",
        dataSrc: "data",
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
              return `<a href='${window.BASE_URL}user/kelola_hukuman_disiplin/getFile/${encodeURIComponent(row.file_sk)}' target='_blank' style='color:inherit;text-decoration:none;'>${data}</a> <a href='${window.BASE_URL}user/kelola_hukuman_disiplin/getFile/${encodeURIComponent(row.file_sk)}' target='_blank'><i class='bi bi-file-earmark-pdf-fill text-danger'></i></a>`;
            } else {
              return data;
            }
          },
        },
        {
          data: null,
          render: function (data, type, row) {
            return `${moment(row.tanggal_mulai).format(
              "DD-MM-YYYY",
            )} s/d ${moment(row.tanggal_berakhir).format("DD-MM-YYYY")}`;
          },
        },
        { data: "hukuman_dijatuhkan" },
        { data: "peraturan_dilanggar" },
        { data: "keterangan" },
        {
          data: "status",
          render: function (data, type, row) {
            if (data == "pending")
              return '<span class="badge bg-warning text-dark">Pending</span>';
            if (data == "approved")
              return '<span class="badge bg-success">Disetujui</span>';
            if (data == "rejected")
              return '<span class="badge bg-danger">Ditolak</span>';
            return '<span class="badge bg-secondary">-</span>';
          },
        },
        {
          data: null,
          orderable: false,
          render: function (data, type, row) {
            let aksi = "";
            if (row.file_sk)
              aksi += `<a href='${window.BASE_URL}user/kelola_hukuman_disiplin/getFile/${encodeURIComponent(row.file_sk)}' target='_blank' class='btn btn-info btn-sm btn-action me-1'><i class='bi bi-file-earmark-pdf-fill'></i></a>`;
            if (row.status == "pending" || row.status == "rejected")
              aksi += `<form action='${window.BASE_URL}user/kelola_hukuman_disiplin/delete/${row.id}' method='post' style='display:inline;'><input type='hidden' name='${window.CSRF_TOKEN_NAME}' value='${window.CSRF_HASH}'><button type='submit' class='btn btn-danger btn-sm btn-action btn-delete'><i class='bi bi-trash'></i></button></form>`;
            return aksi;
          },
        },
      ],
      lengthMenu: [10, 25, 50],
      responsive: true,
      info: false,
      dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-12"p>>',
      language: {
        search: "Cari:",
        lengthMenu: "Tampil _MENU_ baris",
        paginate: { previous: "&lt;", next: "&gt;" },
      },
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
          "Status",
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
    loadMobileHukumanCardsUser();
  }

  var mobileHukumanUserPage = 1;
  var mobileHukumanUserPageSize = 10;
  var mobileHukumanUserData = [];
  var filteredMobileHukumanUserData = [];

  function loadMobileHukumanCardsUser() {
    $.ajax({
      url:
        window.getHukumanDisiplinAjaxDataTablesUserUrl,
      type: "POST",
      data: {
        length: 1000,
        start: 0,
      },
      success: function (response) {
        if (response.data) {
          mobileHukumanUserData = response.data;
          filteredMobileHukumanUserData = mobileHukumanUserData;
          mobileHukumanUserPage = 1;
          renderMobileHukumanCardsUser();
        }
      },
      error: function () {
        $("#mobileHukumanCardsUser").html(
          '<div class="text-center py-3">Gagal memuat data</div>',
        );
      },
    });
  }

  function renderMobileHukumanCardsUser() {
    var start = (mobileHukumanUserPage - 1) * mobileHukumanUserPageSize;
    var end = start + mobileHukumanUserPageSize;
    var pageData = filteredMobileHukumanUserData.slice(start, end);

    if (pageData.length === 0) {
      $("#mobileHukumanCardsUser").html(
        '<div class="text-center py-3">Tidak ada data</div>',
      );
      $("#mobilePaginationUser").hide();
      return;
    }

    window.generateMobileHukumanCardsUser(pageData, start);
    updateMobileHukumanUserPagination();

    if (typeof window.applyDarkModeToCardsUser === "function") {
      window.applyDarkModeToCardsUser();
    }
  }

  function updateMobileHukumanUserPagination() {
    var total = filteredMobileHukumanUserData.length;
    var totalPages = Math.ceil(total / mobileHukumanUserPageSize);

    if (totalPages <= 1) {
      $("#mobilePaginationUser").hide();
      return;
    }

    $("#mobilePaginationUser").show();

    var pageNumbersHtml = "";
    var startPage = Math.max(1, mobileHukumanUserPage - 1);
    var endPage = Math.min(totalPages, startPage + 2);

    if (endPage - startPage < 2) {
      startPage = Math.max(1, endPage - 2);
    }

    var maxPages = Math.min(3, totalPages);
    var actualStart = Math.max(1, mobileHukumanUserPage - 1);
    var actualEnd = Math.min(totalPages, actualStart + maxPages - 1);

    if (actualEnd - actualStart < maxPages - 1) {
      actualStart = Math.max(1, actualEnd - maxPages + 1);
    }

    for (var i = actualStart; i <= actualEnd; i++) {
      var activeClass = i === mobileHukumanUserPage ? "active" : "";
      pageNumbersHtml +=
        '<li class="page-item"><button class="page-link page-number ' +
        activeClass +
        '" data-page="' +
        i +
        '">' +
        i +
        "</button></li>";
    }

    $("#mobilePageNumbersUser").html(pageNumbersHtml);

    $("#mobilePrevUser").prop("disabled", mobileHukumanUserPage === 1);
    $("#mobileNextUser").prop("disabled", mobileHukumanUserPage >= totalPages);

    $(".page-number")
      .off("click")
      .on("click", function () {
        mobileHukumanUserPage = parseInt($(this).data("page"));
        renderMobileHukumanCardsUser();
      });
  }

  $(document).on("click", "#mobilePrevUser:not(:disabled)", function (e) {
    e.preventDefault();
    if (mobileHukumanUserPage > 1) {
      mobileHukumanUserPage--;
      renderMobileHukumanCardsUser();
    }
  });

  $(document).on("click", "#mobileNextUser:not(:disabled)", function (e) {
    e.preventDefault();
    var totalPages = Math.ceil(
      filteredMobileHukumanUserData.length / mobileHukumanUserPageSize,
    );
    if (mobileHukumanUserPage < totalPages) {
      mobileHukumanUserPage++;
      renderMobileHukumanCardsUser();
    }
  });

  window.generateMobileHukumanCardsUser = function (data, startIndex) {
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
        ? `<a href='${window.BASE_URL}user/kelola_hukuman_disiplin/getFile/${encodeURIComponent(row.file_sk)}' target='_blank' style='color:inherit;text-decoration:none;'>${row.no_sk}</a> <a href='${window.BASE_URL}user/kelola_hukuman_disiplin/getFile/${encodeURIComponent(row.file_sk)}' target='_blank'><i class='bi bi-file-earmark-pdf-fill text-danger'></i></a>`
        : row.no_sk;

      var statusBadge = "";
      if (row.status == "pending")
        statusBadge = '<span class="badge bg-warning text-dark">Pending</span>';
      else if (row.status == "approved")
        statusBadge = '<span class="badge bg-success">Disetujui</span>';
      else if (row.status == "rejected")
        statusBadge = '<span class="badge bg-danger">Ditolak</span>';
      else statusBadge = '<span class="badge bg-secondary">-</span>';

      cardsHtml += `
                <div class="border rounded p-3 mb-3 mobile-hukuman-card-user" style="${cardStyle}">
                    <div class="fw-bold mb-1 card-title d-flex justify-content-between align-items-center" style="${textStyle}">
                        <span>No. ${no} - ${row.nama}</span>
                        ${statusBadge}
                    </div>
                    <div class="small mb-2 card-content" style="${textStyle}">
                        <div class="card-row"><span class="card-label" style="font-weight: bold !important; ${textStyle} display: inline-block; min-width: 80px;">Jabatan:</span> <span class="card-value" style="${textStyle}">${
                          row.jabatan
                        }</span></div>
                        <div class="card-row"><span class="card-label" style="font-weight: bold !important; ${textStyle} display: inline-block; min-width: 80px;">No SK:</span> <span class="card-value" style="${textStyle}">${noSkHtml}</span></div>
                        <div class="card-row"><span class="card-label" style="font-weight: bold !important; ${textStyle} display: inline-block; min-width: 80px;">Periode:</span> <span class="card-value" style="${textStyle}">${periode}</span></div>
                        <div class="card-row"><span class="card-label" style="font-weight: bold !important; ${textStyle} display: inline-block; min-width: 80px;">Hukuman:</span> <span class="card-value" style="${textStyle}">${
                          row.hukuman_dijatuhkan
                        }</span></div>
                        <div class="card-row"><span class="card-label" style="font-weight: bold !important; ${textStyle} display: inline-block; min-width: 80px;">Peraturan:</span> <span class="card-value" style="${textStyle}">${
                          row.peraturan_dilanggar
                        }</span></div>
                        <div class="card-row"><span class="card-label" style="font-weight: bold !important; ${textStyle} display: inline-block; min-width: 80px;">Keterangan:</span> <span class="card-value" style="${textStyle}">${
                          row.keterangan || "-"
                        }</span></div>
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        ${
                          row.file_sk
                            ? `<a href='${window.BASE_URL}user/kelola_hukuman_disiplin/getFile/${encodeURIComponent(row.file_sk)}' target='_blank' class='btn btn-info btn-sm btn-action' title='Lihat File'><i class='bi bi-file-earmark-pdf-fill'></i></a>`
                            : ""
                        }
                        ${
                          row.status == "pending" || row.status == "rejected"
                            ? `<form action='${window.BASE_URL}user/kelola_hukuman_disiplin/delete/${row.id}' method='post' style='display:inline;'><input type='hidden' name='${window.CSRF_TOKEN_NAME}' value='${window.CSRF_HASH}'><button type='submit' class='btn btn-danger btn-sm btn-action btn-delete-user' title='Hapus'><i class='bi bi-trash'></i></button></form>`
                            : ""
                        }
                    </div>
                </div>
            `;
    });

    $("#mobileHukumanCardsUser").html(cardsHtml);
  };

  $("#search_mobile_hukuman_user").on("input", function () {
    var searchTerm = $(this).val().toLowerCase();
    filteredMobileHukumanUserData = mobileHukumanUserData.filter(
      function (row) {
        return (
          row.nama.toLowerCase().includes(searchTerm) ||
          (row.jabatan && row.jabatan.toLowerCase().includes(searchTerm)) ||
          (row.no_sk && row.no_sk.toLowerCase().includes(searchTerm))
        );
      },
    );
    mobileHukumanUserPage = 1;
    renderMobileHukumanCardsUser();
  });

  $(document).on("click", ".btn-delete, .btn-delete-user", function (e) {
    e.preventDefault();
    var form = $(this).closest("form");
    Swal.fire({
      title: "Yakin hapus data ini?",
      text: "Data yang dihapus tidak dapat dikembalikan!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Ya, Hapus",
      cancelButtonText: "Batal",
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });

  window.applyDarkModeToCardsUser = function () {
    var isDarkMode =
      $("body").attr("data-bs-theme") === "dark" ||
      $("html").attr("data-bs-theme") === "dark" ||
      $("body").hasClass("dark-mode") ||
      $("body").hasClass("dark");

    if (isDarkMode) {
      $(".mobile-hukuman-card-user").css({
        background: "#232336",
        "border-color": "#2a2a3f",
        color: "#fff",
      });

      $(".mobile-hukuman-card-user .card-title").css("color", "#fff");
      $(".mobile-hukuman-card-user .card-content").css("color", "#fff");
      $(".mobile-hukuman-card-user .card-label").css("color", "#fff");
      $(".mobile-hukuman-card-user .card-value").css("color", "#fff");
    } else {
      $(".mobile-hukuman-card-user").css({
        background: "#fff",
        "border-color": "#dee2e6",
        color: "#212529",
      });

      $(".mobile-hukuman-card-user .card-title").css("color", "#212529");
      $(".mobile-hukuman-card-user .card-content").css("color", "#212529");
      $(".mobile-hukuman-card-user .card-label").css("color", "#212529");
      $(".mobile-hukuman-card-user .card-value").css("color", "#212529");
    }
  };

  setTimeout(function () {
    if (typeof window.applyDarkModeToCardsUser === "function") {
      window.applyDarkModeToCardsUser();
    }
  }, 500);

  $(document).on("click", "[data-bs-theme-value]", function () {
    setTimeout(function () {
      if (typeof window.applyDarkModeToCardsUser === "function") {
        window.applyDarkModeToCardsUser();
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
          if (typeof window.applyDarkModeToCardsUser === "function") {
            window.applyDarkModeToCardsUser();
          }
        }, 100);
      }
    });
  });

  observer.observe(document.body, {
    attributes: true,
    attributeFilter: ["data-bs-theme"],
  });

  $("#exportPdfBtn").on("click", function (e) {
    var isPublic = $("#exportPublic").is(":checked");
    if (isPublic) {
      this.href =
        window.BASE_URL + "user/kelola_hukuman_disiplin/exportPdf?public=1";
    } else {
      this.href = window.BASE_URL + "user/kelola_hukuman_disiplin/exportPdf";
    }
  });

  $("#exportWordBtn").on("click", function (e) {
    var isPublic = $("#exportPublic").is(":checked");
    if (isPublic) {
      this.href =
        window.BASE_URL + "user/kelola_hukuman_disiplin/exportWord?public=1";
    } else {
      this.href = window.BASE_URL + "user/kelola_hukuman_disiplin/exportWord";
    }
  });
});
