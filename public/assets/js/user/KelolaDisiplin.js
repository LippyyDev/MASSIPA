document.addEventListener("DOMContentLoaded", function () {
  let mobileUserData = [];
  let currentMobileUserPage = 1;
  let mobileUserPageSize = 10;
  let filteredMobileUserData = [];
  const mainContent = document.querySelector(".main-content");

  function handleResize() {
    if (window.innerWidth <= 991) {
      if (mainContent) mainContent.style.marginLeft = "0";
    } else {
      if (mainContent) mainContent.style.marginLeft = "240px";
    }
  }
  window.addEventListener("resize", handleResize);
  handleResize();

  function loadMobileUserData() {
    const tableRows = document.querySelectorAll("#rekapPeriodeTable tbody tr");
    mobileUserData = [];

    tableRows.forEach((row, index) => {
      const cells = row.querySelectorAll("td");
      if (cells.length >= 13) {
        mobileUserData.push({
          no: cells[0].textContent.trim(),
          periode: cells[1].textContent.trim(),
          terlambat: cells[2].textContent.trim(),
          tidak_absen_masuk: cells[3].textContent.trim(),
          pulang_awal: cells[4].textContent.trim(),
          tidak_absen_pulang: cells[5].textContent.trim(),
          keluar_tidak_izin: cells[6].textContent.trim(),
          tidak_masuk_tanpa_ket: cells[7].textContent.trim(),
          tidak_masuk_sakit: cells[8].textContent.trim(),
          tidak_masuk_kerja: cells[9].textContent.trim(),
          bentuk_pembinaan: cells[10].textContent.trim(),
          keterangan: cells[11].textContent.trim(),
          aksi: cells[12].innerHTML,
        });
      }
    });

    filteredMobileUserData = [...mobileUserData];
    renderMobileUserCards();
  }

  function renderMobileUserCards() {
    const startIndex = (currentMobileUserPage - 1) * mobileUserPageSize;
    const endIndex = startIndex + mobileUserPageSize;
    const pageData = filteredMobileUserData.slice(startIndex, endIndex);

    let cardsHtml = "";

    if (pageData.length === 0) {
      cardsHtml =
        '<div class="text-center text-muted p-4">Tidak ada data</div>';
    } else {
      pageData.forEach((item, index) => {
        const actualIndex = startIndex + index + 1;
        cardsHtml += `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="card-title mb-0">${item.periode}</h6>
                            </div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="fw-bold text-dark mobile-pelanggaran-label small">T</div>
                                        <div class="mobile-pelanggaran-number">${item.terlambat}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="fw-bold text-dark mobile-pelanggaran-label small">TAM</div>
                                        <div class="mobile-pelanggaran-number">${item.tidak_absen_masuk}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="fw-bold text-dark mobile-pelanggaran-label small">PA</div>
                                        <div class="mobile-pelanggaran-number">${item.pulang_awal}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="fw-bold text-dark mobile-pelanggaran-label small">TAP</div>
                                        <div class="mobile-pelanggaran-number">${item.tidak_absen_pulang}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="fw-bold text-dark mobile-pelanggaran-label small">KTI</div>
                                        <div class="mobile-pelanggaran-number">${item.keluar_tidak_izin}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="fw-bold text-dark mobile-pelanggaran-label small">TK</div>
                                        <div class="mobile-pelanggaran-number">${item.tidak_masuk_tanpa_ket}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="fw-bold text-dark mobile-pelanggaran-label small">TMS</div>
                                        <div class="mobile-pelanggaran-number">${item.tidak_masuk_sakit}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="fw-bold text-dark mobile-pelanggaran-label small">TMK</div>
                                        <div class="mobile-pelanggaran-number">${item.tidak_masuk_kerja}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="small text-muted">BP:</div>
                                        <div class="small">${item.bentuk_pembinaan}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="small text-muted">Ket:</div>
                                        <div class="small">${item.keterangan}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end gap-1">
                                ${item.aksi}
                            </div>
                        </div>
                    </div>
                `;
      });
    }

    $("#mobileUserDisiplinCards").html(cardsHtml);
    updateMobileUserPagination();
  }

  function getPelanggaranClass(count) {
    if (count > 5) return "high";
    if (count > 2) return "medium";
    if (count > 0) return "low";
    return "zero";
  }

  function updateMobileUserPagination() {
    const totalPages = Math.ceil(
      filteredMobileUserData.length / mobileUserPageSize
    );

    if (totalPages > 1) {
      $("#mobileUserPagination").show();

      var pageNumbersHtml = "";
      var startPage = Math.max(1, currentMobileUserPage - 1);
      var endPage = Math.min(totalPages, startPage + 2);

      if (endPage - startPage < 2) {
        startPage = Math.max(1, endPage - 2);
      }

      for (var i = startPage; i <= endPage; i++) {
        var activeClass = i === currentMobileUserPage ? "active" : "";
        pageNumbersHtml +=
          '<li class="page-item"><button class="page-link page-number ' +
          activeClass +
          '" data-page="' +
          i +
          '">' +
          i +
          "</button></li>";
      }

      $("#mobileUserPageNumbers").html(pageNumbersHtml);
      $("#mobileUserPrev").prop("disabled", currentMobileUserPage === 1);
      $("#mobileUserNext").prop(
        "disabled",
        currentMobileUserPage === totalPages
      );

      $(".page-number")
        .off("click")
        .on("click", function () {
          currentMobileUserPage = parseInt($(this).data("page"));
          renderMobileUserCards();
        });
    } else {
      $("#mobileUserPagination").hide();
      $("#mobileUserPageNumbers").empty();
    }
  }

  function searchMobileUserData() {
    const searchTerm = $("#search_mobile_user_disiplin").val().toLowerCase();
    filteredMobileUserData = mobileUserData.filter((item) =>
      item.periode.toLowerCase().includes(searchTerm)
    );
    currentMobileUserPage = 1;
    renderMobileUserCards();
  }

  $("#search_mobile_user_disiplin").on("input", searchMobileUserData);

  $("#mobileUserPrev").on("click", function () {
    if (currentMobileUserPage > 1) {
      currentMobileUserPage--;
      renderMobileUserCards();
    }
  });

  $("#mobileUserNext").on("click", function () {
    const totalPages = Math.ceil(
      filteredMobileUserData.length / mobileUserPageSize
    );
    if (currentMobileUserPage < totalPages) {
      currentMobileUserPage++;
      renderMobileUserCards();
    }
  });

  if (
    window.innerWidth >= 768 &&
    $("#rekapPeriodeTable").length &&
    !$.fn.DataTable.isDataTable("#rekapPeriodeTable")
  ) {
    $("#rekapPeriodeTable").DataTable({
      paging: true,
      searching: true,
      ordering: true,
      info: false,
      pageLength: 10,
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
    });
  }

  setTimeout(() => {
    loadMobileUserData();
  }, 500);
});
$(document).on("submit", ".form-hapus-periode", function (e) {
  e.preventDefault();
  var form = this;
  Swal.fire({
    title: "Konfirmasi Hapus",
    text: "Anda ingin menghapus seluruh data kedisiplinan pegawai / hakim pada bulan ini?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Ya, Hapus",
    cancelButtonText: "Batal",
  }).then((result) => {
    if (result.isConfirmed) {
      form.submit();
    }
  });
});
