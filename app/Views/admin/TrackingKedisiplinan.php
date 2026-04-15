<!--
    Author: MUHAMMAD ALIF QADRI 2025
    Licensed to: PTA MAKASSAR DAN SELURUH JAJARANNYA
    Copyright (c) 2025
-->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - MASSIPA</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/img/Icon/logo_fav.png') ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/sidebar_styles.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin/TrackingKedisiplinan.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components/petunjuk_styles.css') ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <meta name="color-scheme" content="dark light">
    <script>
        (function () {
            try {
                var mode = localStorage.getItem('theme-mode');
                if (
                    mode === 'dark' ||
                    (!mode && window.matchMedia('(prefers-color-scheme: dark)').matches)
                ) {
                    document.documentElement.classList.add('dark-mode');
                }
            } catch (e) { }
        })();
    </script>

</head>

<body>
    <?= $this->include('components/sidebar_admin') ?>

    <?= $this->include('components/navbar_admin') ?>

    <div class="main-content">
        <div class="overlay"></div>
        <div class="container-fluid px-0">
            <div class="row mb-0">
                <div class="col-12">
                    <?php
                    $isiPetunjuk = '<ul class="mb-0 ps-3" style="font-size:0.9em;">
                        <li><i class="bi bi-search"></i> Gunakan kolom pencarian untuk mencari pegawai berdasarkan nama atau NIP.</li>
                        <li><i class="bi bi-calendar"></i> Filter tahun memungkinkan Anda melihat data kedisiplinan pada periode tertentu.</li>
                        <li><i class="bi bi-chart-line"></i> Statistik menampilkan ringkasan pelanggaran kedisiplinan pegawai yang dipilih.</li>
                        <li><i class="bi bi-table"></i> Tabel track record menampilkan detail pelanggaran per bulan dan tahun.</li>
                        <li><i class="bi bi-info-circle"></i> <b>T</b>=Terlambat, <b>TAM</b>=Tidak Absen Masuk, <b>PA</b>=Pulang Awal, <b>TAP</b>=Tidak Absen Pulang</li>
                        <li><i class="bi bi-info-circle"></i> <b>KTI</b>=Keluar Tanpa Izin, <b>TMK</b>=Tidak Masuk Kantor, <b>TMS</b>=Tidak Masuk Sidang, <b>TMK</b>=Tidak Masuk Kerja</li>
                    </ul>';
                    echo view('components/petunjuk', ['isiPetunjuk' => $isiPetunjuk]);
                    ?>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header">Pencarian Pegawai</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="search_pegawai" class="form-label">Cari Nama atau NIP Pegawai</label>
                            <div class="search-container">
                                <input type="text" class="form-control" id="search_pegawai" 
                                       placeholder="Ketik nama atau NIP pegawai..." autocomplete="off">
                                <div class="search-results" id="search_results"></div>
                                <div class="form-text">Ketik minimal 2 karakter untuk memulai pencarian</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3" id="pegawai_info" style="display: none;">
                <div class="card-header">
                    <span>Informasi Pegawai</span>
                </div>
                <div class="card-body" id="pegawai_detail">
                </div>
            </div>

            <div class="card mb-3" id="statistics_section" style="display: none;">
                <div class="card-header">Statistik Kedisiplinan</div>
                <div class="card-body text-center">
                    <div class="row g-0 justify-content-center mb-2 mb-md-3">
                        <div class="col-6 col-md-3 col-lg-3 mb-2 mb-md-0 px-1">
                            <div class="text-center">
                                <small class="text-muted d-block">Terlambat</small>
                                <h5 class="mb-0" id="stat_terlambat_value">0</h5>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg-3 mb-2 mb-md-0 px-1">
                            <div class="text-center">
                                <small class="text-muted d-block">Tidak Absen Masuk</small>
                                <h5 class="mb-0" id="stat_tidak_absen_masuk_value">0</h5>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg-3 mb-2 mb-md-0 px-1">
                            <div class="text-center">
                                <small class="text-muted d-block">Pulang Awal</small>
                                <h5 class="mb-0" id="stat_pulang_awal_value">0</h5>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg-3 mb-2 mb-md-0 px-1">
                            <div class="text-center">
                                <small class="text-muted d-block">Tidak Absen Pulang</small>
                                <h5 class="mb-0" id="stat_tidak_absen_pulang_value">0</h5>
                            </div>
                        </div>
                    </div>
                    <div class="row g-0 justify-content-center">
                        <div class="col-6 col-md-3 col-lg-3 mb-2 mb-md-0 px-1">
                            <div class="text-center">
                                <small class="text-muted d-block">Keluar Tidak Izin</small>
                                <h5 class="mb-0" id="stat_keluar_tidak_izin_value">0</h5>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg-3 mb-2 mb-md-0 px-1">
                            <div class="text-center">
                                <small class="text-muted d-block">Tidak Masuk Tanpa Keterangan</small>
                                <h5 class="mb-0" id="stat_tidak_masuk_tanpa_ket_value">0</h5>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg-3 mb-2 mb-md-0 px-1">
                            <div class="text-center">
                                <small class="text-muted d-block">Tidak Masuk Sakit</small>
                                <h5 class="mb-0" id="stat_tidak_masuk_sakit_value">0</h5>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-lg-3 mb-2 mb-md-0 px-1">
                            <div class="text-center">
                                <small class="text-muted d-block">Tidak Masuk Kerja</small>
                                <h5 class="mb-0" id="stat_tidak_masuk_kerja_value">0</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3" id="track_record_section" style="display: none;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Track Record Kedisiplinan</span>
                    <div class="d-flex align-items-center">
                        <select class="form-select" id="filter_tahun" style="width: 160px; min-width: 140px;">
                            <option value="">Semua Tahun</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-striped table-hover modern-table" id="track_record_table">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Bulan/Tahun</th>
                                    <th>Satker</th>
                                    <th>T</th>
                                    <th>TAM</th>
                                    <th>PA</th>
                                    <th>TAP</th>
                                    <th>KTI</th>
                                    <th>TMK</th>
                                    <th>TMS</th>
                                    <th>TMK</th>
                                    <th>Pembinaan</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="track_record_tbody">
                            </tbody>
                        </table>
                    </div>

                    <div class="d-block d-md-none">
                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label for="search_mobile_tracking" class="form-label">Cari berdasarkan bulan/tahun</label>
                                <div class="search-container">
                                    <input type="text" id="search_mobile_tracking" class="form-control"
                                        placeholder="Cari berdasarkan bulan/tahun..." autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div id="mobileTrackingCards"></div>
                        <div id="mobilePagination" style="display:none;">
                            <ul class="pagination mobile-pagination-purple mb-0" style="justify-content: center; gap: 8px;">
                                <li class="page-item">
                                    <button class="page-link" id="mobilePrev" type="button">&lt;</button>
                                </li>
                                <span id="mobilePageNumbers"></span>
                                <li class="page-item">
                                    <button class="page-link" id="mobileNext" type="button">&gt;</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3" id="no_data_section">
                <div class="card-body">
                    <div class="no-data text-center">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <h4>Belum Ada Data</h4>
                        <p>Silakan pilih pegawai untuk melihat track record kedisiplinan</p>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <script>
        let selectedPegawaiId = null;
        let selectedTahun = null;
        let debounceTimer = null;
        let trackRecordTable = null;
        let mobileData = [];
        let currentMobilePage = 1;
        let mobilePageSize = 10;
        let filteredMobileData = [];

        const SEARCH_PEGAWAI_URL = '<?= base_url('admin/tracking/searchPegawaiAjax') ?>';
        const GET_TRACK_RECORD_URL = '<?= base_url('admin/tracking/getTrackRecordAjax') ?>';
        const GET_TAHUN_URL = '<?= base_url('admin/tracking/getTahunTersediaAjax') ?>';

        $(document).ready(function() {
            initializeEventListeners();
            showNoDataSection();
        });

        function initializeEventListeners() {
            $('#search_pegawai').on('input', function() {
                const query = $(this).val().trim();
                clearTimeout(debounceTimer);
                
                if (query.length < 2) {
                    $('#search_results').hide();
                    return;
                }
                
                debounceTimer = setTimeout(() => {
                    searchPegawai(query);
                }, 300);
            });

            $('#filter_tahun').on('change', function() {
                selectedTahun = $(this).val();
                if (selectedPegawaiId) {
                    loadTrackRecord();
                }
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.search-container').length) {
                    $('#search_results').hide();
                }
            });

            $('#search_mobile_tracking').on('input', function() {
                searchMobileData();
            });

            $('#mobilePrev').on('click', function() {
                if (currentMobilePage > 1) {
                    currentMobilePage--;
                    renderMobileCards();
                }
            });

            $('#mobileNext').on('click', function() {
                const totalPages = Math.ceil(filteredMobileData.length / mobilePageSize);
                if (currentMobilePage < totalPages) {
                    currentMobilePage++;
                    renderMobileCards();
                }
            });
        }

        function searchPegawai(query) {
            $.ajax({
                url: SEARCH_PEGAWAI_URL,
                type: 'POST',
                data: { q: query, limit: 10 },
                dataType: 'json'
            })
                .done(function(data) {
                    displaySearchResults(data);
                })
                .fail(function() {
                    $('#search_results').html('<div class="search-item text-muted">Terjadi kesalahan saat mencari data</div>').show();
                });
        }

        function displaySearchResults(results) {
            let html = '';
            
            if (results && results.length > 0) {
                results.forEach(function(pegawai) {
                    html += `
                        <div class="search-item" data-id="${pegawai.id}" data-nama="${pegawai.nama}" data-nip="${pegawai.nip}">
                            <div class="fw-bold">${pegawai.nama}</div>
                            <div class="text-muted small">
                                NIP: ${pegawai.nip} | ${pegawai.jabatan || 'Jabatan tidak tersedia'}
                            </div>
                        </div>
                    `;
                });
            } else {
                html = '<div class="no-result-item text-muted">Tidak ada hasil ditemukan</div>';
            }
            
            $('#search_results').html(html).show();
            
            $('.search-item').on('click', function() {
                const id = $(this).data('id');
                const nama = $(this).data('nama');
                const nip = $(this).data('nip');
                
                if (!id) return;
                selectPegawai(id, nama, nip);
            });
        }

        function selectPegawai(id, nama, nip) {
            selectedPegawaiId = id;
            selectedTahun = '';
            $('#search_pegawai').val(`${nama} (${nip})`);
            $('#search_results').hide();
            $('#filter_tahun').val('');
            
            loadTahunTersedia();
            loadPegawaiInfo();
        }

        function loadPegawaiInfo() {
            fetchTrackingData(true);
        }

        function loadTrackRecord() {
            fetchTrackingData(false);
        }

        function fetchTrackingData(includePegawaiInfo = false) {
            if (!selectedPegawaiId) return;
            
            showLoading();
            
            $.ajax({
                url: GET_TRACK_RECORD_URL,
                type: 'POST',
                data: { pegawai_id: selectedPegawaiId, tahun: selectedTahun },
                dataType: 'json'
            })
                .done(function(response) {
                    if (response.success) {
                        if (includePegawaiInfo && response.data.pegawai) {
                            displayPegawaiInfo(response.data.pegawai);
                            $('#pegawai_info').show();
                        }
                        
                        displayStatistics(response.data.statistik);
                        displayTrackRecord(response.data.track_record);
                        
                        $('#statistics_section').show();
                        $('#track_record_section').show();
                        $('#no_data_section').hide();
                    } else {
                        showError(response.message);
                    }
                })
                .fail(function(xhr, status, error) {
                    showError('Terjadi kesalahan saat memuat track record: ' + error);
                })
                .always(function() {
                    hideLoading();
                });
        }

        function displayPegawaiInfo(pegawai) {
            const html = `
                <div class="row">
                    <div class="col-md-12">
                        <h6 class="mb-2"><strong>${pegawai.nama}</strong></h6>
                        <p class="mb-1"><strong>NIP:</strong> ${pegawai.nip}</p>
                        <p class="mb-1"><strong>Jabatan:</strong> ${pegawai.jabatan || 'Tidak tersedia'}</p>
                        <p class="mb-1"><strong>Pangkat/Golongan:</strong> ${pegawai.pangkat || 'Tidak tersedia'} ${pegawai.golongan || ''}</p>
                        <p class="mb-0"><strong>Satker:</strong> ${pegawai.satker_nama || 'Tidak tersedia'}</p>
                        <span class="badge bg-${pegawai.status == 'aktif' ? 'success' : 'secondary'} badge-pegawai-status">${pegawai.status == 'aktif' ? 'Aktif' : 'Nonaktif'}</span>
                    </div>
                </div>
            `;
            $('#pegawai_detail').html(html);
        }

        function displayStatistics(statistik) {
            $('#stat_terlambat_value').text(statistik.terlambat || 0);
            $('#stat_tidak_absen_masuk_value').text(statistik.tidak_absen_masuk || 0);
            $('#stat_pulang_awal_value').text(statistik.pulang_awal || 0);
            $('#stat_tidak_absen_pulang_value').text(statistik.tidak_absen_pulang || 0);
            $('#stat_keluar_tidak_izin_value').text(statistik.keluar_tidak_izin || 0);
            $('#stat_tidak_masuk_tanpa_ket_value').text(statistik.tidak_masuk_tanpa_ket || 0);
            $('#stat_tidak_masuk_sakit_value').text(statistik.tidak_masuk_sakit || 0);
            $('#stat_tidak_masuk_kerja_value').text(statistik.tidak_masuk_kerja || 0);
        }

        function displayTrackRecord(trackRecord) {
            let html = '';
            
            const hasData = Array.isArray(trackRecord) && trackRecord.length > 0;

            if (hasData) {
                trackRecord.forEach(function(record, index) {
                    const bulanTahun = getBulanName(record.bulan) + ' ' + record.tahun;
                    const totalPelanggaran = (record.terlambat || 0) + (record.tidak_absen_masuk || 0) + 
                                          (record.pulang_awal || 0) + (record.tidak_absen_pulang || 0) + 
                                          (record.keluar_tidak_izin || 0) + (record.tidak_masuk_tanpa_ket || 0) + 
                                          (record.tidak_masuk_sakit || 0) + (record.tidak_masuk_kerja || 0);
                    
                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${bulanTahun}</td>
                            <td>${record.satker_nama || 'Tidak tersedia'}</td>
                            <td class="text-dark">${record.terlambat || 0}</td>
                            <td class="text-dark">${record.tidak_absen_masuk || 0}</td>
                            <td class="text-dark">${record.pulang_awal || 0}</td>
                            <td class="text-dark">${record.tidak_absen_pulang || 0}</td>
                            <td class="text-dark">${record.keluar_tidak_izin || 0}</td>
                            <td class="text-dark">${record.tidak_masuk_tanpa_ket || 0}</td>
                            <td class="text-dark">${record.tidak_masuk_sakit || 0}</td>
                            <td class="text-dark">${record.tidak_masuk_kerja || 0}</td>
                            <td>${record.bentuk_pembinaan || '-'}</td>
                            <td>${record.keterangan || '-'}</td>
                        </tr>
                    `;
                });
            } else {
                html = '';
            }
            
            if (trackRecordTable) {
                trackRecordTable.destroy();
                trackRecordTable = null;
            }

            $('#track_record_tbody').html(html);
            
            mobileData = trackRecord || [];
            filteredMobileData = [...mobileData];
            currentMobilePage = 1;
            renderMobileCards();
            
            trackRecordTable = $('#track_record_table').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[1, 'desc']],
                pagingType: 'simple_numbers',
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json',
                    paginate: {
                        previous: '<',
                        next: '>'
                    }
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-7"p>>',
                info: false,
                drawCallback: function() {
                    $('.dataTables_wrapper .dataTables_paginate .page-item.previous .page-link').html('<');
                    $('.dataTables_wrapper .dataTables_paginate .page-item.next .page-link').html('>');
                }
            });

            if (!hasData) {
                trackRecordTable.clear().draw();
            }
        }

        function loadTahunTersedia() {
            if (!selectedPegawaiId) return;
            
            $.ajax({
                url: GET_TAHUN_URL,
                type: 'POST',
                data: { pegawai_id: selectedPegawaiId },
                dataType: 'json'
            })
                .done(function(response) {
                    let html = '<option value="">Semua Tahun</option>';
                    
                    if (response.success && Array.isArray(response.data) && response.data.length > 0) {
                        response.data.forEach(function(tahun) {
                            const tahunValue = tahun.tahun || tahun;
                            html += `<option value="${tahunValue}">${tahunValue}</option>`;
                        });
                    } else {
                        html = '<option value="">Semua Tahun</option>';
                    }
                    
                    $('#filter_tahun').html(html);
                    $('#filter_tahun').val(selectedTahun || '');
                })
                .fail(function(xhr, status, error) {
                    $('#filter_tahun').html('<option value="">Semua Tahun</option>');
                    $('#filter_tahun').val('');
                });
        }


        function getBulanName(bulan) {
            const bulanNames = [
                '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            return bulanNames[bulan] || 'Tidak valid';
        }

        function showLoading() {
            if (trackRecordTable) {
                trackRecordTable.destroy();
                trackRecordTable = null;
            }

            $('#track_record_tbody').html('<tr><td colspan="13" class="text-center"><div class="spinner-border spinner-border-sm me-2" role="status"></div> Memuat data...</td></tr>');
        }

        function hideLoading() {
        }

        function showNoDataSection() {
            $('#pegawai_info').hide();
            $('#statistics_section').hide();
            $('#track_record_section').hide();
            $('#no_data_section').show();
        }

        function showError(message) {
            alert('Error: ' + message);
        }

        function renderMobileCards() {
            const startIndex = (currentMobilePage - 1) * mobilePageSize;
            const endIndex = startIndex + mobilePageSize;
            const pageData = filteredMobileData.slice(startIndex, endIndex);

            let cardsHtml = "";

            if (pageData.length === 0) {
                cardsHtml = '<div class="text-center text-muted p-4">Tidak ada data</div>';
            } else {
                pageData.forEach((record, index) => {
                    const actualIndex = startIndex + index + 1;
                    const bulanTahun = getBulanName(record.bulan) + ' ' + record.tahun;
                    const totalPelanggaran = (record.terlambat || 0) + (record.tidak_absen_masuk || 0) + 
                                          (record.pulang_awal || 0) + (record.tidak_absen_pulang || 0) + 
                                          (record.keluar_tidak_izin || 0) + (record.tidak_masuk_tanpa_ket || 0) + 
                                          (record.tidak_masuk_sakit || 0) + (record.tidak_masuk_kerja || 0);
                    
                    cardsHtml += `
                        <div class="border rounded mb-3 p-3 shadow-sm" style="background:#fff;">
                            <div class="fw-bold mb-1">No. ${actualIndex} - ${bulanTahun}</div>
                            <div class="mb-2"><b>Satker:</b> ${record.satker_nama || 'Tidak tersedia'}</div>
                            
                            <div class="row g-2 mb-3">
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="fw-bold small mobile-pelanggaran-label">T</div>
                                        <div class="pelanggaran-number ${getPelanggaranClass(record.terlambat || 0)}">${record.terlambat || 0}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="fw-bold small mobile-pelanggaran-label">TAM</div>
                                        <div class="pelanggaran-number ${getPelanggaranClass(record.tidak_absen_masuk || 0)}">${record.tidak_absen_masuk || 0}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="fw-bold small mobile-pelanggaran-label">PA</div>
                                        <div class="pelanggaran-number ${getPelanggaranClass(record.pulang_awal || 0)}">${record.pulang_awal || 0}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="fw-bold small mobile-pelanggaran-label">TAP</div>
                                        <div class="pelanggaran-number ${getPelanggaranClass(record.tidak_absen_pulang || 0)}">${record.tidak_absen_pulang || 0}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="fw-bold small mobile-pelanggaran-label">KTI</div>
                                        <div class="pelanggaran-number ${getPelanggaranClass(record.keluar_tidak_izin || 0)}">${record.keluar_tidak_izin || 0}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="fw-bold small mobile-pelanggaran-label">TK</div>
                                        <div class="pelanggaran-number ${getPelanggaranClass(record.tidak_masuk_tanpa_ket || 0)}">${record.tidak_masuk_tanpa_ket || 0}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="fw-bold small mobile-pelanggaran-label">TMS</div>
                                        <div class="pelanggaran-number ${getPelanggaranClass(record.tidak_masuk_sakit || 0)}">${record.tidak_masuk_sakit || 0}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="text-center">
                                        <div class="fw-bold small mobile-pelanggaran-label">TMK</div>
                                        <div class="pelanggaran-number ${getPelanggaranClass(record.tidak_masuk_kerja || 0)}">${record.tidak_masuk_kerja || 0}</div>
                                    </div>
                                </div>
                            </div>
                            
                            ${record.bentuk_pembinaan ? `<div class="mt-2"><b>Pembinaan:</b> ${record.bentuk_pembinaan}</div>` : ''}
                            ${record.keterangan ? `<div class="mt-1"><b>Keterangan:</b> ${record.keterangan}</div>` : ''}
                        </div>
                    `;
                });
            }

            $("#mobileTrackingCards").html(cardsHtml);
            updateMobilePagination();
        }

        function getPelanggaranClass(count) {
            if (count > 5) return "high";
            if (count > 2) return "medium";
            if (count > 0) return "low";
            return "zero";
        }

        function updateMobilePagination() {
            const totalPages = Math.ceil(filteredMobileData.length / mobilePageSize);

            if (totalPages > 1) {
                $("#mobilePagination").show();

                var pageNumbersHtml = "";
                var startPage = Math.max(1, currentMobilePage - 1);
                var endPage = Math.min(totalPages, startPage + 2);

                if (endPage - startPage < 2) {
                    startPage = Math.max(1, endPage - 2);
                }

                for (var i = startPage; i <= endPage; i++) {
                    var activeClass = i === currentMobilePage ? "active" : "";
                    pageNumbersHtml +=
                        '<li class="page-item"><button class="page-link page-number ' +
                        activeClass +
                        '" data-page="' +
                        i +
                        '">' +
                        i +
                        "</button></li>";
                }

                $("#mobilePageNumbers").html(pageNumbersHtml);
                $("#mobilePrev").prop("disabled", currentMobilePage === 1);
                $("#mobileNext").prop("disabled", currentMobilePage === totalPages);

                $(".page-number")
                    .off("click")
                    .on("click", function () {
                        currentMobilePage = parseInt($(this).data("page"));
                        renderMobileCards();
                    });
            } else {
                $("#mobilePagination").hide();
                $("#mobilePageNumbers").empty();
            }
        }

        function searchMobileData() {
            const searchTerm = $("#search_mobile_tracking").val().toLowerCase();
            filteredMobileData = mobileData.filter((record) => {
                const bulanTahun = getBulanName(record.bulan) + ' ' + record.tahun;
                return bulanTahun.toLowerCase().includes(searchTerm) ||
                       (record.satker_nama && record.satker_nama.toLowerCase().includes(searchTerm));
            });
            currentMobilePage = 1;
            renderMobileCards();
        }
    </script>
    <?php include(APPPATH . 'Views/components/bottom_nav_admin.php'); ?>
</body>

</html>
