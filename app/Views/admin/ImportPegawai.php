<!-- ===== Modal Import Pegawai CSV ===== -->
<div class="modal fade" id="importPegawaiModal" tabindex="-1" aria-labelledby="importPegawaiModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <script>
                // Immediate fix for import modal aria-hidden
                $(document).ready(function() {
                    $('#importPegawaiModal').removeAttr('aria-hidden');
                    
                    // Watch for aria-hidden changes
                    var observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (mutation.type === 'attributes' && mutation.attributeName === 'aria-hidden') {
                                $('#importPegawaiModal').removeAttr('aria-hidden');
                            }
                        });
                    });
                    
                    observer.observe(document.getElementById('importPegawaiModal'), {
                        attributes: true,
                        attributeFilter: ['aria-hidden']
                    });
                });
            </script>
            <div class="modal-header">
                <h5 class="modal-title" id="importPegawaiModalLabel">
                    <i class="fas fa-upload me-2"></i>Import Data Pegawai via CSV
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="importForm" action="<?= base_url('admin/import_pegawai') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <!-- Step 1: File Selection -->
                    <div id="step1">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Petunjuk Import CSV:</h6>
                            <ul class="mb-0">
                                <li>File harus berformat CSV dengan encoding UTF-8</li>
                                <li>Kolom yang diperlukan: Nama, NIP, Pangkat, Golongan, Jabatan, Satker, TMT Mutasi</li>
                                <li>NIP akan digunakan sebagai identifier unik</li>
                                <li>Jika NIP sudah ada, data akan diupdate (nama, pangkat, golongan, jabatan)</li>
                                <li>Jika Satker berbeda, akan dibuat record mutasi baru</li>
                                <li>Jika semua data sama, akan di-skip</li>
                            </ul>
                        </div>
                        
                        <div class="mb-3">
                            <label for="file_csv" class="form-label">Pilih File CSV</label>
                            <input type="file" class="form-control" id="file_csv" name="file_csv" accept=".csv" required>
                            <div class="form-text">Maksimal ukuran file: 2MB</div>
                        </div>
                    </div>
                    
                    <!-- Step 2: Progress Display -->
                    <div id="step2" style="display: none;">
                        <div id="loadingSection" class="text-center mb-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memproses data...</p>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="progress mb-4">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                        
                        <!-- Progress Counters -->
                        <div class="row g-3 mb-4">
                            <div class="col">
                                <div class="card text-center h-100">
                                    <div class="card-body py-3">
                                        <h4 class="card-title mb-1 text-primary" id="totalProcessed">0</h4>
                                        <p class="card-text small mb-0">Diproses</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card text-center h-100">
                                    <div class="card-body py-3">
                                        <h4 class="card-title mb-1 text-success" id="totalUpdated">0</h4>
                                        <p class="card-text small mb-0">Diupdate</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card text-center h-100">
                                    <div class="card-body py-3">
                                        <h4 class="card-title mb-1 text-info" id="totalNew">0</h4>
                                        <p class="card-text small mb-0">Baru</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card text-center h-100">
                                    <div class="card-body py-3">
                                        <h4 class="card-title mb-1 text-warning" id="totalMutasi">0</h4>
                                        <p class="card-text small mb-0">Mutasi</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card text-center h-100">
                                    <div class="card-body py-3">
                                        <h4 class="card-title mb-1 text-secondary" id="totalSkip">0</h4>
                                        <p class="card-text small mb-0">Skip</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Current Processing -->
                        <div id="currentProcessing" class="mb-3">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-spinner fa-spin me-2"></i>Menunggu data...
                            </div>
                        </div>
                        
                        <!-- Recent Processed / Final Results -->
                        <div id="recentProcessed">
                            <div class="alert alert-light mb-0">
                                <span class="text-muted">Belum ada data yang diproses</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="cancelBtn" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="importBtn">
                        <i class="fas fa-upload me-1"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>