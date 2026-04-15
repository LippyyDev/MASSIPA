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

  $("#filterDisiplinModal").on("hidden.bs.modal", function (e) {
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

  $("#filterDisiplinModal").on("show.bs.modal", function (e) {
    var modal = $(this);
    modal.removeAttr("aria-hidden");
    syncFilterValues();
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

  let table;
  let mobileData = [];
  let currentMobilePage = 1;
  let mobilePageSize = 10;
  let filteredMobileData = [];

  function initializeDataTable() {
    table = $("#kelolaDisiplinTable").DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: window.kelolaDisiplinAjaxUrl,
        type: "POST",
        data: function (d) {
          d.bulan = window.currentBulan;
          d.tahun = window.currentTahun;
          d.satker = window.currentSatker;
          d.pelanggaran = window.currentJenisPelanggaran;
          if (window.CSRF_TOKEN_NAME && window.CSRF_HASH) {
            d[window.CSRF_TOKEN_NAME] = window.CSRF_HASH;
          }
        },
        error: function (xhr, error, thrown) {
          console.error("AJAX Error:", xhr.status, thrown, xhr.responseText);

          var errorMsg = "Terjadi kesalahan saat memuat data.";
          if (xhr.status === 500) {
            errorMsg =
              "Kesalahan server internal. Periksa data database atau hubungi administrator.";
          } else if (xhr.status === 404) {
            errorMsg = "Endpoint tidak ditemukan. Periksa konfigurasi route.";
          }

          $("#kelolaDisiplinTable tbody").html(
            '<tr><td colspan="13" class="text-center text-danger">' +
              '<i class="fas fa-exclamation-triangle me-2"></i>' +
              errorMsg +
              "</td></tr>"
          );
        },
      },
      columns: [
        { data: "no", orderable: false, searchable: false },
        { data: "nama_pegawai" },
        { data: "jabatan" },
        { data: "nama_satker" },
        {
          data: "T",
          className: "text-center",
          render: function (data, type, row) {
            return renderPelanggaranNumber(data);
          },
        },
        {
          data: "TAM",
          className: "text-center",
          render: function (data, type, row) {
            return renderPelanggaranNumber(data);
          },
        },
        {
          data: "PA",
          className: "text-center",
          render: function (data, type, row) {
            return renderPelanggaranNumber(data);
          },
        },
        {
          data: "TAP",
          className: "text-center",
          render: function (data, type, row) {
            return renderPelanggaranNumber(data);
          },
        },
        {
          data: "KTI",
          className: "text-center",
          render: function (data, type, row) {
            return renderPelanggaranNumber(data);
          },
        },
        {
          data: "TK",
          className: "text-center",
          render: function (data, type, row) {
            return renderPelanggaranNumber(data);
          },
        },
        {
          data: "TMS",
          className: "text-center",
          render: function (data, type, row) {
            return renderPelanggaranNumber(data);
          },
        },
        {
          data: "TMK",
          className: "text-center",
          render: function (data, type, row) {
            return renderPelanggaranNumber(data);
          },
        },
        {
          data: "total_pelanggaran",
          className: "text-center",
          render: function (data, type, row) {
            return '<span class="pelanggaran-number high">' + data + "</span>";
          },
        },
      ],
      order: [[11, "desc"]],
      scrollX: false,
      autoWidth: false,
      responsive: true,
      columnDefs: [
        { targets: 0, width: "40px" },
        { targets: 1, width: "260px" },
        { targets: 2, width: "160px" },
        { targets: 3, width: "180px" },
        {
          targets: [4, 5, 6, 7, 8, 9, 10, 11],
          width: "60px",
          className: "text-center",
        },
      ],
      language: {
        emptyTable: "Tidak ada data pelanggaran disiplin untuk periode ini",
        infoEmpty: "Tidak ada data yang tersedia",
        infoFiltered: "(disaring dari _MAX_ entri keseluruhan)",
        lengthMenu: "Tampilkan _MENU_ entri",
        loadingRecords: "Sedang memuat...",
        processing: "Sedang memproses...",
        search: "Cari:",
        zeroRecords: "Tidak ditemukan data yang sesuai dengan filter",
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
      lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, "Semua"],
      ],
      pageLength: 10,
      drawCallback: function (settings) {
        var api = this.api();
        var pageInfo = api.page.info();
      },
    });

    setTimeout(function () {
      if (table && table.columns) {
        table.columns.adjust();
        if (table.responsive && typeof table.responsive.recalc === "function") {
          table.responsive.recalc();
        }
      }
    }, 0);

    $(window).on("resize", function () {
      if (table && table.columns) {
        table.columns.adjust();
        if (table.responsive && typeof table.responsive.recalc === "function") {
          table.responsive.recalc();
        }
      }
    });
  }

  function renderPelanggaranNumber(count) {
    let cssClass = "zero";
    if (count > 0 && count <= 2) {
      cssClass = "low";
    } else if (count > 2 && count <= 5) {
      cssClass = "medium";
    } else if (count > 5) {
      cssClass = "high";
    }

    return (
      '<span class="pelanggaran-number ' + cssClass + '">' + count + "</span>"
    );
  }

  function getFilterValue(filterType) {
    var desktopVal = $("#filter_" + filterType).val();
    var mobileVal = $("#filter_" + filterType + "_mobile").val();
    return desktopVal || mobileVal || "";
  }

  function syncFilterValues() {
    $("#filter_bulan").val(
      $("#filter_bulan_mobile").val() || $("#filter_bulan").val()
    );
    $("#filter_tahun").val(
      $("#filter_tahun_mobile").val() || $("#filter_tahun").val()
    );
    $("#filter_satker").val(
      $("#filter_satker_mobile").val() || $("#filter_satker").val()
    );
    $("#filter_pelanggaran").val(
      $("#filter_pelanggaran_mobile").val() || $("#filter_pelanggaran").val()
    );

    $("#filter_bulan_mobile").val(
      $("#filter_bulan").val() || $("#filter_bulan_mobile").val()
    );
    $("#filter_tahun_mobile").val(
      $("#filter_tahun").val() || $("#filter_tahun_mobile").val()
    );
    $("#filter_satker_mobile").val(
      $("#filter_satker").val() || $("#filter_satker_mobile").val()
    );
    $("#filter_pelanggaran_mobile").val(
      $("#filter_pelanggaran").val() || $("#filter_pelanggaran_mobile").val()
    );
  }

  $("#filter_bulan, #filter_tahun, #filter_satker, #filter_pelanggaran").on(
    "change",
    function () {
      var filterType = $(this).attr("id").replace("filter_", "");
      $("#filter_" + filterType + "_mobile").val($(this).val());

      if (typeof window !== "undefined") {
        window.currentBulan = getFilterValue("bulan");
        window.currentTahun = getFilterValue("tahun");
        window.currentSatker = getFilterValue("satker");
        window.currentJenisPelanggaran = getFilterValue("pelanggaran");
      }
      if (table) {
        table.ajax.reload();
      }
      loadMobileData();
    }
  );

  $(
    "#filter_bulan_mobile, #filter_tahun_mobile, #filter_satker_mobile, #filter_pelanggaran_mobile"
  ).on("change", function () {
    var filterType = $(this).data("filter-type");
    $("#filter_" + filterType).val($(this).val());

    if (typeof window !== "undefined") {
      window.currentBulan = getFilterValue("bulan");
      window.currentTahun = getFilterValue("tahun");
      window.currentSatker = getFilterValue("satker");
      window.currentJenisPelanggaran = getFilterValue("pelanggaran");
    }
    if (table) {
      table.ajax.reload();
    }
    loadMobileData();
  });

  $("#filterDisiplinModal").on("show.bs.modal", function () {
    syncFilterValues();
  });

  $("#filterDisiplinForm").on("submit", function (e) {
    e.preventDefault();
    return false;
  });

  function loadMobileData() {
    $.ajax({
      url: window.kelolaDisiplinAjaxUrl,
      type: "POST",
      data: {
        bulan: getFilterValue("bulan"),
        tahun: getFilterValue("tahun"),
        satker: getFilterValue("satker"),
        pelanggaran: getFilterValue("pelanggaran"),
        length: -1,
        [window.CSRF_TOKEN_NAME || 'csrf_test_name']: window.CSRF_HASH || '',
      },
      success: function (response) {
        if (response.data) {
          mobileData = response.data;
          filteredMobileData = [...mobileData];
          renderMobileCards();
        }
      },
      error: function (xhr, error, thrown) {
        console.error("Mobile data load error:", xhr.status, thrown);
        $("#mobileDisiplinCards").html(
          '<div class="text-center text-danger p-3">Gagal memuat data</div>'
        );
      },
    });
  }

  function renderMobileCards() {
    const startIndex = (currentMobilePage - 1) * mobilePageSize;
    const endIndex = startIndex + mobilePageSize;
    const pageData = filteredMobileData.slice(startIndex, endIndex);

    let cardsHtml = "";

    if (pageData.length === 0) {
      cardsHtml =
        '<div class="text-center text-muted p-4">Tidak ada data</div>';
    } else {
      pageData.forEach((item, index) => {
        const actualIndex = startIndex + index + 1;
        cardsHtml += `
                    <div class="border rounded mb-3 p-3 shadow-sm" style="background:#fff;">
                        <div class="fw-bold mb-1">No. ${actualIndex} - ${
          item.nama_pegawai
        }</div>
                        <div class="mb-1"><b>NIP:</b> ${item.nip}</div>
                        <div class="mb-1"><b>Pangkat:</b> ${item.pangkat}</div>
                        <div class="mb-2"><b>Jabatan:</b> ${item.jabatan}</div>
                        <div class="mb-3"><b>Satker:</b> ${
                          item.nama_satker
                        }</div>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-3">
                                <div class="text-center">
                                    <div class="fw-bold small">T</div>
                                    <div class="pelanggaran-number ${getPelanggaranClass(
                                      item.T
                                    )}">${item.T}</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-center">
                                    <div class="fw-bold small">TAM</div>
                                    <div class="pelanggaran-number ${getPelanggaranClass(
                                      item.TAM
                                    )}">${item.TAM}</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-center">
                                    <div class="fw-bold small">PA</div>
                                    <div class="pelanggaran-number ${getPelanggaranClass(
                                      item.PA
                                    )}">${item.PA}</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-center">
                                    <div class="fw-bold small">TAP</div>
                                    <div class="pelanggaran-number ${getPelanggaranClass(
                                      item.TAP
                                    )}">${item.TAP}</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-center">
                                    <div class="fw-bold small">KTI</div>
                                    <div class="pelanggaran-number ${getPelanggaranClass(
                                      item.KTI
                                    )}">${item.KTI}</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-center">
                                    <div class="fw-bold small">TK</div>
                                    <div class="pelanggaran-number ${getPelanggaranClass(
                                      item.TK
                                    )}">${item.TK}</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-center">
                                    <div class="fw-bold small">TMS</div>
                                    <div class="pelanggaran-number ${getPelanggaranClass(
                                      item.TMS
                                    )}">${item.TMS}</div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="text-center">
                                    <div class="fw-bold small">TMK</div>
                                    <div class="pelanggaran-number ${getPelanggaranClass(
                                      item.TMK
                                    )}">${item.TMK}</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center border-top pt-2">
                            <div class="fw-bold text-danger">Total Pelanggaran</div>
                            <div class="pelanggaran-number high fs-5">${
                              item.total_pelanggaran
                            }</div>
                        </div>
                    </div>
                `;
      });
    }

    $("#mobileDisiplinCards").html(cardsHtml);
    updateMobilePagination();
  }

  function getPelanggaranClass(count) {
    if (count > 5) return "high";
    if (count > 2) return "medium";
    if (count > 0) return "low";
    return "zero";
  }

  function updateMobilePagination() {
    const totalPages = Math.ceil(filteredMobileData.length / mobilePageSize);

    if (totalPages > 1) {
      $("#mobilePagination").show();

      var pageNumbersHtml = "";
      var startPage = Math.max(1, currentMobilePage - 1);
      var endPage = Math.min(totalPages, startPage + 2);

      if (endPage - startPage < 2) {
        startPage = Math.max(1, endPage - 2);
      }

      for (var i = startPage; i <= endPage; i++) {
        var activeClass = i === currentMobilePage ? "active" : "";
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
      $("#mobilePrev").prop("disabled", currentMobilePage === 1);
      $("#mobileNext").prop("disabled", currentMobilePage === totalPages);

      $(".page-number")
        .off("click")
        .on("click", function () {
          currentMobilePage = parseInt($(this).data("page"));
          renderMobileCards();
        });
    } else {
      $("#mobilePagination").hide();
      $("#mobilePageNumbers").empty();
    }
  }

  function searchMobileData() {
    const searchTerm = $("#search_mobile_disiplin").val().toLowerCase();
    filteredMobileData = mobileData.filter((item) =>
      item.nama_pegawai.toLowerCase().includes(searchTerm)
    );
    currentMobilePage = 1;
    renderMobileCards();
  }

  $("#search_mobile_disiplin").on("input", searchMobileData);

  $("#mobilePrev").on("click", function () {
    if (currentMobilePage > 1) {
      currentMobilePage--;
      renderMobileCards();
    }
  });

  $("#mobileNext").on("click", function () {
    const totalPages = Math.ceil(filteredMobileData.length / mobilePageSize);
    if (currentMobilePage < totalPages) {
      currentMobilePage++;
      renderMobileCards();
    }
  });

  function loadMobileDataOnFilterChange() {
    loadMobileData();
  }

  initializeDataTable();

  loadMobileData();
});
