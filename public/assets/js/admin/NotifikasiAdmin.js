(function() {
    try {
        var mode = localStorage.getItem('theme-mode');
        if (
            mode === 'dark' ||
            (!mode && window.matchMedia('(prefers-color-scheme: dark)').matches)
        ) {
            document.documentElement.classList.add('dark-mode');
        }
    } catch(e) {}
})();

/**
 * Render satu item notifikasi menjadi HTML string
 */
function renderNotifItem(item) {
    var unreadClass = (item.is_read == 0) ? 'unread' : '';
    return '<a href="' + item.link + '"' +
        ' class="list-group-item list-group-item-action notification-item ' + unreadClass + '"' +
        ' data-notif-id="' + item.id + '">' +
            '<h5 class="mb-1 notification-title">' + item.judul + '</h5>' +
            '<small class="notification-time notification-time-top">' + item.time_display + '</small>' +
            '<p class="mb-1 notification-message">' + item.pesan + '</p>' +
            '<small class="notification-time">' + item.created_at + '</small>' +
        '</a>';
}

/**
 * Load data notifikasi via AJAX POST
 */
function loadNotifikasiAdmin() {
    $.ajax({
        url: window.baseUrl + 'admin/notifikasi/getNotifikasiAjax',
        type: 'POST',
        data: {
            [window.csrfToken]: window.csrfHash
        },
        dataType: 'json',
        success: function(response) {
            // Sembunyikan skeleton
            $('#notifikasiSkeleton').hide();

            if (response.success && response.total > 0) {
                // Render semua item
                var html = '';
                $.each(response.notifikasi, function(i, item) {
                    html += renderNotifItem(item);
                });
                $('#notifikasiList').html(html).removeClass('d-none');
                $('#btnHapusSemua').removeClass('d-none');
            } else {
                // Tampilkan pesan kosong
                $('#notifikasiKosong').removeClass('d-none');
            }

            // Update CSRF hash dari response header (jika ada)
            var newHash = response.csrf_hash;
            if (newHash) {
                window.csrfHash = newHash;
            }
        },
        error: function(xhr, status, error) {
            $('#notifikasiSkeleton').hide();
            $('#notifikasiKosong').removeClass('d-none');
            console.error('Gagal memuat notifikasi:', error);
        }
    });
}

$(document).ready(function() {
    // Load data saat halaman siap
    loadNotifikasiAdmin();

    // Tombol hapus semua
    $('#btnHapusSemua').on('click', function() {
        $.ajax({
            url: window.baseUrl + 'admin/notifikasi/delete-all',
            type: 'POST',
            data: {
                [window.csrfToken]: window.csrfHash
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('.notification-item').fadeOut(400, function() {
                        $(this).remove();
                        if ($('.notification-item').length === 0) {
                            $('#notifikasiList').addClass('d-none');
                            $('#notifikasiKosong').removeClass('d-none');
                        }
                    });
                    $('#btnHapusSemua').fadeOut(200);
                }
            },
            error: function() {
                console.error('Gagal menghapus notifikasi');
            }
        });
    });
});