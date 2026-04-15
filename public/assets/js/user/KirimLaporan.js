$(document).ready(function () {
  $("#files").on("change", function () {
    var file = this.files[0];
    var maxSize = 1 * 1024 * 1024; // 1MB

    if (file) {
      if (file.type !== "application/pdf") {
        Swal.fire({
          icon: "error",
          title: "Format File Tidak Didukung",
          text: "Hanya file PDF yang diperbolehkan.",
          confirmButtonText: "OK",
        });
        this.value = "";
        return;
      }

      if (file.size > maxSize) {
        Swal.fire({
          icon: "error",
          title: "Ukuran File Terlalu Besar",
          text: "Ukuran file maksimal 1MB.",
          confirmButtonText: "OK",
        });
        this.value = "";
        return;
      }

      if (this.files.length > 1) {
        Swal.fire({
          icon: "error",
          title: "Jumlah File Terlalu Banyak",
          text: "Hanya 1 file yang diperbolehkan.",
          confirmButtonText: "OK",
        });
        this.value = "";
        return;
      }
    }
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
    order: [[7, "desc"]],
    columns: [
      { data: null, orderable: false },
      { data: 1 },
      { data: 2 },
      { data: 3, orderable: false },
      { data: 4 },
      { data: 5, orderable: false },
      { data: 6 },
      { data: 7 },
      { data: 8, orderable: false },
    ],
    createdRow: function (row, data, dataIndex) {
      var api = this.api();
      var pageInfo = api.page.info();
      var rowNum = pageInfo.start + dataIndex + 1;
      $("td:eq(0)", row).html(rowNum);
    },
  });

  var mobileLaporanPage = 1;
  var mobileLaporanLength = 10;

  function loadMobileLaporanCards() {
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
                '<span class="badge bg-primary">Laporan Disiplin</span>';
            }

            var actions = "";
            if (row.file_path) {
              actions +=
                '<a href="' +
                window.location.origin +
                "/user/getFile/" +
                encodeURIComponent(row.file_path) +
                '" target="_blank" class="btn btn-info btn-sm btn-action" title="Lihat File"><i class="fas fa-eye"></i></a> ';
            }
            if (row.link_drive) {
              actions +=
                '<a href="' +
                row.link_drive +
                '" target="_blank" class="btn btn-success btn-sm btn-action" title="Link Drive"><i class="fas fa-link"></i></a> ';
            }

            var hapusAction = "";
            var hapusType = "";
            if (row.status === "diterima" || row.status === "disetujui") {
              hapusAction = window.location.origin + "/user/kirimlaporan/hide";
              hapusType = "hide";
            } else {
              hapusAction =
                window.location.origin + "/user/kirimlaporan/delete";
              hapusType = "delete";
            }

            actions +=
              '<form class="form-hapus-laporan d-inline" action="' +
              hapusAction +
              '" method="POST">';
            var csrfName = $('input[name^="csrf_"]').first().attr('name');
            var csrfVal  = $('input[name^="csrf_"]').first().val();
            if (csrfName && csrfVal) {
              actions += '<input type="hidden" name="' + csrfName + '" value="' + csrfVal + '">';
            }
            actions +=
              '<input type="hidden" name="laporan_id_to_' +
              hapusType +
              '" value="' +
              row.id +
              '">';
            actions +=
              '<button type="button" class="btn btn-danger btn-sm btn-action btn-hapus-laporan" data-hapustype="' +
              hapusType +
              '" data-nama="' +
              (row.nama_laporan || "") +
              '"><i class="fas fa-trash"></i> Hapus</button>';
            actions += "</form>";

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

        if (totalPages > 1) {
          $("#mobileLaporanPagination").show();

          var pageNumbersHtml = "";
          var startPage = Math.max(1, mobileLaporanPage - 1);
          var endPage = Math.min(totalPages, mobileLaporanPage + 1);

          if (endPage - startPage < 2) {
            if (startPage === 1) {
              endPage = Math.min(totalPages, startPage + 2);
            } else if (endPage === totalPages) {
              startPage = Math.max(1, endPage - 2);
            }
          }

          for (var i = startPage; i <= endPage; i++) {
            var activeClass = i === mobileLaporanPage ? "active" : "";
            pageNumbersHtml +=
              '<li class="page-item ' +
              activeClass +
              '">' +
              '<button class="page-link page-number" data-page="' +
              i +
              '" type="button">' +
              i +
              "</button>" +
              "</li>";
          }

          $("#mobileLaporanPageNumbers").html(pageNumbersHtml);

          $("#mobileLaporanPrev").prop("disabled", mobileLaporanPage === 1);
          $("#mobileLaporanNext").prop(
            "disabled",
            mobileLaporanPage === totalPages,
          );
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

  $("#filter_bulan, #filter_tahun, #filter_kategori").on("change", function () {
    if (window.innerWidth > 767) {
      table.ajax.reload();
    } else {
      mobileLaporanPage = 1;
      loadMobileLaporanCards();
    }
  });

  $("#filter_bulan_mobile, #filter_tahun_mobile, #filter_kategori_mobile").on(
    "change",
    function () {
      if (window.innerWidth <= 767) {
        mobileLaporanPage = 1;
        loadMobileLaporanCards();
      }
    },
  );

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

  $(document).on("click", ".page-number", function (e) {
    e.preventDefault();
    var page = $(this).data("page");
    if (page) {
      mobileLaporanPage = page;
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

  $("#reuploadModal").on("show.bs.modal", function (event) {
    var button = $(event.relatedTarget);
    var id = button.data("id");
    var nama = button.data("nama");
    var bulan = button.data("bulan");
    var tahun = button.data("tahun");
    var keterangan = button.data("keterangan");

    var modal = $(this);
    modal.find("#reupload_laporan_id").val(id);
    modal.find("#reupload_nama_laporan").val(nama);
    modal.find("#reupload_bulan").val(bulan);
    modal.find("#reupload_tahun").val(tahun);
    modal.find("#reupload_keterangan").val(keterangan);
  });

  $("#deleteModal").on("show.bs.modal", function (event) {
    var button = $(event.relatedTarget);
    var id = button.data("id");
    var modal = $(this);
    modal.find("#delete_laporan_id").val(id);
  });

  $(document).on("click", ".btn-hapus-laporan", function (e) {
    e.preventDefault();
    var btn = $(this);
    var form = btn.closest("form");
    var hapusType = btn.data("hapustype");
    var nama = btn.data("nama");
    var msg = "";
    if (hapusType === "hide") {
      msg =
        "Laporan yang sudah disetujui hanya akan dihapus dari daftar Anda. Data tetap aman di server/admin.";
    } else {
      msg =
        "Laporan dan file akan dihapus permanen dari server. Tindakan ini tidak dapat dibatalkan!";
    }
    Swal.fire({
      title: "Yakin hapus laporan?",
      html: "<b>" + nama + "</b><br>" + msg,
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Ya, hapus!",
      cancelButtonText: "Batal",
    }).then((result) => {
      if (result.isConfirmed) {
        form.submit();
      }
    });
  });

  const form = document.querySelector('form[action*="kirimlaporan/add"]');
  const fileInput = document.getElementById("files");
  const linkInput = document.getElementById("link_drive");

  if (form && fileInput && linkInput) {
    form.addEventListener("submit", function (e) {
      const hasFile = fileInput.files.length > 0 && fileInput.files[0].size > 0;
      const hasLink = linkInput.value.trim() !== "";

      if (!hasFile && !hasLink) {
        e.preventDefault();
        Swal.fire({
          icon: "warning",
          title: "Perhatian",
          text: "Harus mengupload file ATAU mengisi Link Drive! Minimal salah satu harus diisi.",
          confirmButtonText: "OK",
        });
        return false;
      }

      if (hasFile) {
        const file = fileInput.files[0];
        const maxSize = 1 * 1024 * 1024; // 1MB

        if (file.size > maxSize) {
          e.preventDefault();
          Swal.fire({
            icon: "error",
            title: "File Terlalu Besar",
            text: "Ukuran file terlalu besar! Maksimal 1MB.",
            confirmButtonText: "OK",
          });
          return false;
        }
      }

      if (hasLink) {
        const urlPattern = /^https?:\/\/.+/;
        if (!urlPattern.test(linkInput.value.trim())) {
          e.preventDefault();
          Swal.fire({
            icon: "error",
            title: "Link Tidak Valid",
            text: "Format Link Drive tidak valid! Harus dimulai dengan http:// atau https://",
            confirmButtonText: "OK",
          });
          return false;
        }
      }

      // Semua validasi lolos — tampilkan loading popup
      Swal.fire({
        title: "Sedang Mengirim...",
        text: "Mohon tunggu, laporan Anda sedang dikirim.",
        icon: "info",
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: function () {
          Swal.showLoading();
        },
      });
    });

    function updateValidationStatus() {
      const hasFile = fileInput.files.length > 0 && fileInput.files[0].size > 0;
      const hasLink = linkInput.value.trim() !== "";
      const submitBtn = form.querySelector('button[type="submit"]');

      if (hasFile || hasLink) {
        submitBtn.disabled = false;
        submitBtn.classList.remove("btn-secondary");
        submitBtn.classList.add("btn-primary");
      } else {
        submitBtn.disabled = true;
        submitBtn.classList.remove("btn-primary");
        submitBtn.classList.add("btn-secondary");
      }
    }

    fileInput.addEventListener("change", updateValidationStatus);
    linkInput.addEventListener("input", updateValidationStatus);

    updateValidationStatus();
  }

  $("#filterKirimLaporanModal").on("show.bs.modal", function () {
    if ($("#filter_bulan").length) {
      $("#filter_bulan_mobile").val($("#filter_bulan").val() || "");
    }
    if ($("#filter_tahun").length) {
      $("#filter_tahun_mobile").val($("#filter_tahun").val() || "");
    }
    if ($("#filter_kategori").length) {
      $("#filter_kategori_mobile").val($("#filter_kategori").val() || "");
    }
  });

  $("#btnFilterMobile").on("click", function (e) {
    e.preventDefault();
    if ($("#filter_bulan_mobile").val()) {
      $("#filter_bulan").val($("#filter_bulan_mobile").val());
    }
    if ($("#filter_tahun_mobile").val()) {
      $("#filter_tahun").val($("#filter_tahun_mobile").val());
    }
    if ($("#filter_kategori_mobile").val()) {
      $("#filter_kategori").val($("#filter_kategori_mobile").val());
    }
    if (window.innerWidth > 767) {
      table.ajax.reload();
    } else {
      mobileLaporanPage = 1;
      loadMobileLaporanCards();
    }
    $("#filterKirimLaporanModal").modal("hide");
  });

  $("#filterFormMobile").on("submit", function (e) {
    e.preventDefault();
    if ($("#filter_bulan_mobile").val()) {
      $("#filter_bulan").val($("#filter_bulan_mobile").val());
    }
    if ($("#filter_tahun_mobile").val()) {
      $("#filter_tahun").val($("#filter_tahun_mobile").val());
    }
    if ($("#filter_kategori_mobile").val()) {
      $("#filter_kategori").val($("#filter_kategori_mobile").val());
    }
    if (window.innerWidth > 767) {
      table.ajax.reload();
    } else {
      mobileLaporanPage = 1;
      loadMobileLaporanCards();
    }
    return false;
  });
});
