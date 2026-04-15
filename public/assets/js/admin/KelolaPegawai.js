const golonganPangkat = {
  "I/a": "Juru Muda",
  "I/b": "Juru Muda Tk.I",
  "I/c": "Juru",
  "I/d": "Juru Tk.I",
  "II/a": "Pengatur Muda",
  "II/b": "Pengatur Muda Tk.I",
  "II/c": "Pengatur",
  "II/d": "Pengatur Tk.I",
  "III/a": "Penata Muda",
  "III/b": "Penata Muda Tingkat I",
  "III/c": "Penata",
  "III/d": "Penata Tingkat I",
  "IV/a": "Pembina",
  "IV/b": "Pembina Tingkat I",
  "IV/c": "Pembina Utama Muda",
  "IV/d": "Pembina Utama Madya",
  "IV/e": "Pembina Utama",
};

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

  $("#tambahPegawaiModal").on("hidden.bs.modal", function (e) {
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

  $("#tambahPegawaiModal").on("show.bs.modal", function (e) {
    var modal = $(this);
    modal.removeAttr("aria-hidden");
  });

  $("#editPegawaiModal").on("hidden.bs.modal", function (e) {
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

  $("#editPegawaiModal").on("show.bs.modal", function (e) {
    var modal = $(this);
    modal.removeAttr("aria-hidden");
  });

  $("#importPegawaiModal").on("hidden.bs.modal", function (e) {
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

  $("#importPegawaiModal").on("show.bs.modal", function (e) {
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

  $(document).on("keydown", function (e) {
    if (e.key === "Escape") {
      var modal = $(".modal.show");
      if (modal.length > 0) {
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
      }
    }
  });

  $("#loadingOverlay").show();
  var table = $("#pegawaiTable").DataTable({
    language: {
      emptyTable: "Tidak ada data yang tersedia",
      info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
      infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
      infoFiltered: "(disaring dari _MAX_ entri keseluruhan)",
      lengthMenu: "Tampilkan _MENU_ entri",
      loadingRecords: "Sedang memuat...",
      processing: "Sedang memproses...",
      search: "Cari:",
      zeroRecords: "Tidak ditemukan data yang sesuai",
      paginate: {
        first: "Pertama",
        last: "Terakhir",
        next: "&gt;",
        previous: "&lt;",
      },
      aria: {
        sortAscending: ": aktifkan untuk mengurutkan kolom naik",
        sortDescending: ": aktifkan untuk mengurutkan kolom turun",
      },
    },
    processing: true,
    serverSide: true,
    ajax: {
      url: window.inputPegawaiAjaxUrl,
      type: "POST",
      data: function (d) {
        d.satker = $("#filter_satker").val();
        d.golongan = $("#filter_golongan").val();
        d.jabatan = $("#filter_jabatan").val();
      },
    },
    pageLength: 10,
    order: [[0, "asc"]],
    scrollX: true,
    info: false,
    columns: [
      { data: null, orderable: false, searchable: false },
      { data: "nama" },
      { data: "nip" },
      {
        data: null,
        render: function (data, type, row) {
          return row.pangkat + " " + row.golongan;
        },
      },
      { data: "jabatan" },
      { data: "satker_nama" },
      {
        data: "status",
        render: function (data, type, row) {
          return (
            '<span class="badge bg-' +
            (data === "aktif" ? "success" : "secondary") +
            '">' +
            (data === "aktif" ? "Aktif" : "Nonaktif") +
            "</span>"
          );
        },
      },
      {
        data: null,
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          return `
                    <div class="btn-action-container">
                        <button type="button" class="btn btn-warning btn-sm aksi-btn btn-edit-pegawai" data-id="${
                          row.id
                        }" data-nama="${row.nama}" data-nip="${
                          row.nip
                        }" data-golongan="${row.golongan}" data-pangkat="${
                          row.pangkat
                        }" data-jabatan="${row.jabatan}" data-satker="${
                          row.satker_nama || ""
                        }" data-tanggal="${row.tanggal_mulai || ""}" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <a href="${window.mutasiPegawaiUrl}${
                          row.id
                        }" class="btn btn-info btn-sm aksi-btn" title="Mutasi">
                            <i class="fas fa-random"></i>
                        </a>
                        <a href="${window.toggleStatusPegawaiUrl}${
                          row.id
                        }" class="btn btn-${
                          row.status === "aktif"
                            ? "outline-danger"
                            : "outline-primary"
                        } btn-sm aksi-btn btn-toggle-status" data-nama="${row.nama}" title="${
                          row.status === "aktif" ? "Nonaktifkan" : "Aktifkan"
                        }">
                            <i class="bi ${
                              row.status === "aktif"
                                ? "bi-x-circle"
                                : "bi-check-circle"
                            }"></i>
                        </a>
                        <a href="${window.deletePegawaiUrl}${
                          row.id
                        }" class="btn btn-danger btn-sm aksi-btn btn-delete-pegawai" data-nama="${
                          row.nama
                        }" title="Hapus">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                `;
        },
      },
    ],
    drawCallback: function (settings) {
      $("#loadingOverlay").hide();

      if (window.innerWidth <= 767) {
        var $pagination = $(".dataTables_paginate .pagination");
        var $pageItems = $pagination.find(
          ".page-item:not(.previous):not(.next)",
        );
        var $active = $pagination.find(".page-item.active");
        var activeIndex = $pageItems.index($active);

        $pageItems.each(function (index) {
          var $item = $(this);
          var distance = Math.abs(index - activeIndex);
          if (distance <= 1) {
            $item.show();
          } else {
            $item.hide();
          }
        });
      }
    },
    createdRow: function (row, data, dataIndex) {
      var pageInfo = this.api().page.info();
      $("td:eq(0)", row).html(pageInfo.start + dataIndex + 1);
    },
  });

  table.on("init.dt", function () {
    var searchInput = $("#pegawaiTable_filter input");
    searchInput.attr("placeholder", "Cari Pegawai (Nama atau NIP)");
  });

  function getFilterValue(filterType) {
    var desktopVal = $("#filter_" + filterType).val();
    var mobileVal = $("#filter_" + filterType + "_mobile").val();
    return desktopVal || mobileVal || "";
  }

  $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    var satker = getFilterValue("satker");
    var golongan = getFilterValue("golongan");
    var jabatan = getFilterValue("jabatan");
    var rowSatker = data[5];
    var rowGolongan = data[3].trim();
    var rowGolonganOnly = rowGolongan.split(" ").pop();
    var rowJabatan = data[4];
    if (satker && rowSatker !== satker) {
      return false;
    }
    if (golongan && rowGolonganOnly !== golongan) {
      return false;
    }
    if (jabatan && rowJabatan !== jabatan) {
      return false;
    }
    return true;
  });

  function syncFilterValues() {
    $("#filter_satker").val(
      $("#filter_satker_mobile").val() || $("#filter_satker").val(),
    );
    $("#filter_golongan").val(
      $("#filter_golongan_mobile").val() || $("#filter_golongan").val(),
    );
    $("#filter_jabatan").val(
      $("#filter_jabatan_mobile").val() || $("#filter_jabatan").val(),
    );

    $("#filter_satker_mobile").val(
      $("#filter_satker").val() || $("#filter_satker_mobile").val(),
    );
    $("#filter_golongan_mobile").val(
      $("#filter_golongan").val() || $("#filter_golongan_mobile").val(),
    );
    $("#filter_jabatan_mobile").val(
      $("#filter_jabatan").val() || $("#filter_jabatan_mobile").val(),
    );
  }

  $("#filter_satker, #filter_golongan, #filter_jabatan").on(
    "change",
    function () {
      var filterType = $(this).attr("id").replace("filter_", "");
      $("#filter_" + filterType + "_mobile").val($(this).val());
      table.ajax.reload();
      if (typeof loadMobileCards === "function") {
        loadMobileCards();
      }
    },
  );

  $(
    "#filter_satker_mobile, #filter_golongan_mobile, #filter_jabatan_mobile",
  ).on("change", function () {
    var filterType = $(this).data("filter-type");
    $("#filter_" + filterType).val($(this).val());
    table.ajax.reload();
    if (typeof loadMobileCards === "function") {
      loadMobileCards();
    }
  });

  $("#filterPegawaiModal").on("show.bs.modal", function () {
    syncFilterValues();
  });
  $("#golongan_tambah").on("change", function () {
    const golongan = $(this).val();
    $("#pangkat_tambah").val(golonganPangkat[golongan] || "");
  });
  $(document).on("click", ".btn-edit-pegawai", function () {
    const id = $(this).data("id");
    const nama = $(this).data("nama");
    const nip = $(this).data("nip");
    const golongan = $(this).data("golongan");
    const pangkat = $(this).data("pangkat");
    const jabatan = $(this).data("jabatan");
    const satker = $(this).data("satker");
    let tanggal = $(this).data("tanggal");
    if (tanggal && tanggal.length > 10) {
      tanggal = tanggal.substring(0, 10);
    }
    if (!tanggal || tanggal === "null" || tanggal === "0000-00-00") {
      tanggal = "";
    }
    $("#editPegawaiForm").attr("action", window.updatePegawaiUrl + id);
    $("#edit_pegawai_id").val(id);
    $("#edit_nama").val(nama);
    $("#edit_nip").val(nip);
    $("#edit_golongan").val(golongan);
    $("#edit_pangkat").val(pangkat);
    $("#edit_jabatan").val(jabatan);
    $("#edit_tanggal_mulai").val(tanggal);
    $("#edit_satker_id option").each(function () {
      if ($(this).text().trim() === satker) {
        $(this).prop("selected", true);
      } else {
        $(this).prop("selected", false);
      }
    });
    $("#editPegawaiModal").modal("show");
  });
  $("#edit_golongan").on("change", function () {
    const golongan = $(this).val();
    $("#edit_pangkat").val(golonganPangkat[golongan] || "");
  });
  $(document).on("click", ".btn-delete-pegawai", function (e) {
    e.preventDefault();
    var url = $(this).attr("href");
    var nama = $(this).data("nama");
    Swal.fire({
      title: "Yakin hapus pegawai?",
      text: "Data pegawai " + nama + " akan dihapus permanen!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Ya, hapus!",
      cancelButtonText: "Batal",
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = url;
      }
    });
  });
  $(document).on("click", ".btn-toggle-status", function (e) {
    e.preventDefault();
    var url = $(this).attr("href");
    var nama = $(this).data("nama");
    var isAktif = $(this).find("i").hasClass("bi-x-circle");
    Swal.fire({
      title: "Ubah status pegawai?",
      text: isAktif
        ? "Nonaktifkan pegawai " + nama + "?"
        : "Aktifkan pegawai " + nama + "?",
      icon: "question",
      showCancelButton: true,
      confirmButtonColor: isAktif ? "#d33" : "#7c3aed",
      cancelButtonColor: "#6b7280",
      confirmButtonText: isAktif ? "Ya, Nonaktifkan!" : "Ya, Aktifkan!",
      cancelButtonText: "Batal",
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = url;
      }
    });
  });
  var mobilePage = 1;
  var mobileLength = 10;
  function loadMobileCards() {
    var satker =
      $("#filter_satker").val() || $("#filter_satker_mobile").val() || "";
    var golongan =
      $("#filter_golongan").val() || $("#filter_golongan_mobile").val() || "";
    var jabatan =
      $("#filter_jabatan").val() || $("#filter_jabatan_mobile").val() || "";
    var search = $("#search_mobile_pegawai").val();
    var start = (mobilePage - 1) * mobileLength;
    $.ajax({
      url: window.inputPegawaiAjaxUrl,
      type: "POST",
      data: {
        satker: satker,
        golongan: golongan,
        jabatan: jabatan,
        start: start,
        length: mobileLength,
        "search[value]": search,
      },
      success: function (response) {
        var data = response.data ? response.data : response;
        var html = "";
        if (data.length === 0) {
          html =
            '<div class="text-center text-muted">Tidak ada data pegawai.</div>';
        } else {
          $.each(data, function (i, row) {
            html +=
              `<div class=\"border rounded mb-3 p-3 shadow-sm\" style=\"background:#fff;\">` +
              `<div class=\"fw-bold mb-1 d-flex justify-content-between align-items-center\">` +
              `<span>No. ${start + i + 1} - ${row.nama}</span>` +
              `<span class=\"badge bg-${
                row.status == "aktif" ? "success" : "secondary"
              }\">${row.status == "aktif" ? "Aktif" : "Nonaktif"}</span>` +
              `</div>` +
              `<div><b>NIP:</b> ${row.nip}</div>` +
              `<div><b>Pangkat/Golongan:</b> ${row.pangkat} ${row.golongan}</div>` +
              `<div><b>Jabatan:</b> ${row.jabatan}</div>` +
              `<div><b>Satker Aktif:</b> ${row.satker_nama || "-"}</div>` +
              `<div class=\"mt-2 d-flex gap-2 flex-wrap\">` +
              `<button type=\"button\" class=\"btn btn-warning btn-sm aksi-btn btn-edit-pegawai\" data-id=\"${
                row.id
              }\" data-nama=\"${row.nama}\" data-nip=\"${
                row.nip
              }\" data-golongan=\"${row.golongan}\" data-pangkat=\"${
                row.pangkat
              }\" data-jabatan=\"${row.jabatan}\" data-satker=\"${
                row.satker_nama || ""
              }\" data-tanggal=\"${
                row.tanggal_mulai || ""
              }\" title=\"Edit\"><i class=\"fas fa-edit\"></i></button>` +
              `<a href=\"${window.mutasiPegawaiUrl}${row.id}\" class=\"btn btn-info btn-sm aksi-btn\" title=\"Mutasi\"><i class=\"fas fa-random\"></i></a>` +
              `<a href=\"${window.toggleStatusPegawaiUrl}${
                row.id
              }\" class=\"btn btn-${
                row.status == "aktif" ? "outline-danger" : "outline-primary"
              } btn-sm aksi-btn btn-toggle-status\" title=\"${
                row.status == "aktif" ? "Nonaktifkan" : "Aktifkan"
              }\"><i class=\"bi ${
                row.status == "aktif" ? "bi-x-circle" : "bi-check-circle"
              }\"></i></a>` +
              `<a href=\"${window.deletePegawaiUrl}${row.id}\" class=\"btn btn-danger btn-sm aksi-btn btn-delete-pegawai\" data-nama=\"${row.nama}\" title=\"Hapus\"><i class=\"fas fa-trash\"></i></a>` +
              `</div>` +
              `</div>`;
          });
        }
        $("#mobilePegawaiCards").html(html);
        var total = response.recordsFiltered || 0;
        var totalPages = Math.ceil(total / mobileLength);

        if (data.length === 0 || totalPages <= 1) {
          $("#mobilePagination").hide();
          $("#mobilePageNumbers").empty();
          $("#mobilePrev").prop("disabled", true);
          $("#mobileNext").prop("disabled", true);
          return;
        }

        $("#mobilePagination").show();

        var pageNumbersHtml = "";
        var startPage = Math.max(1, mobilePage - 1);
        var endPage = Math.min(totalPages, startPage + 2);

        if (endPage - startPage < 2) {
          startPage = Math.max(1, endPage - 2);
        }

        for (var i = startPage; i <= endPage; i++) {
          var activeClass = i === mobilePage ? "active" : "";
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
        $("#mobilePrev").prop("disabled", mobilePage === 1);
        $("#mobileNext").prop("disabled", mobilePage === totalPages);

        $(".page-number")
          .off("click")
          .on("click", function () {
            mobilePage = parseInt($(this).data("page"));
            loadMobileCards();
          });
      },
      error: function () {
        $("#mobilePegawaiCards").html(
          '<div class="text-center text-danger">Gagal memuat data pegawai.</div>',
        );
        $("#mobilePagination").hide();
      },
    });
  }
  $(
    "#filter_satker, #filter_golongan, #filter_jabatan, #search_mobile_pegawai",
  ).on("input change", function () {
    mobilePage = 1;
    loadMobileCards();
  });
  $("#mobilePrev").on("click", function () {
    if (mobilePage > 1) {
      mobilePage--;
      loadMobileCards();
    }
  });
  $("#mobileNext").on("click", function () {
    mobilePage++;
    loadMobileCards();
  });
  loadMobileCards();
});
