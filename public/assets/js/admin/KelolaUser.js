$(document).ready(function () {
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

  function fixAriaHidden() {
    $(".modal:visible").each(function () {
      var modal = $(this);
      modal.removeAttr("aria-hidden");
      modal.find('[tabindex="-1"]').removeAttr("tabindex");
    });

    var activeElement = document.activeElement;
    if (activeElement && !$(activeElement).is("input, select, textarea")) {
      activeElement.blur();
    }
  }

  fixAriaHidden();

  setInterval(fixAriaHidden, 1000);

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

  $("#tambahUserModal").on("hidden.bs.modal", function (e) {
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

  $("#tambahUserModal").on("show.bs.modal", function (e) {
    var modal = $(this);
    modal.removeAttr("aria-hidden");
  });

  $('[id^="editUserModal"]').on("hidden.bs.modal", function (e) {
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

  $('[id^="editUserModal"]').on("show.bs.modal", function (e) {
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
    $("#userTable").DataTable({
      paging: true,
      searching: true,
      ordering: true,
      info: false,
      autoWidth: false,
      language: {
        emptyTable: "Tidak ada data yang tersedia",
        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
        infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
        search: "Cari:",
        paginate: {
          first: "Pertama",
          last: "Terakhir",
          next: "&gt;",
          previous: "&lt;",
        },
      },
    });
  }
  // Toggle show/hide password
  $(document).on("click", ".toggle-password", function () {
    var targetId = $(this).data("target");
    var $input = $("#" + targetId);
    var isPassword = $input.attr("type") === "password";
    $input.attr("type", isPassword ? "text" : "password");
    $(this).find("i").toggleClass("bi-eye bi-eye-slash");
  });

  var adminCount = parseInt($("body").data("admin-count")) || 0;

  $(document).on("click", ".btn-delete-user", function (e) {
    e.preventDefault();
    var form = $(this).closest("form");
    var role = $(this).data("role");

    // Proteksi: tidak boleh hapus jika hanya 1 admin tersisa
    if (role === "admin" && adminCount <= 1) {
      Swal.fire({
        icon: "warning",
        title: "Tidak Dapat Dihapus!",
        text: "Minimal harus ada 1 akun Admin. Tambahkan admin lain terlebih dahulu sebelum menghapus ini.",
        confirmButtonColor: "#7c3aed",
        confirmButtonText: "Mengerti",
      });
      return;
    }

    Swal.fire({
      title: "Yakin hapus user ini?",
      text: "Data user akan dihapus permanen!",
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

  // --- Tambah User: show/hide satker based on role ---
  function toggleTambahSatker() {
    var role = $("#tambah_role").val();
    var $group = $("#tambah_satker_group");
    var $select = $("#satker_id");
    if (role === "admin") {
      $group.hide();
      $select.removeAttr("required").val("");
    } else {
      $group.show();
      $select.attr("required", "required");
    }
  }

  // Run on modal open and on role change
  $("#tambahUserModal").on("show.bs.modal", function () {
    toggleTambahSatker();
  });
  $("#tambah_role").on("change", function () {
    toggleTambahSatker();
    // clear invalid state when switching
    $("#satker_id").removeClass("is-invalid");
  });

  // --- Edit User: show/hide satker based on role ---
  $(document).on("change", ".edit-role-select", function () {
    var userId = $(this).data("user-id");
    var role = $(this).val();
    var $group = $("#edit_satker_group_" + userId);
    var $select = $("#satker_id_" + userId);
    if (role === "admin") {
      $group.hide();
      $select.removeAttr("required").val("");
    } else {
      $group.show();
      $select.attr("required", "required");
    }
  });

  $('form[action$="addUser"]').on("submit", function (e) {
    var role = $("#tambah_role").val();
    var valid = true;
    if (role !== "admin") {
      var satker = $("#satker_id", this);
      if (!satker.val()) {
        satker.addClass("is-invalid");
        valid = false;
      } else {
        satker.removeClass("is-invalid");
      }
    }
    if (!valid) {
      e.preventDefault();
      e.stopPropagation();
    }
  });
  $("#satker_id").on("change", function () {
    if ($(this).val()) {
      $(this).removeClass("is-invalid");
    }
  });
});
