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
    var mobilePage = 1;
    var mobileLength = 10;
    var mutasiData = window.mutasiRiwayatData || [];
    var listSatker = window.mutasiListSatker || [];
    
    function getSatkerName(satkerId) {
        var satker = listSatker.find(function(s) { return s.id == satkerId; });
        return satker ? satker.nama : '-';
    }
    
    function loadMobileMutasiCards() {
        var start = (mobilePage - 1) * mobileLength;
        var end = start + mobileLength;
        var pageData = mutasiData.slice(start, end);
        var html = '';
        
        if (pageData.length === 0) {
            html = '<div class="text-center text-muted">Tidak ada data mutasi.</div>';
        } else {
            pageData.forEach(function(row, i) {
                var no = start + i + 1;
                var satkerName = getSatkerName(row.satker_id);
                var canDelete = no > 1;
                
                html += '<div class="border rounded mb-3 p-3 shadow-sm" style="background:#fff;">';
                html += '<div class="fw-bold mb-1">No. ' + no + ' - ' + satkerName + '</div>';
                html += '<div><b>Tanggal Mulai:</b> ' + row.tanggal_mulai + '</div>';
                html += '<div><b>Tanggal Selesai:</b> ' + (row.tanggal_selesai || '-') + '</div>';
                html += '<div class="mt-2 d-flex gap-2 flex-wrap">';
                html += '<button type="button" class="btn btn-warning btn-sm btn-edit-mutasi" data-bs-toggle="modal" data-bs-target="#editMutasiModal' + row.id + '">';
                html += '<i class="fas fa-edit"></i><span class="d-none d-md-inline ms-1">Edit</span></button>';
                if (canDelete) {
                    html += '<a href="' + window.baseUrl + 'admin/deleteMutasiPegawai/' + row.id + '" class="btn btn-danger btn-sm btn-hapus-mutasi" data-nama="' + satkerName + '">';
                    html += '<i class="fas fa-trash"></i><span class="d-none d-md-inline ms-1">Hapus</span></a>';
                }
                html += '</div></div>';
            });
        }
        
        $('#mobileMutasiCards').html(html);
        
        var total = mutasiData.length;
        var totalPages = Math.ceil(total / mobileLength);
        if (totalPages > 1) {
            $('#mobilePagination').show();
            
            var pageNumbersHtml = '';
            var startPage = Math.max(1, mobilePage - 1);
            var endPage = Math.min(totalPages, startPage + 2);
            
            if (endPage - startPage < 2) {
                startPage = Math.max(1, endPage - 2);
            }
            
            for (var i = startPage; i <= endPage; i++) {
                var activeClass = (i === mobilePage) ? 'active' : '';
                pageNumbersHtml += '<li class="page-item"><button class="page-link page-number ' + activeClass + '" data-page="' + i + '">' + i + '</button></li>';
            }
            
            $('#mobilePageNumbers').html(pageNumbersHtml);
            $('#mobilePrev').prop('disabled', mobilePage === 1);
            $('#mobileNext').prop('disabled', mobilePage === totalPages);
            
            $('.page-number').off('click').on('click', function() {
                mobilePage = parseInt($(this).data('page'));
                loadMobileMutasiCards();
            });
        } else {
            $('#mobilePagination').hide();
        }
    }
    
    $('#mobilePrev').off('click').on('click', function() {
        if (mobilePage > 1) {
            mobilePage--;
            loadMobileMutasiCards();
        }
    });
    
    $('#mobileNext').off('click').on('click', function() {
        var totalPages = Math.ceil(mutasiData.length / mobileLength);
        if (mobilePage < totalPages) {
            mobilePage++;
            loadMobileMutasiCards();
        }
    });
    
    if (window.innerWidth < 768) {
        loadMobileMutasiCards();
    }
    
    if (window.innerWidth >= 768) {
        $('#mutasiTable').DataTable({
            paging: true,
            searching: false,
            ordering: true,
            info: false,
            autoWidth: false,
            language: {
                emptyTable: "Tidak ada data yang tersedia",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
                infoFiltered: "(disaring dari _MAX_ entri keseluruhan)",
                lengthMenu: "Tampilkan _MENU_ entri",
                loadingRecords: "Sedang memuat...",
                processing: "Sedang memproses...",
                search: "Cari:",
                zeroRecords: "Tidak ditemukan data yang sesuai",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "&gt;",
                    previous: "&lt;"
                },
                aria: {
                    sortAscending: ": aktifkan untuk mengurutkan kolom naik",
                    sortDescending: ": aktifkan untuk mengurutkan kolom turun"
                }
            },
            drawCallback: function(settings) {
                if (window.innerWidth <= 767) {
                    var $pagination = $('.dataTables_paginate .pagination');
                    var $pageItems = $pagination.find('.page-item:not(.previous):not(.next)');
                    var $active = $pagination.find('.page-item.active');
                    var activeIndex = $pageItems.index($active);
                    
                    $pageItems.each(function(index) {
                        var $item = $(this);
                        var distance = Math.abs(index - activeIndex);
                        if (distance <= 1) {
                            $item.show();
                        } else {
                            $item.hide();
                        }
                    });
                }
            }
        });
    }

    $(document).on('click', '.btn-hapus-mutasi', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var nama = $(this).data('nama');
        Swal.fire({
            title: 'Yakin hapus riwayat mutasi?',
            text: 'Data mutasi satker "' + nama + '" akan dihapus permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });

    $('#toggleFormMutasi').on('click', function() {
        $('#formMutasiWrapper').slideToggle(200, function() {
            var $btn = $('#toggleFormMutasi');
            if ($(this).is(':visible')) {
                $btn.html('<i class="bi bi-chevron-up"></i><span class="d-none d-md-inline ms-1">Sembunyikan Form</span>');
            } else {
                $btn.html('<i class="bi bi-chevron-down"></i><span class="d-none d-md-inline ms-1">Tampilkan Form</span>');
            }
        });
    });

    function fixModalAccessibility() {
        $('.modal.show').removeAttr('inert').removeAttr('aria-hidden');
        
        $('.modal:not(.show)').attr('inert', '').removeAttr('aria-hidden');
        
        $('.modal-backdrop.show').removeAttr('aria-hidden');
        
        $('.modal-backdrop:not(.show)').attr('aria-hidden', 'true');
    }

    $(document).on('shown.bs.modal', function() {
        fixModalAccessibility();
    });

    $(document).on('hidden.bs.modal', function() {
        fixModalAccessibility();
    });

    setInterval(fixModalAccessibility, 500);

    $(window).on('load', function() {
        setTimeout(fixModalAccessibility, 100);
    });

    $(document).on('show.bs.modal', function(e) {
        $(e.target).removeAttr('inert').removeAttr('aria-hidden');
    });

    $(document).on('hide.bs.modal', function(e) {
        $(e.target).attr('inert', '').removeAttr('aria-hidden');
    });

    $(document).ready(function() {
        setTimeout(function() {
            fixModalAccessibility();
        }, 200);
    });
}); 