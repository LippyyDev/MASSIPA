$(document).ready(function () {
  // Fix "Blocked aria-hidden on an element because its descendant retained focus"
  // MutationObserver catches the EXACT moment aria-hidden is set and moves focus out.
  // This is more reliable than hide.bs.modal which may fire too early/late.
  document.querySelectorAll(".modal").forEach(function (modal) {
    new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        if (
          mutation.attributeName === "aria-hidden" &&
          modal.getAttribute("aria-hidden") === "true"
        ) {
          // Check if focused element is inside the modal
          if (modal.contains(document.activeElement)) {
            document.activeElement.blur();
          }
        }
      });
    }).observe(modal, { attributes: true, attributeFilter: ["aria-hidden"] });
  });

  $("#rekapTable").DataTable({
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
    pageLength: 10,
    info: false,
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
  });

  var mobileSatkerPage = 1;
  var mobileSatkerPageSize = 10;
  var allMobileCards = [];
  var filteredMobileCards = [];
  var currentSearch = "";

  function initializeMobileCards() {
    var $originalContainer = $("#mobileSatkerCardsOriginal");
    if ($originalContainer.length && $originalContainer.children().length > 0) {
      allMobileCards = [];
      $originalContainer
        .children(".border.rounded.mb-3.p-3.shadow-sm")
        .each(function () {
          allMobileCards.push($(this).clone(true));
        });
      filteredMobileCards = allMobileCards;
      renderMobileCards();
    }
  }

  function renderMobileCards() {
    var $container = $("#mobileSatkerCards");
    $container.empty();

    var start = (mobileSatkerPage - 1) * mobileSatkerPageSize;
    var end = start + mobileSatkerPageSize;
    var pageData = filteredMobileCards.slice(start, end);

    if (pageData.length === 0) {
      $container.html(
        '<div class="text-center text-muted py-4">Tidak ada data satker yang sesuai.</div>',
      );
      $("#mobileSatkerPagination").hide();
      return;
    }

    pageData.forEach(function ($card) {
      $container.append($card);
    });

    var total = filteredMobileCards.length;
    var totalPages = Math.ceil(total / mobileSatkerPageSize);

    if (total > mobileSatkerPageSize) {
      $("#mobileSatkerPagination").show();

      var pageNumbersHtml = "";
      var maxPages = Math.min(3, totalPages);
      var actualStart = Math.max(1, mobileSatkerPage - 1);
      var actualEnd = Math.min(totalPages, actualStart + maxPages - 1);

      if (actualEnd - actualStart < maxPages - 1) {
        actualStart = Math.max(1, actualEnd - maxPages + 1);
      }

      for (var i = actualStart; i <= actualEnd; i++) {
        var activeClass = i === mobileSatkerPage ? "active" : "";
        pageNumbersHtml +=
          '<li class="page-item" style="display: inline-block;"><button class="page-link page-number ' +
          activeClass +
          '" data-page="' +
          i +
          '">' +
          i +
          "</button></li>";
      }

      $("#mobileSatkerPageNumbers").html(pageNumbersHtml);

      if (mobileSatkerPage === 1) {
        $("#mobileSatkerPrev").prop("disabled", true).addClass("disabled");
      } else {
        $("#mobileSatkerPrev").prop("disabled", false).removeClass("disabled");
      }

      if (mobileSatkerPage === totalPages) {
        $("#mobileSatkerNext").prop("disabled", true).addClass("disabled");
      } else {
        $("#mobileSatkerNext").prop("disabled", false).removeClass("disabled");
      }
    } else {
      $("#mobileSatkerPagination").hide();
    }
  }

  let searchTimeout;
  $("#search_mobile_satker").on("input", function () {
    clearTimeout(searchTimeout);
    currentSearch = $(this).val().toLowerCase().trim();

    searchTimeout = setTimeout(function () {
      filterMobileCards(currentSearch);
    }, 300);
  });

  function filterMobileCards(searchQuery) {
    if (searchQuery === "") {
      filteredMobileCards = allMobileCards;
    } else {
      filteredMobileCards = allMobileCards.filter(function ($card) {
        const cardText = $card.text().toLowerCase();
        const satkerName = $card.find(".fw-bold").first().text().toLowerCase();
        return (
          satkerName.includes(searchQuery) || cardText.includes(searchQuery)
        );
      });
    }

    mobileSatkerPage = 1;
    renderMobileCards();
  }

  $(document).on("click", "#mobileSatkerPrev:not(:disabled)", function (e) {
    e.preventDefault();
    if (mobileSatkerPage > 1) {
      mobileSatkerPage--;
      renderMobileCards();
    }
  });

  $(document).on("click", "#mobileSatkerNext:not(:disabled)", function (e) {
    e.preventDefault();
    var total = filteredMobileCards.length;
    var totalPages = Math.ceil(total / mobileSatkerPageSize);
    if (mobileSatkerPage < totalPages) {
      mobileSatkerPage++;
      renderMobileCards();
    }
  });

  $(document).on("click", ".page-number", function (e) {
    e.preventDefault();
    mobileSatkerPage = parseInt($(this).data("page"));
    renderMobileCards();
  });

  if (window.innerWidth <= 767) {
    initializeMobileCards();
  }

  $(window).on("resize", function () {
    if (window.innerWidth <= 767 && allMobileCards.length === 0) {
      initializeMobileCards();
    }
  });

  let currentReports = [];

  $(document).on("click", ".clickable-icon", function (e) {
    e.preventDefault();

    const reportsData = $(this).data("reports");

    if (!reportsData || reportsData.length === 0) {
      alert("Tidak ada file atau link yang tersedia.");
      return;
    }

    currentReports = reportsData;

    if (currentReports.length === 1) {
      const report = currentReports[0];
      const hasFile = !!report.file_path;
      const hasLink = !!report.link_drive;

      if (hasFile && hasLink) {
        showSimpleChoiceModal(report);
      } else if (hasFile) {
        openFileByPath(report.file_path);
      } else if (hasLink) {
        openLinkByUrl(report.link_drive);
      }
    } else {
      showReportsListModal();
    }
  });

  $("#openFileBtn").click(function () {
    $("#choiceModal").modal("hide");
    setTimeout(() => {
      openFile();
    }, 300);
  });

  $("#openLinkBtn").click(function () {
    $("#choiceModal").modal("hide");
    setTimeout(() => {
      openLink();
    }, 300);
  });

  function showSimpleChoiceModal(report) {
    $("#choiceModalLabel").text("Pilih Aksi");
    $("#choiceModalText").text(
      `Laporan "${report.nama}" memiliki file dan link. Pilih aksi yang ingin dilakukan:`,
    );

    const container = $("#reportsListContainer");
    container.html(`
            <div class="d-grid gap-2">
                <button type="button" class="btn btn-info" onclick="openFileByPath('${report.file_path}'); $('#choiceModal').modal('hide');">
                    <i class="fas fa-eye"></i> Lihat File
                </button>
                <button type="button" class="btn btn-success" onclick="openLinkByUrl('${report.link_drive}'); $('#choiceModal').modal('hide');">
                    <i class="fas fa-link"></i> Lihat Link
                </button>
            </div>
        `);

    $("#choiceModal").modal("show");
  }

  function showReportsListModal() {
    $("#choiceModalLabel").text("Daftar Laporan");
    $("#choiceModalText").text("Pilih laporan yang ingin dibuka:");

    let listHtml = '<div class="list-group">';

    currentReports.forEach((report, index) => {
      const hasFile = !!report.file_path;
      const hasLink = !!report.link_drive;
      const statusBadge = getStatusBadge(report.status);
      const categoryIcon = getCategoryIcon(report.kategori);

      listHtml += `
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                ${categoryIcon} ${report.nama}
                            </h6>
                            <p class="mb-1 text-muted small">
                                ${report.kategori} - ${statusBadge}
                            </p>
                        </div>
                    </div>
                    <div class="mt-2">`;

      if (hasFile && hasLink) {
        listHtml += `
                    <button type="button" class="btn btn-sm btn-info btn-action me-2" onclick="openFileByPath('${report.file_path}')">
                        <i class="fas fa-eye"></i><span class="btn-text"> Lihat File</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-success btn-action" onclick="openLinkByUrl('${report.link_drive}')">
                        <i class="fas fa-link"></i><span class="btn-text"> Lihat Link</span>
                    </button>`;
      } else if (hasFile) {
        listHtml += `
                    <button type="button" class="btn btn-sm btn-info btn-action" onclick="openFileByPath('${report.file_path}')">
                        <i class="fas fa-eye"></i><span class="btn-text"> Lihat File</span>
                    </button>`;
      } else if (hasLink) {
        listHtml += `
                    <button type="button" class="btn btn-sm btn-success btn-action" onclick="openLinkByUrl('${report.link_drive}')">
                        <i class="fas fa-link"></i><span class="btn-text"> Lihat Link</span>
                    </button>`;
      }

      listHtml += `
                    </div>
                </div>`;
    });

    listHtml += "</div>";

    $("#reportsListContainer").html(listHtml);
    $("#choiceModal").modal("show");
  }

  function getStatusBadge(status) {
    switch (status) {
      case "diterima":
        return '<span class="badge bg-success">DITERIMA</span>';
      case "dilihat":
        return '<span class="badge bg-warning">DILIHAT</span>';
      case "terkirim":
        return '<span class="badge bg-info">TERKIRIM</span>';
      default:
        return (
          '<span class="badge bg-secondary">' + status.toUpperCase() + "</span>"
        );
    }
  }

  function getCategoryIcon(kategori) {
    if (kategori === "Laporan Disiplin") {
      return '<i class="fas fa-check-circle text-success"></i>';
    } else if (kategori === "Laporan Apel") {
      return '<i class="fas fa-apple-alt text-danger"></i>';
    }
    return '<i class="fas fa-file-alt"></i>';
  }

  function openFileByPath(filePath) {
    if (!filePath) return;

    // Encode filePath untuk URL yang aman
    const encodedFilePath = encodeURIComponent(filePath);
    const fileUrl =
      window.location.origin + "/admin/getFile/" + encodedFilePath;

    // Deteksi Android WebView untuk kompatibilitas
    const isAndroidWebView =
      /Android/i.test(navigator.userAgent) && /wv/i.test(navigator.userAgent);
    const isCustomWebView = /MASSIPA/i.test(navigator.userAgent);

    if (isAndroidWebView || isCustomWebView) {
      // Untuk WebView Android, gunakan window.location.href langsung (lebih reliable untuk preview)
      // WebView akan menangani Content-Disposition: inline dengan benar
      window.location.href = fileUrl;
    } else {
      // Untuk browser biasa, gunakan window.open
      const newWindow = window.open(fileUrl, "_blank");
      if (!newWindow) {
        alert("Popup diblokir. Silakan izinkan popup untuk melihat file PDF.");
      }
    }
  }

  function openLinkByUrl(linkUrl) {
    if (!linkUrl) return;

    // Validasi domain — hanya Google yang diizinkan (sama seperti di Kelola Laporan)
    var allowedDomains = [
      "drive.google.com",
      "docs.google.com",
      "www.google.com",
      "google.com",
    ];
    try {
      var parsedUrl = new URL(linkUrl);
      var host = parsedUrl.hostname.toLowerCase();
      if (!allowedDomains.includes(host)) {
        // Tutup Bootstrap modal dulu agar tidak konflik aria-hidden dengan SweetAlert
        var openModal = document.querySelector(".modal.show");
        if (openModal) {
          var bsModal = bootstrap.Modal.getInstance(openModal);
          if (bsModal) bsModal.hide();
        }
        if (document.activeElement) document.activeElement.blur();
        Swal.fire({
          icon: "error",
          title: "Gagal",
          text: "Link drive tidak valid atau domain tidak diizinkan.",
        });
        return;
      }
    } catch (e) {
      var openModal = document.querySelector(".modal.show");
      if (openModal) {
        var bsModal = bootstrap.Modal.getInstance(openModal);
        if (bsModal) bsModal.hide();
      }
      if (document.activeElement) document.activeElement.blur();
      Swal.fire({
        icon: "error",
        title: "Gagal",
        text: "Link drive tidak valid atau domain tidak diizinkan.",
      });
      return;
    }

    var newWindow = window.open(linkUrl, "_blank");
    if (!newWindow) {
      alert("Popup diblokir. Silakan izinkan popup untuk membuka link.");
    }
  }

  function openFile() {
    if (currentFileUrl) {
      const newWindow = window.open(currentFileUrl, "_blank");
      if (!newWindow) {
        alert("Popup diblokir. Silakan izinkan popup untuk melihat file PDF.");
      }
    }
  }

  function openLink() {
    if (currentLinkUrl) {
      const newWindow = window.open(currentLinkUrl, "_blank");
      if (!newWindow) {
        alert("Popup diblokir. Silakan izinkan popup untuk membuka link.");
      }
    }
  }

  window.openFileByPath = openFileByPath;
  window.openLinkByUrl = openLinkByUrl;
});
