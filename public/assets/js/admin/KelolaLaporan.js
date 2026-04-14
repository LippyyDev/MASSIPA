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

  var autoRefreshInterval = null;
  var autoRefreshEnabled = true;

  function startAutoRefresh() {
    if (autoRefreshInterval) {
      clearInterval(autoRefreshInterval);
    }

    autoRefreshInterval = setInterval(function () {
      if (autoRefreshEnabled && !document.hidden) {
        if (window.innerWidth > 767 && typeof table !== "undefined") {
          table.ajax.reload(null, false);
        }
        if (window.innerWidth <= 767) {
          loadMobileLaporanCards();
        }
      }
    }, 10000);
  }

  document.addEventListener("visibilitychange", function () {
    if (document.hidden) {
      autoRefreshEnabled = false;
    } else {
      autoRefreshEnabled = true;
      if (window.innerWidth > 767 && typeof table !== "undefined") {
        table.ajax.reload(null, false);
      } else if (window.innerWidth <= 767) {
        loadMobileLaporanCards();
      }
    }
  });

  setTimeout(function () {
    startAutoRefresh();
  }, 2000);

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

  var table = $("#laporanTable").DataTable({
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
        first: "",
        last: "",
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
      url: window.laporanAjaxUrl,
      type: "GET",
      data: function (d) {
        d.user_id =
          $("#filter_user").val() || $("#filter_user_mobile").val() || "";
        d.bulan =
          $("#filter_bulan").val() || $("#filter_bulan_mobile").val() || "";
        d.tahun =
          $("#filter_tahun").val() || $("#filter_tahun_mobile").val() || "";
        d.kategori =
          $("#filter_kategori").val() ||
          $("#filter_kategori_mobile").val() ||
          "";
      },
    },
    pageLength: 10,
    order: [[6, "desc"]],
    scrollX: true,
    info: false,
    columns: [
      { data: null, orderable: false, searchable: false },
      { data: 1 },
      { data: 2 },
      { data: 3, orderable: false },
      { data: 4 },
      { data: 5 },
      { data: 6 },
      { data: 7, orderable: false },
      { data: 8 },
      { data: 9, orderable: false, searchable: false },
    ],
    drawCallback: function (settings) {
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
    var searchInput = $("#laporanTable_filter input");
    searchInput.attr("placeholder", "Cari Laporan");
  });

  function syncFilterValues() {
    $("#filter_user").val(
      $("#filter_user_mobile").val() || $("#filter_user").val(),
    );
    $("#filter_bulan").val(
      $("#filter_bulan_mobile").val() || $("#filter_bulan").val(),
    );
    $("#filter_tahun").val(
      $("#filter_tahun_mobile").val() || $("#filter_tahun").val(),
    );
    $("#filter_kategori").val(
      $("#filter_kategori_mobile").val() || $("#filter_kategori").val(),
    );

    $("#filter_user_mobile").val(
      $("#filter_user").val() || $("#filter_user_mobile").val(),
    );
    $("#filter_bulan_mobile").val(
      $("#filter_bulan").val() || $("#filter_bulan_mobile").val(),
    );
    $("#filter_tahun_mobile").val(
      $("#filter_tahun").val() || $("#filter_tahun_mobile").val(),
    );
    $("#filter_kategori_mobile").val(
      $("#filter_kategori").val() || $("#filter_kategori_mobile").val(),
    );
  }

  $("#filter_user, #filter_bulan, #filter_tahun, #filter_kategori").on(
    "change",
    function () {
      var filterType = $(this).attr("id").replace("filter_", "");
      $("#filter_" + filterType + "_mobile").val($(this).val());
      if (window.innerWidth > 767) {
        table.ajax.reload();
      } else {
        mobileLaporanPage = 1;
        loadMobileLaporanCards();
      }
    },
  );

  $(
    "#filter_user_mobile, #filter_bulan_mobile, #filter_tahun_mobile, #filter_kategori_mobile",
  ).on("change", function () {
    var filterType = $(this)
      .attr("id")
      .replace("filter_", "")
      .replace("_mobile", "");
    $("#filter_" + filterType).val($(this).val());
    if (window.innerWidth > 767) {
      table.ajax.reload();
    } else {
      mobileLaporanPage = 1;
      loadMobileLaporanCards();
    }
  });

  $("#filterLaporanModal").on("show.bs.modal", function () {
    syncFilterValues();
  });

  $("#applyFilterMobile").on("click", function () {
    syncFilterValues();
    table.ajax.reload();
    $("#filterLaporanModal").modal("hide");
  });

  $(document).on("click", ".btn-delete-laporan", function () {
    var id = $(this).data("id");
    var nama = $(this).data("nama");
    deleteLaporan(id);
  });

  $(document).on("click", ".btn-approve-laporan", function () {
    var id = $(this).data("id");
    $("#approveLaporanId").val(id);
    $("#approveFeedback").val("");
  });

  $(document).on("click", ".btn-reject-laporan", function () {
    var id = $(this).data("id");
    $("#rejectLaporanId").val(id);
    $("#rejectFeedback").val("");
  });

  var mobileLaporanPage = 1;
  var mobileLaporanLength = 10;

  function loadMobileLaporanCards() {
    var user_id =
      $("#filter_user").val() || $("#filter_user_mobile").val() || "";
    var bulan =
      $("#filter_bulan").val() || $("#filter_bulan_mobile").val() || "";
    var tahun =
      $("#filter_tahun").val() || $("#filter_tahun_mobile").val() || "";
    var kategori =
      $("#filter_kategori").val() || $("#filter_kategori_mobile").val() || "";
    var search = "";
    var start = (mobileLaporanPage - 1) * mobileLaporanLength;

    $.ajax({
      url: window.laporanAjaxUrl,
      type: "GET",
      data: {
        user_id: user_id,
        bulan: bulan,
        tahun: tahun,
        kategori: kategori,
        start: start,
        length: mobileLaporanLength,
        "search[value]": search,
        draw: 1,
      },
      success: function (response) {
        var rawData = response.rawData || [];
        var html = "";

        if (rawData.length === 0) {
          html =
            '<div class="text-center text-muted py-3">Tidak ada laporan ditemukan.</div>';
        } else {
          $.each(rawData, function (i, row) {
            var no = start + i + 1;

            var status_class = "";
            var status_display = row.status || "-";
            switch (row.status) {
              case "terkirim":
                status_class = "bg-warning";
                status_display = "Pending";
                break;
              case "dilihat":
                status_class = "bg-primary";
                status_display = "Dilihat";
                break;
              case "diterima":
                status_class = "bg-success";
                status_display = "Diterima";
                break;
              case "ditolak":
                status_class = "bg-danger";
                status_display = "Ditolak";
                break;
              default:
                status_class = "bg-secondary";
                break;
            }

            var kategori_badge = "";
            if (row.kategori) {
              if (row.kategori == "Laporan Apel") {
                kategori_badge =
                  '<span class="badge bg-danger">' + row.kategori + "</span>";
              } else {
                kategori_badge =
                  '<span class="badge bg-primary">' + row.kategori + "</span>";
              }
            } else {
              kategori_badge =
                '<span class="badge bg-secondary">Laporan Disiplin</span>';
            }

            var actions = "";
            if (row.file_path) {
              actions +=
                '<a href="' +
                window.location.origin +
                "/admin/kelola_laporan/view/" +
                row.id +
                '" target="_blank" class="btn btn-info btn-sm btn-action" title="Lihat File"><i class="fas fa-eye"></i></a> ';
            }
            if (row.link_drive) {
              actions +=
                '<a href="' +
                window.location.origin +
                "/admin/kelola_laporan/link/" +
                row.id +
                '" target="_blank" class="btn btn-success btn-sm btn-action" title="Link Drive"><i class="fas fa-link"></i></a> ';
            }
            if (row.status != "diterima" && row.status != "ditolak") {
              actions +=
                '<button type="button" class="btn btn-success btn-sm btn-action btn-approve-laporan" data-id="' +
                row.id +
                '" data-bs-toggle="modal" data-bs-target="#approveModal"><i class="fas fa-check"></i> Approve</button> ';
              actions +=
                '<button type="button" class="btn btn-danger btn-sm btn-action btn-reject-laporan" data-id="' +
                row.id +
                '" data-bs-toggle="modal" data-bs-target="#rejectModal"><i class="fas fa-times"></i> Reject</button> ';
            }
            actions +=
              '<button type="button" class="btn btn-danger btn-sm btn-action btn-delete-laporan" data-id="' +
              row.id +
              '" data-nama="' +
              (row.nama_laporan || "") +
              '"><i class="fas fa-trash"></i> Hapus</button>';

            var bulan_tahun = "-";
            if (row.bulan && row.tahun) {
              var bulanNama = [
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
              bulan_tahun = bulanNama[parseInt(row.bulan)] + " " + row.tahun;
            }
            var tanggal_upload = row.created_at
              ? new Date(row.created_at).toLocaleString("id-ID", {
                  day: "2-digit",
                  month: "2-digit",
                  year: "numeric",
                  hour: "2-digit",
                  minute: "2-digit",
                })
              : "-";

            html +=
              '<div class="border rounded mb-3 p-3 shadow-sm" style="background:#fff;">' +
              '<div class="fw-bold mb-1 d-flex justify-content-between align-items-center">' +
              "<span>No. " +
              no +
              " - " +
              (row.nama_laporan || "-") +
              "</span>" +
              '<span class="badge ' +
              status_class +
              '">' +
              status_display +
              "</span>" +
              "</div>" +
              "<div><b>Bulan/Tahun:</b> " +
              bulan_tahun +
              "</div>" +
              "<div><b>Kategori:</b> " +
              kategori_badge +
              "</div>" +
              "<div><b>Pengirim:</b> " +
              (row.nama_lengkap || "-") +
              "</div>" +
              "<div><b>Keterangan:</b> " +
              (row.keterangan || "-") +
              "</div>" +
              "<div><b>Tanggal Upload:</b> " +
              tanggal_upload +
              "</div>" +
              "<div><b>Feedback:</b> " +
              (row.feedback || "-") +
              "</div>" +
              '<div class="mt-2 d-flex gap-2 flex-wrap">' +
              actions +
              "</div>" +
              "</div>";
          });
        }

        $("#mobileLaporanCards").html(html);

        if (rawData.length === 0) {
          $("#mobileLaporanPagination").hide();
          $("#mobileLaporanPageNumbers").empty();
          $("#mobileLaporanPrev").prop("disabled", true);
          $("#mobileLaporanNext").prop("disabled", true);
          return;
        }

        var total = response.recordsFiltered || 0;
        window.mobileLaporanTotal = total;
        var totalPages = Math.ceil(total / mobileLaporanLength);

        if (total > mobileLaporanLength) {
          $("#mobileLaporanPagination").show();

          var pageNumbersHtml = "";
          var startPage = Math.max(1, mobileLaporanPage - 1);
          var endPage = Math.min(totalPages, startPage + 2);

          if (endPage - startPage < 2) {
            startPage = Math.max(1, endPage - 2);
          }

          var maxPages = Math.min(3, totalPages);
          var actualStart = Math.max(1, mobileLaporanPage - 1);
          var actualEnd = Math.min(totalPages, actualStart + maxPages - 1);

          if (actualEnd - actualStart < maxPages - 1) {
            actualStart = Math.max(1, actualEnd - maxPages + 1);
          }

          for (var i = actualStart; i <= actualEnd; i++) {
            var activeClass = i === mobileLaporanPage ? "active" : "";
            pageNumbersHtml +=
              '<li class="page-item"><button class="page-link page-number ' +
              activeClass +
              '" data-page="' +
              i +
              '">' +
              i +
              "</button></li>";
          }

          $("#mobileLaporanPageNumbers").html(pageNumbersHtml);

          if (mobileLaporanPage === 1) {
            $("#mobileLaporanPrev").prop("disabled", true).addClass("disabled");
          } else {
            $("#mobileLaporanPrev")
              .prop("disabled", false)
              .removeClass("disabled");
          }

          if (mobileLaporanPage === totalPages) {
            $("#mobileLaporanNext").prop("disabled", true).addClass("disabled");
          } else {
            $("#mobileLaporanNext")
              .prop("disabled", false)
              .removeClass("disabled");
          }

          $(document)
            .off("click", ".page-number")
            .on("click", ".page-number", function (e) {
              e.preventDefault();
              mobileLaporanPage = parseInt($(this).data("page"));
              loadMobileLaporanCards();
            });
        } else {
          $("#mobileLaporanPagination").hide();
        }
      },
      error: function () {
        $("#mobileLaporanCards").html(
          '<div class="text-center text-danger py-3">Gagal memuat data laporan.</div>',
        );
        $("#mobileLaporanPagination").hide();
      },
    });
  }

  if (window.innerWidth <= 767) {
    loadMobileLaporanCards();
  }

  $(document).on("click", "#mobileLaporanPrev:not(:disabled)", function (e) {
    e.preventDefault();
    if (mobileLaporanPage > 1) {
      mobileLaporanPage--;
      loadMobileLaporanCards();
    }
  });

  $(document).on("click", "#mobileLaporanNext:not(:disabled)", function (e) {
    e.preventDefault();
    var total = window.mobileLaporanTotal || 0;
    var totalPages = Math.ceil(total / mobileLaporanLength);
    if (mobileLaporanPage < totalPages) {
      mobileLaporanPage++;
      loadMobileLaporanCards();
    }
  });

  $(window).on("resize", function () {
    if (window.innerWidth <= 767) {
      if ($("#mobileLaporanCards").html().trim() === "") {
        loadMobileLaporanCards();
      }
    }
  });
});

function deleteLaporan(id) {
  Swal.fire({
    title: "Yakin hapus laporan?",
    text: "Laporan akan dihapus secara permanen.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#ef4444",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Ya, hapus!",
    cancelButtonText: "Batal",
  }).then((result) => {
    if (result.isConfirmed) {
      var form = $("<form>", {
        action: window.location.origin + "/admin/kelola_laporan/delete",
        method: "POST",
      });
      form.append(
        $("<input>", {
          type: "hidden",
          name: window.csrfTokenName,
          value: window.csrfHash,
        }),
      );
      form.append(
        $("<input>", {
          type: "hidden",
          name: "laporan_id",
          value: id,
        }),
      );
      $("body").append(form);
      form.submit();
    }
  });
}

function viewLaporan(id) {
  window.open(
    window.location.origin + "/admin/kelola_laporan/view/" + id,
    "_blank",
  );
}
