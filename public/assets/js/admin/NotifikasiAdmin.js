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

$(document).ready(function() {
    $('#btnHapusSemua').on('click', function() {
        $.ajax({
            url: window.baseUrl + 'admin/notifikasi/delete-all',
            type: 'POST',
            data: {
                [window.csrfToken]: window.csrfHash
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