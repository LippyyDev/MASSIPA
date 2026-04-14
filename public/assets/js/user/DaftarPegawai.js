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

  $("#filterPegawaiModal").on("hidden.bs.modal", function (e) {
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

  $("#filterPegawaiModal").on("show.bs.modal", function (e) {
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

  var table = $("#pegawaiTable").DataTable({
    language: {
      emptyTable: "Tidak ada data yang tersedia",
      infoEmpty: "Tidak ada data yang tersedia",
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
    info: false,
    processing: true,
    serverSide: true,
    ajax: {
      url: "/user/getPegawaiAjax",
      type: "GET",
      data: function (d) {
        d.golongan =
          $("#filter_golongan").val() ||
          $("#filter_golongan_mobile").val() ||
          "";
        d.jabatan =
          $("#filter_jabatan").val() || $("#filter_jabatan_mobile").val() || "";
      },
    },
    pageLength: 10,
    order: [[0, "asc"]],
    autoWidth: false,
    scrollX: false,
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
    ],
    drawCallback: function (settings) {},
    createdRow: function (row, data, dataIndex) {
      var pageInfo = this.api().page.info();
      $("td:eq(0)", row).html(pageInfo.start + dataIndex + 1);
    },
  });

  table.on("init.dt", function () {
    var searchInput = $("#pegawaiTable_filter input");
    searchInput.attr("placeholder", "Cari Pegawai (Nama atau NIP)");
    searchInput.css({
      "min-width": "220px",
      "max-width": "320px",
      width: "auto",
      display: "inline-block",
    });
  });
  function syncFilterValues() {
    $("#filter_golongan").val(
      $("#filter_golongan_mobile").val() || $("#filter_golongan").val(),
    );
    $("#filter_jabatan").val(
      $("#filter_jabatan_mobile").val() || $("#filter_jabatan").val(),
    );

    $("#filter_golongan_mobile").val(
      $("#filter_golongan").val() || $("#filter_golongan_mobile").val(),
    );
    $("#filter_jabatan_mobile").val(
      $("#filter_jabatan").val() || $("#filter_jabatan_mobile").val(),
    );
  }

  $("#filter_golongan, #filter_jabatan").on("change", function () {
    var filterType = $(this).attr("id").replace("filter_", "");
    $("#filter_" + filterType + "_mobile").val($(this).val());
    table.ajax.reload();
    loadMobileCards();
  });

  $("#filter_golongan_mobile, #filter_jabatan_mobile").on(
    "change",
    function () {
      var filterType = $(this).data("filter-type");
      $("#filter_" + filterType).val($(this).val());
      table.ajax.reload();
      loadMobileCards();
    },
  );

  $("#filterPegawaiModal").on("show.bs.modal", function () {
    syncFilterValues();
  });
  var mobilePage = 1;
  var mobileLength = 10;
  function loadMobileCards() {
    var golongan =
      $("#filter_golongan").val() || $("#filter_golongan_mobile").val() || "";
    var jabatan =
      $("#filter_jabatan").val() || $("#filter_jabatan_mobile").val() || "";
    var search = $("#search_mobile_pegawai").val();
    var start = (mobilePage - 1) * mobileLength;
    $.ajax({
      url: "/user/getPegawaiAjax",
      type: "GET",
      data: {
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
                row.status === "aktif" ? "success" : "secondary"
              }\">${row.status === "aktif" ? "Aktif" : "Nonaktif"}</span>` +
              `</div>` +
              `<div><b>NIP:</b> ${row.nip}</div>` +
              `<div><b>Pangkat/Golongan:</b> ${row.pangkat} ${row.golongan}</div>` +
              `<div><b>Jabatan:</b> ${row.jabatan}</div>` +
              `</div>`;
          });
        }
        $("#mobileCards").html(html);
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
        $("#mobileCards").html(
          '<div class="text-center text-danger">Gagal memuat data pegawai.</div>',
        );
        $("#mobilePagination").hide();
      },
    });
  }
  var searchTimeout;
  $("#search_mobile_pegawai").on("input", function () {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(function () {
      mobilePage = 1;
      loadMobileCards();
    }, 300);
  });

  $("#filter_golongan, #filter_jabatan").on("change", function () {
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
