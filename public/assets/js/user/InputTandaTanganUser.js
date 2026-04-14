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

  $("#pilihPegawaiModal").on("hidden.bs.modal", function (e) {
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

  $("#pilihPegawaiModal").on("show.bs.modal", function (e) {
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

  const MAX_IMG_SIZE = 1 * 1024 * 1024; // 1MB

  $("#gambar_ttd").on("change", function (e) {
    const [file] = this.files;
    if (file) {
      if (file.size > MAX_IMG_SIZE) {
        Swal.fire({
          icon: "warning",
          title: "Gambar Terlalu Besar!",
          text: "Ukuran gambar tanda tangan maksimal adalah 1 MB. Silakan pilih file yang lebih kecil.",
          confirmButtonColor: "#7c3aed",
          confirmButtonText: "Mengerti",
        });
        this.value = "";
        $("#preview_gambar_ttd").hide();
        return;
      }
      const url = URL.createObjectURL(file);
      $("#preview_gambar_ttd").attr("src", url).show();
    } else {
      $("#preview_gambar_ttd").hide();
    }
  });

  $('input[type="file"]').on("change", function (e) {
    const [file] = this.files;
    const modalId = $(this).closest(".modal").attr("id");

    if (modalId && modalId.startsWith("editTandaTanganGambarModal")) {
      const previewId =
        "preview_gambar_edit_" +
        modalId.replace("editTandaTanganGambarModal", "");
      if (file) {
        if (file.size > MAX_IMG_SIZE) {
          Swal.fire({
            icon: "warning",
            title: "Gambar Terlalu Besar!",
            text: "Ukuran gambar tanda tangan maksimal adalah 1 MB. Silakan pilih file yang lebih kecil.",
            confirmButtonColor: "#7c3aed",
            confirmButtonText: "Mengerti",
          });
          $(this).val("");
          $("#" + previewId).hide();
          return;
        }
        const url = URL.createObjectURL(file);
        $("#" + previewId)
          .attr("src", url)
          .show();
      } else {
        $("#" + previewId).hide();
      }
    }
  });

  $('#ttdTab button[data-bs-toggle="tab"]').on("shown.bs.tab", function (e) {});

  const SEARCH_PEGAWAI_URL = window.location.origin + "/user/searchPegawaiAjax";

  let debounceTimer;
  $("#search_pegawai_input").on("input", function () {
    const q = $(this).val().trim();
    clearTimeout(debounceTimer);
    if (q.length < 2) {
      $("#search_pegawai_results").hide();
      return;
    }
    debounceTimer = setTimeout(function () {
      $.get(SEARCH_PEGAWAI_URL, { q: q, limit: 10 }, function (res) {
        let html = "";
        if (res && res.length > 0) {
          res.forEach(function (row) {
            html += `<div class='search-pegawai-item px-2 py-2' style='cursor:pointer;' data-id='${row.id}' data-nama='${row.nama}' data-nip='${row.nip}' data-jabatan='${row.jabatan}'>
                            <b>${row.nama}</b> <span class='text-muted' style='font-size:0.95em;'>(${row.nip})</span><br>
                            <span style='font-size:0.95em;'>${row.jabatan}</span>
                        </div>`;
          });
        } else {
          html = '<div class="px-2 py-2 text-muted">Tidak ada hasil.</div>';
        }
        $("#search_pegawai_results").html(html).show();
      });
    }, 250);
  });

  $(document).on("click", ".search-pegawai-item", function () {
    const id = $(this).data("id");
    const nama = $(this).data("nama");
    const nip = $(this).data("nip");
    const jabatan = $(this).data("jabatan");
    $("#nama_penandatangan").val(nama);
    $("#nip_penandatangan").val(nip);
    $("#nama_jabatan").val(jabatan);
    $("#search_pegawai_id").val(id);
    $("#pilihPegawaiModal").modal("hide");
    $("#search_pegawai_results").hide();
  });

  $(document).on("click", function (e) {
    if (
      !$(e.target).closest("#search_pegawai_input, #search_pegawai_results")
        .length
    ) {
      $("#search_pegawai_results").hide();
    }
  });

  $("#pilihPegawaiModal").on("shown.bs.modal", function () {
    $("#search_pegawai_input").val("");
    $("#search_pegawai_results").hide();
  });

  $(document).on("click", "a.aksi-btn.btn-danger", function (e) {
    var href = $(this).attr("href");
    var isDelete =
      $(this).attr("title") &&
      $(this).attr("title").toLowerCase().includes("hapus");
    if (isDelete) {
      e.preventDefault();
      Swal.fire({
        title: "Konfirmasi Hapus",
        text: "Apakah Anda yakin ingin menghapus data tanda tangan ini?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#39396a",
        confirmButtonText: "Ya, Hapus",
        cancelButtonText: "Batal",
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = href;
        }
      });
    }
  });
});
