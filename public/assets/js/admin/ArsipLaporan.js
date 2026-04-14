var checkedIds = [];
var desktopCheckedIds = [];

$(document).ready(function () {
  var flashMsg = $(".alert").first();
  if (flashMsg.length > 0) {
    var msgType = flashMsg.hasClass("alert-success")
      ? "success"
      : flashMsg.hasClass("alert-danger")
      ? "error"
      : flashMsg.hasClass("alert-warning")
      ? "warning"
      : "info";
    var msgText = flashMsg
      .clone()
      .find(".btn-close")
      .remove()
      .end()
      .text()
      .trim();

    if (msgText) {
      Swal.fire({
        icon: msgType,
        title:
          msgType === "success"
            ? "Berhasil!"
            : msgType === "error"
            ? "Error!"
            : msgType === "warning"
            ? "Peringatan!"
            : "Info",
        text: msgText,
        confirmButtonColor:
          msgType === "success"
            ? "#7c3aed"
            : msgType === "error"
            ? "#d33"
            : msgType === "warning"
            ? "#f59e0b"
            : "#3085d6",
      });
      flashMsg.hide();
    }
  }

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

  var arsipDataOriginal = window.arsipDataOriginal;
  var arsipData = arsipDataOriginal.slice();
  var mobilePage = 1;
  var mobileLength = 10;

  function applyMobileFilter() {
    var pengirim = $("#filterPengirim").val();
    var bulan = $("#filterBulan").val();
    var tahun = $("#filterTahun").val();
    var kategori = $("#filterKategori").val();
    arsipData = arsipDataOriginal.filter(function (row) {
      var match = true;
      if (pengirim && row.nama_lengkap !== pengirim) match = false;
      if (bulan && row.bulan !== bulan) match = false;
      if (tahun && row.tahun !== tahun) match = false;
      if (kategori && row.kategori !== kategori) match = false;
      return match;
    });
    mobilePage = 1;
    renderMobileCards();
    updateNotification();
  }

  function renderMobileCards() {
    var searchQuery = $("#search_mobile_arsip").val().toLowerCase();
    var filteredData = arsipData;

    if (searchQuery) {
      filteredData = arsipData.filter(function (row) {
        return (
          (row.nama_laporan &&
            row.nama_laporan.toLowerCase().indexOf(searchQuery) !== -1) ||
          (row.nama_lengkap &&
            row.nama_lengkap.toLowerCase().indexOf(searchQuery) !== -1) ||
          (row.keterangan &&
            row.keterangan.toLowerCase().indexOf(searchQuery) !== -1) ||
          (row.kategori &&
            row.kategori.toLowerCase().indexOf(searchQuery) !== -1)
        );
      });
    }

    var start = (mobilePage - 1) * mobileLength;
    var end = Math.min(start + mobileLength, filteredData.length);
    var html = "";
    if (filteredData.length === 0) {
      html =
        '<div class="text-center text-muted">Tidak ada arsip laporan.</div>';
    } else {
      for (var i = start; i < end; i++) {
        var row = filteredData[i];
        var isChecked = checkedIds.includes(String(row.id)) ? "checked" : "";
        var no = i + 1;
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
        var bulan_tahun =
          String(row.bulan || "").padStart(2, "0") + "/" + (row.tahun || "");
        var tanggal_upload = row.created_at
          ? new Date(row.created_at).toLocaleDateString("id-ID")
          : "-";

        html +=
          '<div class="border rounded mb-3 p-3 shadow-sm arsip-card-mobile" style="background:#fff;">' +
          '<div class="fw-bold mb-1 d-flex justify-content-between align-items-center">' +
          '<div class="d-flex align-items-center">' +
          '<input type="checkbox" name="selected[]" value="' +
          row.id +
          '" class="form-check-input me-2 arsip-checkbox" ' +
          isChecked +
          ">" +
          "<span>No. " +
          no +
          " - " +
          (row.nama_laporan || "-") +
          "</span>" +
          "</div>" +
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
          "</div>";
      }
    }
    $("#mobileArsipCards").html(html);

    $(".arsip-checkbox:checked").each(function () {
      $(this).closest(".arsip-card-mobile").addClass("card-selected");
    });

    var totalPages = Math.ceil(filteredData.length / mobileLength);
    if (mobilePage > totalPages) mobilePage = totalPages;

    if (filteredData.length > mobileLength) {
      $("#mobileArsipPagination").show();

      var pageNumbersHtml = "";
      var maxPages = Math.min(3, totalPages);
      var actualStart = Math.max(1, mobilePage - 1);
      var actualEnd = Math.min(totalPages, actualStart + maxPages - 1);

      if (actualEnd - actualStart < maxPages - 1) {
        actualStart = Math.max(1, actualEnd - maxPages + 1);
      }

      for (var i = actualStart; i <= actualEnd; i++) {
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

      $("#mobileArsipPageNumbers").html(pageNumbersHtml);

      if (mobilePage === 1) {
        $("#mobileArsipPrev").prop("disabled", true).addClass("disabled");
      } else {
        $("#mobileArsipPrev").prop("disabled", false).removeClass("disabled");
      }

      if (mobilePage === totalPages) {
        $("#mobileArsipNext").prop("disabled", true).addClass("disabled");
      } else {
        $("#mobileArsipNext").prop("disabled", false).removeClass("disabled");
      }

      $(document)
        .off("click", ".page-number")
        .on("click", ".page-number", function (e) {
          e.preventDefault();
          mobilePage = parseInt($(this).data("page"));
          renderMobileCards();
        });
    } else {
      $("#mobileArsipPagination").hide();
    }
  }

  $(document).on("input", "#search_mobile_arsip", function () {
    mobilePage = 1;
    renderMobileCards();
    updateNotification();
  });

  $(document).on("click", "#mobileArsipPrev:not(:disabled)", function (e) {
    e.preventDefault();
    if (mobilePage > 1) {
      mobilePage--;
      renderMobileCards();
    }
  });

  $(document).on("click", "#mobileArsipNext:not(:disabled)", function (e) {
    e.preventDefault();
    var searchQuery = $("#search_mobile_arsip").val().toLowerCase();
    var filteredData = arsipData;
    if (searchQuery) {
      filteredData = arsipData.filter(function (row) {
        return (
          (row.nama_laporan &&
            row.nama_laporan.toLowerCase().indexOf(searchQuery) !== -1) ||
          (row.nama_lengkap &&
            row.nama_lengkap.toLowerCase().indexOf(searchQuery) !== -1) ||
          (row.keterangan &&
            row.keterangan.toLowerCase().indexOf(searchQuery) !== -1) ||
          (row.kategori &&
            row.kategori.toLowerCase().indexOf(searchQuery) !== -1)
        );
      });
    }
    var totalPages = Math.ceil(filteredData.length / mobileLength);
    if (mobilePage < totalPages) {
      mobilePage++;
      renderMobileCards();
    }
  });

  if ($.fn.DataTable.isDataTable("#arsipTable")) {
    $("#arsipTable").DataTable().destroy();
  }
  var table = $("#arsipTable").DataTable({
    language: {
      emptyTable: "Tidak ada data yang tersedia",
      info: "",
      infoEmpty: "",
      infoFiltered: "",
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
    info: false,
    ajax: {
      url: window.getArsipAjaxUrl,
      type: "GET",
      data: function (d) {
        d.pengirim = $("#filterPengirim").val();
        d.bulan = $("#filterBulan").val();
        d.tahun = $("#filterTahun").val();
        d.kategori = $("#filterKategori").val();
      },
      error: function (xhr, error, thrown) {
        console.error("AJAX Error:", xhr.status, thrown, xhr.responseText);
        alert("AJAX Error: " + xhr.status + "\n" + xhr.responseText);
      },
    },
    order: [[6, "desc"]],
    columnDefs: [
      { orderable: false, targets: 0 },
      { orderable: false, targets: 7 },
    ],
    lengthMenu: [
      [10, 25, 50, 100, -1],
      [10, 25, 50, 100, "Semua"],
    ],
    destroy: true,
    drawCallback: function (settings) {
      $('#arsipTable tbody tr input[name="selected[]"]').each(function () {
        var id = $(this).val();
        $(this).prop("checked", desktopCheckedIds.includes(id));
      });

      var visibleChecked = $(
        '#arsipTable tbody tr:visible input[name="selected[]"]:checked'
      ).length;
      var visibleTotal = $(
        '#arsipTable tbody tr:visible input[name="selected[]"]'
      ).length;
      var allChecked = visibleTotal > 0 && visibleChecked === visibleTotal;
      $("#selectAll, #checkAllBox").prop("checked", allChecked);
      updateNotification();
    },
  });

  $(document).on("click", "#btnPilihSemua", function (e) {
    e.preventDefault();
    e.stopPropagation();

    var currentState = $("#checkAllBox").is(":checked");
    var newState = !currentState;

    $("#checkAllBox, #selectAll").prop("checked", newState);
    $("#selectAll").trigger("change");
  });

  $(document).on("change", "#checkAllBox", function () {
    var checked = $(this).is(":checked");
    $("#selectAll").prop("checked", checked);
    $("#selectAll").trigger("change");
  });

  $("#selectAll").on("change", function () {
    $("#checkAllBox").prop("checked", this.checked);

    $('#arsipTable tbody tr:visible input[name="selected[]"]').prop(
      "checked",
      this.checked
    );

    if (this.checked) {
      $('#arsipTable tbody tr:visible input[name="selected[]"]').each(
        function () {
          var id = $(this).val();
          if (!desktopCheckedIds.includes(id)) {
            desktopCheckedIds.push(id);
          }
        }
      );
    } else {
      $('#arsipTable tbody tr:visible input[name="selected[]"]').each(
        function () {
          var id = $(this).val();
          desktopCheckedIds = desktopCheckedIds.filter(function (checkedId) {
            return checkedId !== id;
          });
        }
      );
    }
    updateNotification();
  });

  $(document).on(
    "change",
    '#arsipTable tbody input[name="selected[]"]',
    function () {
      var id = $(this).val();
      if ($(this).is(":checked")) {
        if (!desktopCheckedIds.includes(id)) {
          desktopCheckedIds.push(id);
        }
      } else {
        desktopCheckedIds = desktopCheckedIds.filter(function (checkedId) {
          return checkedId !== id;
        });
      }

      var visibleChecked = $(
        '#arsipTable tbody tr:visible input[name="selected[]"]:checked'
      ).length;
      var visibleTotal = $(
        '#arsipTable tbody tr:visible input[name="selected[]"]'
      ).length;
      var allChecked = visibleTotal > 0 && visibleChecked === visibleTotal;
      $("#selectAll, #checkAllBox").prop("checked", allChecked);
      updateNotification();
    }
  );

  $("#btnDownloadZip").on("click", function (e) {
    e.preventDefault();

    if (desktopCheckedIds.length === 0) {
      Swal.fire({
        icon: "warning",
        title: "Tidak ada file dipilih",
        text: "Pilih minimal satu file arsip yang ingin diunduh!",
        confirmButtonColor: "#7c3aed",
      });
      return false;
    }

    var form = $(
      '<form method="post" action="' +
        window.arsipDownloadZipUrl +
        '" style="display:none;"></form>'
    );
    desktopCheckedIds.forEach(function (id) {
      form.append('<input type="hidden" name="selected[]" value="' + id + '">');
    });
    $("body").append(form);
    form.submit();
    form.remove();
  });

  $("#btnDeleteArsip").on("click", function (e) {
    e.preventDefault();

    if (desktopCheckedIds.length === 0) {
      Swal.fire({
        icon: "warning",
        title: "Tidak ada file dipilih",
        text: "Pilih minimal satu file arsip yang ingin dihapus!",
        confirmButtonColor: "#7c3aed",
      });
      return false;
    }

    Swal.fire({
      title: "Yakin hapus file arsip terpilih?",
      text: "File yang dipilih akan dihapus permanen!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Ya, hapus!",
      cancelButtonText: "Batal",
    }).then((result) => {
      if (result.isConfirmed) {
        var form = $(
          '<form method="post" action="' +
            window.arsipDeleteBulkUrl +
            '" style="display:none;"></form>'
        );
        desktopCheckedIds.forEach(function (id) {
          form.append(
            '<input type="hidden" name="selected[]" value="' + id + '">'
          );
        });
        $("body").append(form);
        form.submit();
        form.remove();
      }
    });
  });

  $("#filterPengirim, #filterBulan, #filterTahun, #filterKategori").on(
    "change",
    function () {
      table.ajax.reload();
      setTimeout(function () {
        $('#arsipTable tbody tr input[name="selected[]"]').each(function () {
          var id = $(this).val();
          $(this).prop("checked", desktopCheckedIds.includes(id));
        });
        var visibleChecked = $(
          '#arsipTable tbody tr:visible input[name="selected[]"]:checked'
        ).length;
        var visibleTotal = $(
          '#arsipTable tbody tr:visible input[name="selected[]"]'
        ).length;
        var allChecked = visibleTotal > 0 && visibleChecked === visibleTotal;
        $("#selectAll, #checkAllBox").prop("checked", allChecked);
        updateNotification();
      }, 300);
    }
  );

  renderMobileCards();

  $("#btnDownloadZipMobile").on("click", function (e) {
    e.preventDefault();

    if (checkedIds.length === 0) {
      return false;
    }

    var form = $(
      '<form method="post" action="' +
        window.arsipDownloadZipUrl +
        '" style="display:none;"></form>'
    );
    checkedIds.forEach(function (id) {
      form.append('<input type="hidden" name="selected[]" value="' + id + '">');
    });
    $("body").append(form);
    form.submit();
    form.remove();
  });

  $("#btnDeleteArsipMobile").on("click", function (e) {
    e.preventDefault();

    if (checkedIds.length === 0) {
      Swal.fire({
        icon: "warning",
        title: "Tidak ada file dipilih",
        text: "Pilih minimal satu file arsip yang ingin dihapus!",
        confirmButtonColor: "#7c3aed",
      });
      return false;
    }

    Swal.fire({
      title: "Yakin hapus file arsip terpilih?",
      text: "File yang dipilih akan dihapus permanen!",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#3085d6",
      confirmButtonText: "Ya, hapus!",
      cancelButtonText: "Batal",
    }).then((result) => {
      if (result.isConfirmed) {
        var form = $(
          '<form method="post" action="' +
            window.arsipDeleteBulkUrl +
            '" style="display:none;"></form>'
        );
        checkedIds.forEach(function (id) {
          form.append(
            '<input type="hidden" name="selected[]" value="' + id + '">'
          );
        });
        $("body").append(form);
        form.submit();
        form.remove();
      }
    });
  });

  $("#filterPengirim, #filterBulan, #filterTahun, #filterKategori").on(
    "change",
    function () {
      applyMobileFilter();
    }
  );

  $(
    "#filterPengirimMobile, #filterBulanMobile, #filterTahunMobile, #filterKategoriMobile"
  ).on("change", function () {
    var filterId = $(this).attr("id").replace("Mobile", "");
    $("#" + filterId)
      .val($(this).val())
      .trigger("change");
    $("#filterArsipModal").modal("hide");
  });

  $("#filterArsipModal").on("show.bs.modal", function () {
    $("#filterPengirimMobile").val($("#filterPengirim").val());
    $("#filterBulanMobile").val($("#filterBulan").val());
    $("#filterTahunMobile").val($("#filterTahun").val());
    $("#filterKategoriMobile").val($("#filterKategori").val());
  });

  $(document).on("click", "#btnPilihSemuaMobile", function (e) {
    e.preventDefault();
    e.stopPropagation();

    var allChecked =
      arsipData.length > 0 && checkedIds.length === arsipData.length;
    var newState = !allChecked;

    if (newState) {
      arsipData.forEach(function (row) {
        var id = String(row.id);
        if (!checkedIds.includes(id)) {
          checkedIds.push(id);
        }
      });
    } else {
      arsipData.forEach(function (row) {
        var id = String(row.id);
        checkedIds = checkedIds.filter(function (checkedId) {
          return checkedId !== id;
        });
      });
    }
    renderMobileCards();
    updateNotification();
  });

  $(document).on("change", "#selectAllMobile", function () {
    if ($(this).is(":checked")) {
      arsipData.forEach(function (row) {
        var id = String(row.id);
        if (!checkedIds.includes(id)) {
          checkedIds.push(id);
        }
      });
    } else {
      arsipData.forEach(function (row) {
        var id = String(row.id);
        checkedIds = checkedIds.filter(function (checkedId) {
          return checkedId !== id;
        });
      });
    }
    renderMobileCards();
    updateNotification();
  });

  $(document).on("change", ".arsip-checkbox", function () {
    var val = $(this).val();
    var $card = $(this).closest(".arsip-card-mobile");

    if ($(this).is(":checked")) {
      if (!checkedIds.includes(val)) checkedIds.push(val);
      $card.addClass("card-selected");
    } else {
      checkedIds = checkedIds.filter(function (id) {
        return id !== val;
      });
      $card.removeClass("card-selected");
    }
    updateNotification();
  });

  $(document).on("click", ".arsip-card-mobile", function (e) {
    if (
      $(e.target).is('input[type="checkbox"]') ||
      $(e.target).closest('input[type="checkbox"]').length > 0 ||
      $(e.target).is(".btn") ||
      $(e.target).closest(".btn").length > 0 ||
      $(e.target).is("a") ||
      $(e.target).closest("a").length > 0 ||
      $(e.target).is("input") ||
      $(e.target).closest("input").length > 0 ||
      $(e.target).is("label") ||
      $(e.target).closest("label").length > 0
    ) {
      return;
    }

    var $checkbox = $(this).find(".arsip-checkbox");
    if ($checkbox.length > 0) {
      var newState = !$checkbox.is(":checked");
      $checkbox.prop("checked", newState).trigger("change");
    }
  });
});

var notificationTimer = null;

function updateNotification() {
  var notifPopup = document.getElementById("notifDataTerpilih");
  var notifText = document.getElementById("notifTextTerpilih");

  if (notifPopup && notifText) {
    var totalChecked = 0;

    if (
      window.innerWidth >= 768 &&
      $("#arsipTable").length > 0 &&
      $("#arsipTable").is(":visible")
    ) {
      totalChecked = desktopCheckedIds.length;
    } else {
      totalChecked = checkedIds.length;
    }

    if (totalChecked > 0) {
      notifText.textContent = totalChecked + " data terpilih";
      showNotification();
    } else {
      hideNotification();
    }
  }
}

function showNotification() {
  var notifPopup = document.getElementById("notifDataTerpilih");
  if (notifPopup) {
    if (notificationTimer) {
      clearTimeout(notificationTimer);
      notificationTimer = null;
    }

    notifPopup.classList.remove("show");
    notifPopup.style.display = "block";
    setTimeout(function () {
      notifPopup.classList.add("show");

      notificationTimer = setTimeout(function () {
        hideNotification();
        notificationTimer = null;
      }, 3000);
    }, 10);
  }
}

function hideNotification() {
  var notifPopup = document.getElementById("notifDataTerpilih");
  if (notifPopup) {
    if (notificationTimer) {
      clearTimeout(notificationTimer);
      notificationTimer = null;
    }

    notifPopup.classList.remove("show");
    setTimeout(function () {
      notifPopup.style.display = "none";
    }, 300);
  }
}
