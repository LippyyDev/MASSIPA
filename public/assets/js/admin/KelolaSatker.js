$(document).ready(function () {
  var flashMsg = $("body").data("flash-msg");
  var flashType = $("body").data("flash-type");
  var flashSuccess = $("body").data("flash-success");
  var flashError = $("body").data("flash-error");

  if (flashMsg) {
    Swal.fire({
      icon: flashType === "success" ? "success" : "error",
      title: flashType === "success" ? "Berhasil" : "Gagal",
      text: flashMsg,
      timer: 2500,
      showConfirmButton: false,
    });
  } else if (flashSuccess) {
    Swal.fire({
      icon: "success",
      title: "Berhasil",
      text: flashSuccess,
      timer: 2500,
      showConfirmButton: false,
    });
  } else if (flashError) {
    Swal.fire({
      icon: "error",
      title: "Gagal",
      text: flashError,
      timer: 2500,
      showConfirmButton: false,
    });
  }

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

  $("#modalSatker").on("hidden.bs.modal", function (e) {
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

  $("#modalSatker").on("show.bs.modal", function (e) {
    var modal = $(this);
    modal.removeAttr("aria-hidden");
  });

  $('[id^="modalSatkerEdit"]').on("hidden.bs.modal", function (e) {
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

  $('[id^="modalSatkerEdit"]').on("show.bs.modal", function (e) {
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

  if (window.innerWidth >= 768) {
    $("#satkerTable").DataTable({
      paging: true,
      searching: true,
      ordering: true,
      info: false,
      pageLength: 10,
      autoWidth: false,
      language: {
        emptyTable: "Tidak ada data yang tersedia",
        infoEmpty: "Tidak ada data yang tersedia",
        infoFiltered: "(disaring dari _MAX_ entri keseluruhan)",
        lengthMenu: "Tampilkan _MENU_ entri",
        loadingRecords: "Sedang memuat...",
        processing: "Sedang memproses...",
        search: "Cari:",
        searchPlaceholder: "Cari nama satker...",
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
      initComplete: function () {
        $(".dataTables_filter input").attr(
          "placeholder",
          "Cari nama satker..."
        );
      },
    });
  }

  var mobilePage = 1;
  var mobileLength = 10;
  var allSatkerCards = $(
    ".d-block.d-md-none .border.rounded.mb-3.p-3.shadow-sm"
  );
  var totalSatker = allSatkerCards.length;

  function loadMobileSatkerCards() {
    var start = (mobilePage - 1) * mobileLength;
    var end = start + mobileLength;

    allSatkerCards.hide();

    allSatkerCards.slice(start, end).show();

    var totalPages = Math.ceil(totalSatker / mobileLength);
    if (totalPages > 1 && totalSatker > 0) {
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
          loadMobileSatkerCards();
        });
    } else {
      $("#mobilePagination").hide();
    }
  }

  if (window.innerWidth < 768 && totalSatker > 0) {
    loadMobileSatkerCards();

    $("#mobilePrev")
      .off("click")
      .on("click", function () {
        if (mobilePage > 1) {
          mobilePage--;
          loadMobileSatkerCards();
        }
      });

    $("#mobileNext")
      .off("click")
      .on("click", function () {
        var totalPages = Math.ceil(totalSatker / mobileLength);
        if (mobilePage < totalPages) {
          mobilePage++;
          loadMobileSatkerCards();
        }
      });

    $(window).on("resize", function () {
      if (window.innerWidth >= 768) {
        allSatkerCards.show();
        $("#mobilePagination").hide();
      } else {
        loadMobileSatkerCards();
      }
    });
  }

  $(".btn-delete-satker").on("click", function (e) {
    e.preventDefault();
    var url = $(this).attr("href");
    var nama = $(this).data("nama");
    Swal.fire({
      title: "Yakin hapus satker?",
      text: "Data satker " + nama + " akan dihapus permanen!",
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
});
