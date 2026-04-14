document.addEventListener("DOMContentLoaded", function () {
  if (window.Swal && window.flashMsgType && window.flashMsg) {
    Swal.fire({
      icon: window.flashMsgType === "success" ? "success" : "error",
      title: window.flashMsgType === "success" ? "Berhasil" : "Gagal",
      text: window.flashMsg,
      timer: 2500,
      showConfirmButton: false,
      confirmButtonColor: "#7c3aed",
    });
  }
});

$(document).ready(function () {
  $("#passwordForm").on("submit", function (e) {
    const passwordLama = $("#password_lama").val().trim();
    const passwordBaru = $("#password_baru").val().trim();
    const konfirmasiPassword = $("#konfirmasi_password").val().trim();

    if (!passwordLama || !passwordBaru || !konfirmasiPassword) {
      e.preventDefault();
      Swal.fire({
        icon: "warning",
        title: "Peringatan!",
        text: "Semua field password harus diisi!",
        confirmButtonText: "OK",
        confirmButtonColor: "#7c3aed",
      });
      return false;
    }

    if (passwordBaru !== konfirmasiPassword) {
      e.preventDefault();
      Swal.fire({
        icon: "error",
        title: "Password Tidak Cocok!",
        text: "Password baru dan konfirmasi password harus sama!",
        confirmButtonText: "OK",
        confirmButtonColor: "#7c3aed",
      });
      return false;
    }

    if (passwordBaru.length < 6) {
      e.preventDefault();
      Swal.fire({
        icon: "warning",
        title: "Password Terlalu Pendek!",
        text: "Password baru minimal harus 6 karakter!",
        confirmButtonText: "OK",
        confirmButtonColor: "#7c3aed",
      });
      return false;
    }

    e.preventDefault();
    Swal.fire({
      icon: "question",
      title: "Konfirmasi Ubah Password",
      text: "Apakah Anda yakin ingin mengubah password?",
      showCancelButton: true,
      confirmButtonText: "Ya, Ubah Password",
      cancelButtonText: "Batal",
      confirmButtonColor: "#7c3aed",
      cancelButtonColor: "#6c757d",
    }).then((result) => {
      if (result.isConfirmed) {
        $("#passwordForm")[0].submit();
      }
    });
  });

  $("#password_baru").on("input", function () {
    const passwordBaru = $(this).val();
    const konfirmasiPassword = $("#konfirmasi_password").val();

    if (passwordBaru.length > 0 && passwordBaru.length < 6) {
      $(this).addClass("is-invalid");
      if (!$(this).next(".invalid-feedback").length) {
        $(this).after(
          '<div class="invalid-feedback">Password minimal 6 karakter</div>',
        );
      }
    } else {
      $(this).removeClass("is-invalid");
      $(this).next(".invalid-feedback").remove();
    }

    if (konfirmasiPassword.length > 0) {
      if (passwordBaru !== konfirmasiPassword) {
        $("#konfirmasi_password").addClass("is-invalid");
        if (!$("#konfirmasi_password").next(".invalid-feedback").length) {
          $("#konfirmasi_password").after(
            '<div class="invalid-feedback">Password tidak cocok</div>',
          );
        }
      } else {
        $("#konfirmasi_password").removeClass("is-invalid");
        $("#konfirmasi_password").next(".invalid-feedback").remove();
      }
    }
  });

  $("#konfirmasi_password").on("input", function () {
    const passwordBaru = $("#password_baru").val();
    const konfirmasiPassword = $(this).val();

    if (passwordBaru.length > 0 && konfirmasiPassword.length > 0) {
      if (passwordBaru !== konfirmasiPassword) {
        $(this).addClass("is-invalid");
        if (!$(this).next(".invalid-feedback").length) {
          $(this).after(
            '<div class="invalid-feedback">Password tidak cocok</div>',
          );
        }
      } else {
        $(this).removeClass("is-invalid");
        $(this).next(".invalid-feedback").remove();
      }
    } else {
      $(this).removeClass("is-invalid");
      $(this).next(".invalid-feedback").remove();
    }
  });

  $('form[action$="admin/profil/update"]').on("submit", function (e) {
    if (!$(this).is("#passwordForm")) {
      const nama = $("#nama_lengkap").val().trim();
      const email = $("#email").val().trim();
      const username = $("#username").val().trim();
      if (!nama || !email || !username) {
        e.preventDefault();
        Swal.fire({
          icon: "warning",
          title: "Peringatan!",
          text: "Semua field profil harus diisi!",
          confirmButtonText: "OK",
          confirmButtonColor: "#7c3aed",
        });
        return false;
      }
      e.preventDefault();
      Swal.fire({
        icon: "question",
        title: "Konfirmasi Simpan Profil",
        text: "Apakah Anda yakin ingin menyimpan perubahan profil?",
        showCancelButton: true,
        confirmButtonText: "Ya, Simpan",
        cancelButtonText: "Batal",
        confirmButtonColor: "#7c3aed",
        cancelButtonColor: "#6c757d",
      }).then((result) => {
        if (result.isConfirmed) {
          $(this)[0].submit();
        }
      });
    }
  });

  const profileImageWrapper = document.querySelector(".profile-image-wrapper");
  const profileImage = document.getElementById("profileImageAdmin");
  const fileInput = document.getElementById("foto_profil");
  const fotoForm = document.getElementById("fotoFormAdmin");

  if (profileImageWrapper && fileInput && fotoForm && profileImage) {
    const originalImageSrc = profileImage.src;

    profileImageWrapper.addEventListener("click", function () {
      fileInput.click();
    });

    fileInput.addEventListener("change", function (e) {
      if (this.files.length === 0) {
        return;
      }

      const file = this.files[0];
      const maxSize = 1 * 1024 * 1024;
      const allowedTypes = [
        "image/jpeg",
        "image/jpg",
        "image/png",
        "image/webp",
        "image/bmp",
        "image/svg+xml",
        "image/tiff",
        "image/x-icon",
        "image/x-ico",
      ];
      const fileName = file.name.toLowerCase();
      const fileExt = fileName.substring(fileName.lastIndexOf(".") + 1);

      if (file.type === "image/gif" || fileExt === "gif") {
        Swal.fire({
          icon: "error",
          title: "Format File Tidak Diizinkan!",
          text: "File GIF tidak diperbolehkan. Gunakan format gambar lain (JPG, PNG, WEBP, BMP, SVG, TIFF, ICO)",
          confirmButtonColor: "#7c3aed",
        });
        this.value = "";
        return false;
      }

      if (file.size > maxSize) {
        Swal.fire({
          icon: "error",
          title: "File Terlalu Besar!",
          text: "Ukuran foto profil maksimal 1MB. Silakan pilih file yang lebih kecil.",
          confirmButtonColor: "#7c3aed",
        });
        this.value = "";
        return false;
      }

      if (!allowedTypes.includes(file.type)) {
        Swal.fire({
          icon: "error",
          title: "Format File Tidak Didukung!",
          text: "Format file tidak didukung. Gunakan JPG, PNG, WEBP, BMP, SVG, TIFF, atau ICO (kecuali GIF)",
          confirmButtonColor: "#7c3aed",
        });
        this.value = "";
        return false;
      }

      const reader = new FileReader();
      reader.onload = function (e) {
        profileImage.src = e.target.result;
      };
      reader.readAsDataURL(file);

      Swal.fire({
        icon: "question",
        title: "Konfirmasi Update Foto",
        text: "Apakah Anda yakin ingin mengubah foto profil?",
        showCancelButton: true,
        confirmButtonText: "Ya, Update Foto",
        cancelButtonText: "Batal",
        confirmButtonColor: "#7c3aed",
        cancelButtonColor: "#6c757d",
      }).then((result) => {
        if (result.isConfirmed) {
          fotoForm.submit();
        } else {
          fileInput.value = "";
          profileImage.src = originalImageSrc;
        }
      });
    });

    fotoForm.addEventListener("submit", function (e) {
      if (!fileInput || fileInput.files.length === 0) {
        e.preventDefault();
        return false;
      }

      const file = fileInput.files[0];
      const maxSize = 1 * 1024 * 1024;
      const allowedTypes = [
        "image/jpeg",
        "image/jpg",
        "image/png",
        "image/webp",
        "image/bmp",
        "image/svg+xml",
        "image/tiff",
        "image/x-icon",
        "image/x-ico",
      ];
      const fileName = file.name.toLowerCase();
      const fileExt = fileName.substring(fileName.lastIndexOf(".") + 1);

      if (file.type === "image/gif" || fileExt === "gif") {
        e.preventDefault();
        Swal.fire({
          icon: "error",
          title: "Format File Tidak Diizinkan!",
          text: "File GIF tidak diperbolehkan. Gunakan format gambar lain (JPG, PNG, WEBP, BMP, SVG, TIFF, ICO)",
          confirmButtonColor: "#7c3aed",
        });
        return false;
      }

      if (file.size > maxSize) {
        e.preventDefault();
        Swal.fire({
          icon: "error",
          title: "File Terlalu Besar!",
          text: "Ukuran foto profil maksimal 1MB. Silakan pilih file yang lebih kecil.",
          confirmButtonColor: "#7c3aed",
        });
        return false;
      }

      if (!allowedTypes.includes(file.type)) {
        e.preventDefault();
        Swal.fire({
          icon: "error",
          title: "Format File Tidak Didukung!",
          text: "Format file tidak didukung. Gunakan JPG, PNG, WEBP, BMP, SVG, TIFF, atau ICO (kecuali GIF)",
          confirmButtonColor: "#7c3aed",
        });
        return false;
      }
    });
  }
});
