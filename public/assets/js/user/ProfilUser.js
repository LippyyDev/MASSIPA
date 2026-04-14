(function () {
  try {
    var mode = localStorage.getItem("theme-mode");
    if (
      mode === "dark" ||
      (!mode && window.matchMedia("(prefers-color-scheme: dark)").matches)
    ) {
      document.documentElement.classList.add("dark-mode");
    }
  } catch (e) {}
})();

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

document.addEventListener("DOMContentLoaded", function () {
  const profileImageWrapper = document.querySelector(".profile-image-wrapper");
  const profileImage = document.getElementById("profileImageUser");
  const fileInput = document.getElementById("foto_profil");
  const fotoForm = document.getElementById("fotoForm");

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

  const profileForm = document.getElementById("profileForm");
  if (profileForm) {
    profileForm.addEventListener("submit", function (e) {
      const nama = document.getElementById("nama_lengkap").value.trim();
      const email = document.getElementById("email").value.trim();
      const username = document.getElementById("username").value.trim();

      if (!nama || !email || !username) {
        e.preventDefault();
        Swal.fire({
          icon: "warning",
          title: "Peringatan!",
          text: "Semua field profil harus diisi!",
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
          profileForm.submit();
        }
      });
    });
  }

  const passwordForm = document.getElementById("passwordForm");
  if (passwordForm) {
    passwordForm.addEventListener("submit", function (e) {
      const passwordLama = document
        .getElementById("password_lama")
        .value.trim();
      const passwordBaru = document
        .getElementById("password_baru")
        .value.trim();
      const konfirmasiPassword = document
        .getElementById("konfirmasi_password")
        .value.trim();

      if (!passwordLama || !passwordBaru || !konfirmasiPassword) {
        e.preventDefault();
        Swal.fire({
          icon: "warning",
          title: "Peringatan!",
          text: "Semua field password harus diisi!",
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
          passwordForm.submit();
        }
      });
    });
  }

  const passwordBaruInput = document.getElementById("password_baru");
  if (passwordBaruInput) {
    passwordBaruInput.addEventListener("input", function () {
      const passwordBaru = this.value;
      const konfirmasiPassword = document.getElementById(
        "konfirmasi_password",
      ).value;

      if (passwordBaru.length > 0 && passwordBaru.length < 6) {
        this.classList.add("is-invalid");
        if (
          !this.nextElementSibling ||
          !this.nextElementSibling.classList.contains("invalid-feedback")
        ) {
          this.insertAdjacentHTML(
            "afterend",
            '<div class="invalid-feedback">Password minimal 6 karakter</div>',
          );
        }
      } else {
        this.classList.remove("is-invalid");
        const invalidFeedback = this.nextElementSibling;
        if (
          invalidFeedback &&
          invalidFeedback.classList.contains("invalid-feedback")
        ) {
          invalidFeedback.remove();
        }
      }

      if (konfirmasiPassword.length > 0) {
        const konfirmasiInput = document.getElementById("konfirmasi_password");
        if (passwordBaru !== konfirmasiPassword) {
          konfirmasiInput.classList.add("is-invalid");
          if (
            !konfirmasiInput.nextElementSibling ||
            !konfirmasiInput.nextElementSibling.classList.contains(
              "invalid-feedback",
            )
          ) {
            konfirmasiInput.insertAdjacentHTML(
              "afterend",
              '<div class="invalid-feedback">Password tidak cocok</div>',
            );
          }
        } else {
          konfirmasiInput.classList.remove("is-invalid");
          const invalidFeedback = konfirmasiInput.nextElementSibling;
          if (
            invalidFeedback &&
            invalidFeedback.classList.contains("invalid-feedback")
          ) {
            invalidFeedback.remove();
          }
        }
      }
    });
  }

  const konfirmasiPasswordInput = document.getElementById(
    "konfirmasi_password",
  );
  if (konfirmasiPasswordInput) {
    konfirmasiPasswordInput.addEventListener("input", function () {
      const passwordBaru = document.getElementById("password_baru").value;
      const konfirmasiPassword = this.value;

      if (passwordBaru.length > 0 && konfirmasiPassword.length > 0) {
        if (passwordBaru !== konfirmasiPassword) {
          this.classList.add("is-invalid");
          if (
            !this.nextElementSibling ||
            !this.nextElementSibling.classList.contains("invalid-feedback")
          ) {
            this.insertAdjacentHTML(
              "afterend",
              '<div class="invalid-feedback">Password tidak cocok</div>',
            );
          }
        } else {
          this.classList.remove("is-invalid");
          const invalidFeedback = this.nextElementSibling;
          if (
            invalidFeedback &&
            invalidFeedback.classList.contains("invalid-feedback")
          ) {
            invalidFeedback.remove();
          }
        }
      } else {
        this.classList.remove("is-invalid");
        const invalidFeedback = this.nextElementSibling;
        if (
          invalidFeedback &&
          invalidFeedback.classList.contains("invalid-feedback")
        ) {
          invalidFeedback.remove();
        }
      }
    });
  }
});
