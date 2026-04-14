const BASE_URL_REKAP_BULANAN_AJAX = '/user/getRekapBulananAjax';

$(document).ready(function() {
    const nama_bulan_singkat = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    let currentPage = 1;
    let currentLength = 10;
    let currentSearch = '';
    let currentTahun = $('#tahun').val();
    let allData = [];
    let filteredData = [];

    function extractNamaPegawai(namaNipHtml) {
        if (!namaNipHtml) return '';
        const temp = $('<div>').html(namaNipHtml);
        const text = temp.text() || '';
        const parts = text.split('NIP:');
        return parts[0] ? parts[0].trim() : text.trim();
    }

    function buildMutasiNote(namaPegawai, rawHtml, options = {}) {
        if (!rawHtml) return '';
        let content = rawHtml.trim();
        if (options.removePegawaiPrefix) {
            content = content.replace(/^Pegawai\s+telah\s+/i, '');
        }
        const nama = namaPegawai || 'Pegawai';
        return `<span class="cell-mutasi-info">${nama} ${content}</span>`;
    }

    function renderMobileCards(data, startIndex) {
        const container = $('#mobileCardsContainer');
        if (!data || data.length === 0) {
            container.html('<div class="text-center text-muted py-4">Tidak ada data</div>');
            return;
        }

        let html = '';
        data.forEach((row, idx) => {
            const no = startIndex + idx + 1;
            let namaNip = row.nama_nip || '-';
            let pangkatGolongan = row.pangkat_golongan || '-';
            let jabatan = row.jabatan || '-';
            
            if (namaNip.includes('<')) {
                const $temp = $('<div>').html(namaNip);
                namaNip = $temp.text() || namaNip;
            }
            
            let namaDisplay = namaNip;
            if (namaNip.includes('NIP:')) {
                const parts = namaNip.split('NIP:');
                const nama = parts[0].trim();
                const nip = parts[1] ? 'NIP: ' + parts[1].trim() : '';
                namaDisplay = nama + (nip ? '<br><small class="text-muted">' + nip + '</small>' : '');
            }
            if (pangkatGolongan.includes('<')) {
                const $temp = $('<div>').html(pangkatGolongan);
                pangkatGolongan = $temp.text() || pangkatGolongan;
            }
            if (jabatan.includes('<')) {
                const $temp = $('<div>').html(jabatan);
                jabatan = $temp.text() || jabatan;
            }
            
            let bulanHtml = '';
            let skip = 0;
            let bulanItems = [];
            
            for (let i = 1; i <= 12; i++) {
                if (skip > 0) { skip--; continue; }
                
                if (row.mutasi_bulan && row.mutasi_bulan == i && row.keterangan_mutasi) {
                    const colspan = 13 - i + 1;
                    bulanItems.push({
                        type: 'mutasi-keluar',
                        content: row.keterangan_mutasi,
                        colspan: colspan
                    });
                    skip = colspan - 1;
                    continue;
                }
                
                if (row.keterangan_sebelum_mutasi && row.bulan_sebelum_mutasi && i == 1) {
                    const colspan = row.bulan_sebelum_mutasi > 0 ? row.bulan_sebelum_mutasi : 1;
                    bulanItems.push({
                        type: 'mutasi-masuk',
                        content: row.keterangan_sebelum_mutasi,
                        colspan: colspan
                    });
                    skip = colspan - 1;
                    continue;
                }
                
                const hasData = row.kedisiplinan_per_bulan && row.kedisiplinan_per_bulan[i] ? true : false;
                bulanItems.push({
                    type: 'bulan',
                    bulan: nama_bulan_singkat[i-1],
                    hasData: hasData
                });
            }
            
            let currentRow = '';
            let bulanCount = 0;
            bulanItems.forEach((item, idx) => {
                if (item.type === 'mutasi-keluar' || item.type === 'mutasi-masuk') {
                    if (currentRow) {
                        bulanHtml += `<div class="row g-2 mb-2">${currentRow}</div>`;
                        currentRow = '';
                        bulanCount = 0;
                    }
                    bulanHtml += `<div class="alert alert-${item.type === 'mutasi-keluar' ? 'mutasi-keluar' : 'mutasi-masuk'} mb-2 mt-2" style="font-size: 0.85em; padding: 10px;">${item.content}</div>`;
                } else {
                    const statusIcon = item.hasData ? '<span class="text-success fw-bold">✓</span>' : '<span class="text-muted">-</span>';
                    currentRow += `
                        <div class="col-2">
                            <div class="text-center">
                                <div class="fw-bold small" style="font-size: 0.75rem;">${item.bulan}</div>
                                <div style="font-size: 0.85rem;">${statusIcon}</div>
                            </div>
                        </div>
                    `;
                    bulanCount++;
                    
                    if (bulanCount === 6 || idx === bulanItems.length - 1) {
                        bulanHtml += `<div class="row g-2 mb-2">${currentRow}</div>`;
                        currentRow = '';
                        bulanCount = 0;
                    }
                }
            });

            let namaParsed = namaDisplay;
            let nipParsed = '';
            if (namaDisplay.includes('<br>')) {
                const parts = namaDisplay.split('<br>');
                namaParsed = parts[0].trim();
                if (parts[1]) {
                    nipParsed = parts[1].replace(/<small[^>]*>|<\/small>/g, '').trim();
                }
            } else if (namaDisplay.includes('NIP:')) {
                const parts = namaDisplay.split('NIP:');
                namaParsed = parts[0].trim();
                nipParsed = parts[1] ? 'NIP: ' + parts[1].trim() : '';
            }

            html += `
                <div class="border rounded mb-3 p-3 shadow-sm" style="background:#fff;">
                    <div class="fw-bold mb-1">No. ${no} - ${namaParsed}</div>
                    ${nipParsed ? `<div class="mb-1"><b>${nipParsed}</b></div>` : ''}
                    <div class="mb-1"><b>Pangkat / Golongan:</b> ${pangkatGolongan}</div>
                    <div class="mb-2"><b>Jabatan:</b> ${jabatan}</div>
                    <div class="border-top pt-2 mt-2">
                        <div class="fw-bold mb-2" style="font-size: 0.85rem;">Status Kedisiplinan per Bulan</div>
                        ${bulanHtml}
                    </div>
                </div>
            `;
        });

        container.html(html);
    }

    function loadMobileData() {
        const start = (currentPage - 1) * currentLength;
        const end = start + currentLength;
        const pageData = filteredData.slice(start, end);
        
        renderMobileCards(pageData, start);
        
        const total = filteredData.length;
        const totalPages = Math.ceil(total / currentLength);
        updateMobilePagination(total, totalPages);
    }

    function updateMobilePagination(total, totalPages) {
        const prevBtn = $('#mobilePegawaiPrev');
        const nextBtn = $('#mobilePegawaiNext');
        const pagination = $('#mobilePegawaiPagination');
        
        if (total > currentLength) {
            pagination.show();

            var pageNumbersHtml = '';
            var maxPages = Math.min(3, totalPages);
            var actualStart = Math.max(1, currentPage - 1);
            var actualEnd = Math.min(totalPages, actualStart + maxPages - 1);

            if (actualEnd - actualStart < maxPages - 1) {
                actualStart = Math.max(1, actualEnd - maxPages + 1);
            }

            for (var i = actualStart; i <= actualEnd; i++) {
                var activeClass = i === currentPage ? 'active' : '';
                pageNumbersHtml += '<li class="page-item" style="display: inline-block;"><button class="page-link page-number ' + activeClass + '" data-page="' + i + '">' + i + '</button></li>';
            }

            $('#mobilePegawaiPageNumbers').html(pageNumbersHtml);

            if (currentPage === 1) {
                prevBtn.prop('disabled', true).addClass('disabled');
            } else {
                prevBtn.prop('disabled', false).removeClass('disabled');
            }

            if (currentPage === totalPages) {
                nextBtn.prop('disabled', true).addClass('disabled');
            } else {
                nextBtn.prop('disabled', false).removeClass('disabled');
            }
        } else {
            pagination.hide();
        }
    }

    function fetchData(callback) {
        $.ajax({
            url: BASE_URL_REKAP_BULANAN_AJAX,
            type: 'GET',
            data: {
                tahun: currentTahun,
                start: 0,
                length: 10000,
                search: { value: currentSearch }
            },
            success: function(response) {
                allData = response.data || [];
                if (currentSearch) {
                    filteredData = allData.filter(row => {
                        const searchLower = currentSearch.toLowerCase();
                        return (row.nama_nip && row.nama_nip.toLowerCase().includes(searchLower)) ||
                               (row.pangkat_golongan && row.pangkat_golongan.toLowerCase().includes(searchLower)) ||
                               (row.jabatan && row.jabatan.toLowerCase().includes(searchLower));
                    });
                } else {
                    filteredData = allData;
                }
                if (callback) callback();
            },
            error: function() {
                $('#mobileCardsContainer').html('<div class="text-center text-danger py-4">Gagal memuat data</div>');
            }
        });
    }

    function setFixedDimensions() {
        const isMobile = window.innerWidth <= 991;
        const table = $('#rekapTable');
        const tableWrapper = $('.table-responsive');
        
        if (!isMobile) {
            tableWrapper.css({
                'min-height': '400px',
                'contain': 'layout'
            });
            table.css({
                'min-width': '1050px',
                'table-layout': 'fixed'
            });
        }
    }
    
    setFixedDimensions();
    
    $(window).on('resize', function() {
        setFixedDimensions();
        if (window.innerWidth <= 991) {
            fetchData(loadMobileData);
        }
    });
    
    var table = $('#rekapTable').DataTable({
        "language": {
            "emptyTable": "Tidak ada data yang tersedia",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
            "infoFiltered": "(disaring dari _MAX_ entri keseluruhan)",
            "lengthMenu": "Tampilkan _MENU_ entri",
            "loadingRecords": "Sedang memuat...",
            "processing": "Sedang memproses...",
            "search": "Cari:",
            "zeroRecords": "Tidak ditemukan data yang sesuai",
            "paginate": {
                "first": "",
                "last": "",
                "next": "&gt;",
                "previous": "&lt;",
            },
            "aria": {
                "sortAscending": ": aktifkan untuk mengurutkan kolom naik",
                "sortDescending": ": aktifkan untuk mengurutkan kolom turun",
            },
        },
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": BASE_URL_REKAP_BULANAN_AJAX,
            "type": "GET",
            "data": function(d) {
                d.tahun = $('#tahun').val();
            }
        },
        "pageLength": 10,
        "info": false,
        "order": [[0, "asc"]],
        "autoWidth": false,
        "drawCallback": function (settings) {
            if (window.innerWidth <= 767) {
                var $pagination = $(".dataTables_paginate .pagination");
                var $pageItems = $pagination.find(
                    ".page-item:not(.previous):not(.next)"
                );
                var $active = $pagination.find(".page-item.active");
                var activeIndex = $pageItems.index($active);

                $pageItems.each(function (index) {
                    var $item = $(this);
                    var distance = Math.abs(index - activeIndex);
                    if (distance <= 1) {
                        $item.show();
                    } else {
                        $item.hide();
                    }
                });
            }
        },
        "columns": [
            { "data": null, "orderable": false, "searchable": false },
            { "data": "nama_nip" },
            { "data": "jabatan" },
            { "data": null, "orderable": false, "searchable": false },
            { "data": null, "orderable": false, "searchable": false },
            { "data": null, "orderable": false, "searchable": false },
            { "data": null, "orderable": false, "searchable": false },
            { "data": null, "orderable": false, "searchable": false },
            { "data": null, "orderable": false, "searchable": false },
            { "data": null, "orderable": false, "searchable": false },
            { "data": null, "orderable": false, "searchable": false },
            { "data": null, "orderable": false, "searchable": false },
            { "data": null, "orderable": false, "searchable": false },
            { "data": null, "orderable": false, "searchable": false },
            { "data": null, "orderable": false, "searchable": false }
        ],
        "createdRow": function(row, data, dataIndex) {
            var pageInfo = this.api().page.info();
            $('td:eq(0)', row).html(pageInfo.start + dataIndex + 1);
        },
        "rowCallback": function(row, data, dataIndex) {
            var $row = $(row);
            $row.find('td').slice(3).remove();
            var html = '';
            for (var i = 1; i <= 12; i++) {
                var d = data.kedisiplinan_per_bulan[i] ? 1 : 0;
                html += d === 1
                    ? '<td class="text-center"><span class="text-success font-weight-bold">✓</span></td>'
                    : '<td class="text-center text-muted">-</td>';
            }
            $row.find('td').eq(2).after(html);

            var notes = [];
            var namaPegawai = extractNamaPegawai(data.nama_nip);
            if (data.keterangan_sebelum_mutasi) {
                notes.push(buildMutasiNote(namaPegawai, data.keterangan_sebelum_mutasi));
            }
            if (data.keterangan_mutasi) {
                notes.push(buildMutasiNote(namaPegawai, data.keterangan_mutasi, { removePegawaiPrefix: true }));
            }

            if (notes.length) {
                $row.data('mutasi-note', notes.join(''));
            } else {
                $row.removeData('mutasi-note');
            }
        },
        "drawCallback": function(settings) {
            setFixedDimensions();
            var api = this.api();
            var colCount = api.columns().count();
            var $tbody = $('#rekapTable tbody');
            $tbody.find('.mutasi-note-row').remove();
            $tbody.find('tr').each(function() {
                var $currentRow = $(this);
                var noteHtml = $currentRow.data('mutasi-note');
                if (noteHtml) {
                    var noteRow = `<tr class="mutasi-note-row"><td colspan="${colCount}"><div class="mutasi-note-wrapper">${noteHtml}</div></td></tr>`;
                    $currentRow.after(noteRow);
                }
            });
        }
    });
    
    $('#tahun').on('change', function() {
        currentTahun = $(this).val();
        $('#tahunDisplay').text(currentTahun);
        
        if (window.innerWidth <= 991) {
            currentPage = 1;
            fetchData(loadMobileData);
        } else {
            table.ajax.reload();
        }
    });

    let searchTimeout;
    $('#mobileSearch').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            currentSearch = $('#mobileSearch').val();
            currentPage = 1;
            fetchData(loadMobileData);
        }, 300);
    });

    $(document).on('click', '#mobilePegawaiPrev:not(:disabled)', function(e) {
        e.preventDefault();
        if (currentPage > 1) {
            currentPage--;
            loadMobileData();
        }
    });

    $(document).on('click', '#mobilePegawaiNext:not(:disabled)', function(e) {
        e.preventDefault();
        const totalPages = Math.ceil(filteredData.length / currentLength);
        if (currentPage < totalPages) {
            currentPage++;
            loadMobileData();
        }
    });

    $(document).on('click', '.page-number', function(e) {
        e.preventDefault();
        currentPage = parseInt($(this).data('page'));
        loadMobileData();
    });

    if (window.innerWidth <= 991) {
        fetchData(loadMobileData);
    }

    table.on('draw', function() {
        if (window.innerWidth <= 991) {
            fetchData(loadMobileData);
        }
    });
    
});