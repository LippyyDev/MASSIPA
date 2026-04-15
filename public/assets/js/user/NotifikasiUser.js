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
function loadNotifikasiUser() {
    $.ajax({
        url: (window.BASE_URL || '') + 'user/notifikasiuser/getNotifikasiAjax',
        type: 'POST',
        data: {
            [CSRF_TOKEN_NAME]: CSRF_HASH
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

                // Pasang event klik untuk mark-as-read (setelah render)
                bindNotifClickEvents();
            } else {
                // Tampilkan pesan kosong
                $('#notifikasiKosong').removeClass('d-none');
            }
        },
        error: function(xhr, status, error) {
            $('#notifikasiSkeleton').hide();
            $('#notifikasiKosong').removeClass('d-none');
            console.error('Gagal memuat notifikasi:', error);
        }
    });
}

/**
 * Pasang event klik pada item notifikasi untuk mark-as-read
 */
function bindNotifClickEvents() {
    $(document).on('click', '.notification-item', function() {
        var notifId  = $(this).data('notif-id');
        var isUnread = $(this).hasClass('unread');
        if (isUnread) {
            $.ajax({
                url: (window.BASE_URL || '') + 'user/notifikasiuser/mark-read',
                type: 'POST',
                data: {
                    notif_id: notifId,
                    [CSRF_TOKEN_NAME]: CSRF_HASH
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('[data-notif-id="' + notifId + '"]').removeClass('unread');
                    }
                },
                error: function() {
                    console.log('Gagal menandai notifikasi sebagai sudah dibaca');
                }
            });
        }
    });
}

$(document).ready(function() {
    // Load data saat halaman siap
    loadNotifikasiUser();

    // Tombol hapus semua
    $('#btnHapusSemua').on('click', function() {
        $.ajax({
            url: (window.BASE_URL || '') + 'user/notifikasiuser/delete-all',
            type: 'POST',
            data: {
                [CSRF_TOKEN_NAME]: CSRF_HASH
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