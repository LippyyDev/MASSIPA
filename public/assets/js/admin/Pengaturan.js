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
    if ($('#apiKeyTable').length) {
        $('#apiKeyTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "scrollX": true,
            "pageLength": 10,
            "lengthChange": false,
            "pagingType": "simple_numbers",
            "language": {
                "emptyTable": "Tidak ada data yang tersedia",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ entri",
                "paginate": {
                    "first": "",
                    "last": "",
                    "next": "",
                    "previous": ""
                }
            },
            "drawCallback": function(settings) {
                $(this.api().table().container()).find('.dataTables_paginate .paginate_button.previous, .dataTables_paginate .paginate_button.next').hide();
            }
        });
    }

    if ($('#originTable').length) {
        $('#originTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "scrollX": true,
            "pageLength": 10,
            "lengthChange": false,
            "pagingType": "simple_numbers",
            "language": {
                "emptyTable": "Tidak ada data yang tersedia",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ entri",
                "paginate": {
                    "first": "",
                    "last": "",
                    "next": "",
                    "previous": ""
                }
            },
            "drawCallback": function(settings) {
                $(this.api().table().container()).find('.dataTables_paginate .paginate_button.previous, .dataTables_paginate .paginate_button.next').hide();
            }
        });
    }

    $('.btn-copy-key').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const apiKey = $(this).data('key');
        navigator.clipboard.writeText(apiKey).then(function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'API Key telah disalin ke clipboard',
                timer: 1500,
                showConfirmButton: false
            });
        }).catch(function(err) {
            const textArea = document.createElement('textarea');
            textArea.value = apiKey;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'API Key telah disalin ke clipboard',
                timer: 1500,
                showConfirmButton: false
            });
        });
    });

    $('.btn-delete-key').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const deleteUrl = $(this).data('url');
        
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus API Key ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = deleteUrl;
            }
        });
    });

    $('.btn-delete-origin').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const deleteUrl = $(this).attr('href');
        
        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Apakah Anda yakin ingin menghapus domain ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = deleteUrl;
            }
        });
    });

    if (window.flashMessage) {
        Swal.fire({
            icon: window.flashMessageType || 'success',
            title: 'Info',
            text: window.flashMessage,
            timer: 2000,
            showConfirmButton: false
        });
    }

    var mobilePageApiKey = 1;
    var mobileLengthApiKey = 10;
    var allApiKeyCards = $('.pengaturan-card-mobile[data-type="apikey"]').toArray();
    
    function loadMobileApiKeyCards() {
        var start = (mobilePageApiKey - 1) * mobileLengthApiKey;
        var end = start + mobileLengthApiKey;
        var cards = allApiKeyCards.slice(start, end);
        
        $('.pengaturan-card-mobile[data-type="apikey"]').hide();
        $(cards).show();
        
        var total = allApiKeyCards.length;
        var totalPages = Math.ceil(total / mobileLengthApiKey);
        
        if (totalPages > 1) {
            $('#mobilePaginationApiKey').show();
            
            var pageNumbersHtml = '';
            var startPage = Math.max(1, mobilePageApiKey - 1);
            var endPage = Math.min(totalPages, startPage + 2);
            
            if (endPage - startPage < 2) {
                startPage = Math.max(1, endPage - 2);
            }
            
            for (var i = startPage; i <= endPage; i++) {
                var activeClass = (i === mobilePageApiKey) ? 'active' : '';
                pageNumbersHtml += '<li class="page-item"><button class="page-link page-number ' + activeClass + '" data-page="' + i + '">' + i + '</button></li>';
            }
            
            $('#mobilePageNumbersApiKey').html(pageNumbersHtml);
            $('#mobilePrevApiKey').prop('disabled', mobilePageApiKey === 1);
            $('#mobileNextApiKey').prop('disabled', mobilePageApiKey === totalPages);
            
            $('#mobilePaginationApiKey .page-number').off('click').on('click', function() {
                mobilePageApiKey = parseInt($(this).data('page'));
                loadMobileApiKeyCards();
            });
        } else {
            $('#mobilePaginationApiKey').hide();
        }
    }
    
    $('#mobilePrevApiKey').on('click', function() {
        if (mobilePageApiKey > 1) {
            mobilePageApiKey--;
            loadMobileApiKeyCards();
        }
    });
    
    $('#mobileNextApiKey').on('click', function() {
        var totalPages = Math.ceil(allApiKeyCards.length / mobileLengthApiKey);
        if (mobilePageApiKey < totalPages) {
            mobilePageApiKey++;
            loadMobileApiKeyCards();
        }
    });
    
    var mobilePageOrigin = 1;
    var mobileLengthOrigin = 10;
    var allOriginCards = $('.pengaturan-card-mobile[data-type="origin"]').toArray();
    
    function loadMobileOriginCards() {
        var start = (mobilePageOrigin - 1) * mobileLengthOrigin;
        var end = start + mobileLengthOrigin;
        var cards = allOriginCards.slice(start, end);
        
        $('.pengaturan-card-mobile[data-type="origin"]').hide();
        $(cards).show();
        
        var total = allOriginCards.length;
        var totalPages = Math.ceil(total / mobileLengthOrigin);
        
        if (totalPages > 1) {
            $('#mobilePaginationOrigin').show();
            
            var pageNumbersHtml = '';
            var startPage = Math.max(1, mobilePageOrigin - 1);
            var endPage = Math.min(totalPages, startPage + 2);
            
            if (endPage - startPage < 2) {
                startPage = Math.max(1, endPage - 2);
            }
            
            for (var i = startPage; i <= endPage; i++) {
                var activeClass = (i === mobilePageOrigin) ? 'active' : '';
                pageNumbersHtml += '<li class="page-item"><button class="page-link page-number ' + activeClass + '" data-page="' + i + '">' + i + '</button></li>';
            }
            
            $('#mobilePageNumbersOrigin').html(pageNumbersHtml);
            $('#mobilePrevOrigin').prop('disabled', mobilePageOrigin === 1);
            $('#mobileNextOrigin').prop('disabled', mobilePageOrigin === totalPages);
            
            $('#mobilePaginationOrigin .page-number').off('click').on('click', function() {
                mobilePageOrigin = parseInt($(this).data('page'));
                loadMobileOriginCards();
            });
        } else {
            $('#mobilePaginationOrigin').hide();
        }
    }
    
    $('#mobilePrevOrigin').on('click', function() {
        if (mobilePageOrigin > 1) {
            mobilePageOrigin--;
            loadMobileOriginCards();
        }
    });
    
    $('#mobileNextOrigin').on('click', function() {
        var totalPages = Math.ceil(allOriginCards.length / mobileLengthOrigin);
        if (mobilePageOrigin < totalPages) {
            mobilePageOrigin++;
            loadMobileOriginCards();
        }
    });
    
    if (window.innerWidth < 768) {
        allApiKeyCards = $('.pengaturan-card-mobile[data-type="apikey"]').toArray();
        allOriginCards = $('.pengaturan-card-mobile[data-type="origin"]').toArray();
        
        if (allApiKeyCards.length > 0) {
            loadMobileApiKeyCards();
        }
        if (allOriginCards.length > 0) {
            loadMobileOriginCards();
        }
    }
}); 