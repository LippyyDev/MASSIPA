<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class KelolaDisiplinModel extends Model
{
    protected $table = 'kedisiplinan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'pegawai_id', 'riwayat_mutasi_id', 'bulan', 'tahun', 'terlambat', 'tidak_absen_masuk', 'pulang_awal', 'tidak_absen_pulang', 'keluar_tidak_izin', 'tidak_masuk_tanpa_ket', 'tidak_masuk_sakit', 'tidak_masuk_kerja', 'bentuk_pembinaan', 'keterangan', 'created_by'
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Mengambil data pelanggaran kedisiplinan dengan informasi pegawai dan satker
     * berdasarkan bulan dan tahun
     */
    public function getDisiplinData($bulan, $tahun, $start, $length, $searchValue, $orderColumn, $orderDir, $satker = null, $jenisPelanggaran = null)
    {

        
        $builder = $this->db->table('kedisiplinan k');
        $builder->select('
            k.id,
            k.pegawai_id,
            k.bulan,
            k.tahun,
            k.terlambat as T,
            k.tidak_absen_masuk as TAM,
            k.pulang_awal as PA,
            k.tidak_absen_pulang as TAP,
            k.keluar_tidak_izin as KTI,
            k.tidak_masuk_tanpa_ket as TK,
            k.tidak_masuk_sakit as TMS,
            k.tidak_masuk_kerja as TMK,
            p.nama as nama_pegawai,
            p.jabatan,
            p.nip,
            p.pangkat,
            COALESCE(s.nama, "Tidak Diketahui") as nama_satker,
            (k.terlambat + k.tidak_absen_masuk + k.pulang_awal + k.tidak_absen_pulang + k.keluar_tidak_izin + k.tidak_masuk_tanpa_ket + k.tidak_masuk_sakit + k.tidak_masuk_kerja) as total_pelanggaran
        ');
        
        // Join dengan tabel pegawai
        $builder->join('pegawai p', 'p.id = k.pegawai_id', 'inner');
        
        // Strategy 1: Try to use riwayat_mutasi_id if available
        // Strategy 2: Fall back to period-based matching if riwayat_mutasi_id is null
        $builder->join('riwayat_mutasi rm', 
            '(k.riwayat_mutasi_id IS NOT NULL AND rm.id = k.riwayat_mutasi_id) 
            OR (k.riwayat_mutasi_id IS NULL AND rm.pegawai_id = k.pegawai_id 
                AND rm.tanggal_mulai <= CONCAT(k.tahun, "-", LPAD(k.bulan, 2, "0"), "-01")
                AND (rm.tanggal_selesai IS NULL OR rm.tanggal_selesai >= LAST_DAY(CONCAT(k.tahun, "-", LPAD(k.bulan, 2, "0"), "-01"))))', 
            'left');
        
        // Join dengan satker
        $builder->join('satker s', 's.id = rm.satker_id', 'left');
        
        // Filter berdasarkan bulan dan tahun
        if ($bulan) {
            $builder->where('k.bulan', $bulan);
        }
        if ($tahun) {
            $builder->where('k.tahun', $tahun);
        }

        // Filter berdasarkan satker
        if ($satker && !empty($satker)) {
            $builder->where('s.id', $satker);
        }

        // Filter berdasarkan jenis pelanggaran
        if ($jenisPelanggaran && !empty($jenisPelanggaran)) {
            // Map field yang benar sesuai dengan database
            $fieldMap = [
                'T' => 'terlambat',
                'TAM' => 'tidak_absen_masuk',
                'PA' => 'pulang_awal',
                'TAP' => 'tidak_absen_pulang',
                'KTI' => 'keluar_tidak_izin',
                'TK' => 'tidak_masuk_tanpa_ket',
                'TMS' => 'tidak_masuk_sakit',
                'TMK' => 'tidak_masuk_kerja'
            ];
            
            if (isset($fieldMap[$jenisPelanggaran])) {
                $dbField = $fieldMap[$jenisPelanggaran];
                $builder->where("k.$dbField >", 0);
            }
        }
        
        // Check if table has any data first
        $hasData = $this->db->table('kedisiplinan')->countAllResults() > 0;
        if (!$hasData) {
            return [
                'data' => [],
                'total' => 0,
                'filtered' => 0
            ];
        }
        
        // Hanya tampilkan yang memiliki pelanggaran (total > 0)
        $builder->having('total_pelanggaran > 0');
        
        // Search functionality
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('p.nama', $searchValue)
                ->orLike('p.jabatan', $searchValue)
                ->orLike('s.nama', $searchValue)
                ->groupEnd();
        }
        
        // Count for filtered records - handle empty data gracefully
        // Use subquery to count correctly with HAVING clause
        // Clone builder BEFORE order by and limit are added, so no need to remove them
        try {
            $builderCount = clone $builder;
            // Get the query SQL and wrap it in a subquery for counting
            $sql = $builderCount->getCompiledSelect(false);
            // Execute count query using subquery
            $countQuery = $this->db->query("SELECT COUNT(*) as total FROM ($sql) as counted");
            $result = $countQuery->getRow();
            $filteredRecords = $result ? (int)$result->total : 0;
        } catch (\Exception $e) {
            log_message('error', 'Error counting filtered records: ' . $e->getMessage());
            // Fallback: try to get all data and count manually (less efficient but works)
            try {
                $builderCountFallback = clone $builder;
                $allData = $builderCountFallback->get()->getResultArray();
                $filteredRecords = count($allData);
            } catch (\Exception $e2) {
                log_message('error', 'Error in fallback count: ' . $e2->getMessage());
                $filteredRecords = 0;
            }
        }


        
        // Ordering
        if ($orderColumn !== null && $orderDir !== null) {
            $builder->orderBy($orderColumn, $orderDir);
        } else {
            $builder->orderBy('p.nama', 'ASC'); // Default order
        }
        
        // Pagination
        if ($length > 0) {
            $builder->limit($length, $start);
        }
        
        // Execute query safely
        try {
            $data = $builder->get()->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'Error executing main query: ' . $e->getMessage());
            $data = [];
        }
        
        // Count for total records (without search filter)
        $builderTotal = $this->db->table('kedisiplinan k');
        $builderTotal->select('k.id');
        $builderTotal->join('pegawai p', 'p.id = k.pegawai_id', 'inner');
        
        // Use same JOIN strategy for consistency
        $builderTotal->join('riwayat_mutasi rm', 
            '(k.riwayat_mutasi_id IS NOT NULL AND rm.id = k.riwayat_mutasi_id) 
            OR (k.riwayat_mutasi_id IS NULL AND rm.pegawai_id = k.pegawai_id 
                AND rm.tanggal_mulai <= CONCAT(k.tahun, "-", LPAD(k.bulan, 2, "0"), "-01")
                AND (rm.tanggal_selesai IS NULL OR rm.tanggal_selesai >= LAST_DAY(CONCAT(k.tahun, "-", LPAD(k.bulan, 2, "0"), "-01"))))', 
            'left');
        
        $builderTotal->join('satker s', 's.id = rm.satker_id', 'left');
        $builderTotal->select('(k.terlambat + k.tidak_absen_masuk + k.pulang_awal + k.tidak_absen_pulang + k.keluar_tidak_izin + k.tidak_masuk_tanpa_ket + k.tidak_masuk_sakit + k.tidak_masuk_kerja) as total_pelanggaran');
        
        if ($bulan) {
            $builderTotal->where('k.bulan', $bulan);
        }
        if ($tahun) {
            $builderTotal->where('k.tahun', $tahun);
        }

        // Apply same filters for total count
        if ($satker && !empty($satker)) {
            $builderTotal->where('s.id', $satker);
        }

        if ($jenisPelanggaran && !empty($jenisPelanggaran)) {
            // Map field yang benar sesuai dengan database
            $fieldMap = [
                'T' => 'terlambat',
                'TAM' => 'tidak_absen_masuk',
                'PA' => 'pulang_awal',
                'TAP' => 'tidak_absen_pulang',
                'KTI' => 'keluar_tidak_izin',
                'TK' => 'tidak_masuk_tanpa_ket',
                'TMS' => 'tidak_masuk_sakit',
                'TMK' => 'tidak_masuk_kerja'
            ];
            
            if (isset($fieldMap[$jenisPelanggaran])) {
                $dbField = $fieldMap[$jenisPelanggaran];
                $builderTotal->where("k.$dbField >", 0);
            }
        }
        
        $builderTotal->having('total_pelanggaran > 0');
        
        // Use subquery to count correctly with HAVING clause
        try {
            // Get the query SQL and wrap it in a subquery for counting
            $sql = $builderTotal->getCompiledSelect(false);
            // Execute count query using subquery
            $countQuery = $this->db->query("SELECT COUNT(*) as total FROM ($sql) as counted");
            $result = $countQuery->getRow();
            $totalRecords = $result ? (int)$result->total : 0;
        } catch (\Exception $e) {
            log_message('error', 'Error counting total records: ' . $e->getMessage());
            // Fallback: try to get all data and count manually (less efficient but works)
            try {
                $allData = $builderTotal->get()->getResultArray();
                $totalRecords = count($allData);
            } catch (\Exception $e2) {
                log_message('error', 'Error in fallback count: ' . $e2->getMessage());
                $totalRecords = 0;
            }
        }
        
        return [
            'data' => $data,
            'total' => $totalRecords,
            'filtered' => $filteredRecords
        ];
    }

    /**
     * Mengambil daftar tahun yang tersedia di data kedisiplinan
     */
    public function getDistinctYears()
    {
        return $this->distinct()
            ->select('tahun')
            ->orderBy('tahun', 'DESC')
            ->findAll();
    }

    /**
     * Mengambil daftar satker yang tersedia di data kedisiplinan
     */
    public function getDistinctSatkers()
    {
        // Method 1: Coba ambil dari riwayat_mutasi
        try {
            $builder = $this->db->table('satker s');
            $builder->select('s.id, s.nama')
                ->where('s.id IN (SELECT DISTINCT rm.satker_id FROM riwayat_mutasi rm WHERE rm.satker_id IS NOT NULL)')
                ->orderBy('s.nama', 'ASC');
            
            $result = $builder->get()->getResultArray();
            
            if (!empty($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in getDistinctSatkers method 1: ' . $e->getMessage());
        }
        
        // Method 2: Fallback - ambil semua satker
        try {
            $builder = $this->db->table('satker s');
            $builder->select('s.id, s.nama')
                ->orderBy('s.nama', 'ASC');
            
            $result = $builder->get()->getResultArray();
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Error in getDistinctSatkers method 2: ' . $e->getMessage());
            return [];
        }
    }


}
