/**
 * Realtime Notifications Handler
 * Menangani notifikasi realtime untuk user dan admin
 */

// Mencegah deklarasi ganda
if (typeof RealtimeNotifications === "undefined") {
  class RealtimeNotifications {
    constructor() {
      this.pollingInterval = 5000; // 5 detik
      this.currentCount = 0;
      this.isPolling = false;
      this.baseUrl = window.BASE_URL || "";
      this.csrfHash = window.CSRF_HASH || "";

      this.init();
    }

    init() {
      // Mulai polling setelah halaman dimuat
      setTimeout(() => {
        this.startPolling();
      }, 2000);

      // Update badge saat halaman dimuat
      this.updateNotificationCount();
    }

    startPolling() {
      if (this.isPolling) return;

      this.isPolling = true;
      this.pollNotifications();
    }

    stopPolling() {
      this.isPolling = false;
    }

    async pollNotifications() {
      if (!this.isPolling) return;

      try {
        const response = await this.fetchNotificationCount();

        if (response.success) {
          const newCount = response.count;

          // Jika ada notifikasi baru
          if (newCount > this.currentCount) {
            const addedCount = newCount - this.currentCount;
            this.currentCount = newCount;
            this.updateNotificationBadge(newCount);
            this.showNotificationAlert(addedCount);

            // Update halaman notifikasi jika sedang dibuka
            this.updateNotificationPage();
          } else if (newCount !== this.currentCount) {
            this.currentCount = newCount;
            this.updateNotificationBadge(newCount);
          }
        }
      } catch (error) {
        console.error("Error polling notifications:", error);
      }

      // Lanjutkan polling
      setTimeout(() => {
        this.pollNotifications();
      }, this.pollingInterval);
    }

    async fetchNotificationCount() {
      const response = await fetch(`${this.baseUrl}api/notifications/count`, {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        credentials: "same-origin",
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      return await response.json();
    }

    async fetchLatestNotifications() {
      const response = await fetch(`${this.baseUrl}api/notifications/latest`, {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
        credentials: "same-origin",
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      return await response.json();
    }

    updateNotificationBadge(count) {
      const notificationLink = document.querySelector(
        '.navbar a[href*="notifikasi"]',
      );
      let badge = notificationLink
        ? notificationLink.querySelector(".badge")
        : null;

      if (count > 0) {
        if (badge) {
          badge.textContent = count;
          badge.style.display = "flex";
        } else if (notificationLink) {
          // Buat badge baru jika belum ada
          const newBadge = document.createElement("span");
          newBadge.className =
            "position-absolute badge bg-danger rounded-pill d-flex align-items-center justify-content-center";
          newBadge.style.cssText =
            "top:2px; left:85%; min-width:16px; height:16px; font-size:10px; padding:0 4px; color:#fff; line-height:16px; background-color:#dc3545 !important;";
          newBadge.textContent = count;
          notificationLink.appendChild(newBadge);
        }
      } else {
        if (badge) {
          badge.style.display = "none";
        }
      }
    }

    showNotificationAlert(newCount) {
      // Tampilkan notifikasi desktop jika diizinkan
      if ("Notification" in window && Notification.permission === "granted") {
        const notification = new Notification("Notifikasi Baru", {
          body: `Anda memiliki ${newCount} notifikasi baru`,
          icon: "/favicon.ico",
          badge: "/favicon.ico",
        });

        // Tutup notifikasi setelah 5 detik
        setTimeout(() => {
          notification.close();
        }, 5000);
      }

      // Tampilkan toast notification
      this.showToastNotification(newCount);
    }

    showToastNotification(newCount) {
      // Pastikan container ada
      let container = document.querySelector(".toast-notification-container");
      if (!container) {
        container = document.createElement("div");
        container.className = "toast-notification-container";
        document.body.appendChild(container);
      }

      // Tentukan URL notifikasi
      const notifUrl = `${this.baseUrl}${window.location.pathname.includes("admin") ? "admin/notifikasi" : "user/notifikasiuser"}`;

      // Buat toast element
      const toast = document.createElement("div");
      toast.className = "toast-notification";
      toast.innerHTML = `
            <div class="toast-notification-icon">
                <i class="bi bi-bell-fill"></i>
            </div>
            <div class="toast-notification-content">
                <p class="toast-notification-title">Notifikasi Baru</p>
                <p class="toast-notification-message">Anda Telah Menerima Notifikasi Terbaru.</p>
            </div>
            <button class="toast-notification-close" aria-label="Tutup">&times;</button>
        `;

      // Klik toast → navigasi ke halaman notifikasi
      toast.addEventListener("click", (e) => {
        if (!e.target.closest(".toast-notification-close")) {
          window.location.href = notifUrl;
        }
      });

      // Tombol tutup
      toast
        .querySelector(".toast-notification-close")
        .addEventListener("click", (e) => {
          e.stopPropagation();
          this.dismissToast(toast);
        });

      container.appendChild(toast);

      // Auto dismiss setelah 6 detik
      setTimeout(() => {
        this.dismissToast(toast);
      }, 6000);
    }

    dismissToast(toast) {
      if (!toast || toast.classList.contains("dismissing")) return;
      toast.classList.add("dismissing");
      setTimeout(() => {
        if (toast.parentNode) {
          toast.parentNode.removeChild(toast);
        }
      }, 400);
    }

    async updateNotificationPage() {
      // Update halaman notifikasi jika sedang dibuka
      const currentPath = window.location.pathname;
      if (currentPath.includes("notifikasi")) {
        try {
          const response = await this.fetchLatestNotifications();

          if (response.success && response.notifications.length > 0) {
            // Refresh halaman notifikasi
            window.location.reload();
          }
        } catch (error) {
          console.error("Error updating notification page:", error);
        }
      }
    }

    async updateNotificationCount() {
      try {
        const response = await this.fetchNotificationCount();

        if (response.success) {
          this.currentCount = response.count;
          this.updateNotificationBadge(response.count);
        }
      } catch (error) {
        console.error("Error updating notification count:", error);
      }
    }

    // Request permission untuk desktop notifications
    requestNotificationPermission() {
      if ("Notification" in window && Notification.permission === "default") {
        Notification.requestPermission();
      }
    }
  }

  // Inisialisasi notifikasi realtime saat DOM siap
  // Mencegah event listener ganda
  if (!window.domContentLoadedHandlerAdded) {
    document.addEventListener("DOMContentLoaded", function () {
      // Mencegah inisialisasi ganda
      if (window.realtimeNotifications) {
        return;
      }

      // Pastikan variabel global tersedia
      if (typeof BASE_URL === "undefined") {
        window.BASE_URL = "";
      }
      if (typeof CSRF_HASH === "undefined") {
        window.CSRF_HASH = "";
      }

      // Inisialisasi realtime notifications
      window.realtimeNotifications = new RealtimeNotifications();

      // Request permission untuk desktop notifications
      window.realtimeNotifications.requestNotificationPermission();
    });
    window.domContentLoadedHandlerAdded = true;
  }

  // Pause polling saat tab tidak aktif untuk menghemat resources
  // Mencegah event listener ganda
  if (!window.visibilityChangeHandlerAdded) {
    document.addEventListener("visibilitychange", function () {
      if (window.realtimeNotifications) {
        if (document.hidden) {
          window.realtimeNotifications.stopPolling();
        } else {
          window.realtimeNotifications.startPolling();
        }
      }
    });
    window.visibilityChangeHandlerAdded = true;
  }
}
