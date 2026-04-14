$(document).ready(function() {
    $('.notification-item').on('click', function() {
        var notifId = $(this).data('notif-id');
        var isUnread = $(this).hasClass('unread');
        if (isUnread) {
            $.ajax({
                url: BASE_URL + 'user/notifikasiuser/mark-read',
                type: 'POST',
                data: {
                    notif_id: notifId,
                    csrf_token_name: CSRF_HASH
                },
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

    $('#btnHapusSemua').on('click', function() {
        $.ajax({
            url: BASE_URL + 'user/notifikasiuser/delete-all',
            type: 'POST',
            data: {
                csrf_token_name: CSRF_HASH
            },
            success: function(response) {
                $('.notification-item').fadeOut(400, function() {
                    $(this).remove();
                    if ($('.notification-item').length === 0) {
                        $('#notifikasiList').after('<div class="text-center p-3" id="notifikasiKosong"><p>Tidak ada notifikasi</p><p>Anda belum memiliki notifikasi apapun saat ini.</p></div>');
                    }
                });
                $('#btnHapusSemua').fadeOut(200);
            }
        });
    });
}); 