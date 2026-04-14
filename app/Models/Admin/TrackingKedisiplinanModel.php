<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class TrackingKedisiplinanModel extends Model
{
    protected $table = 'kedisiplinan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'pegawai_id', 'riwayat_mutasi_id', 'bulan', 'tahun', 'terlambat', 'tidak_absen_masuk', 
        'pulang_awal', 'tidak_absen_pulang', 'keluar_tidak_izin', 'tidak_masuk_tanpa_ket', 
        'tidak_masuk_sakit', 'tidak_masuk_kerja', 'bentuk_pembinaan', 'keterangan', 'created_by'
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Mencari pegawai berdasarkan nama atau NIP untuk autocomplete
     */
    public function searchPegawai($query, $limit = 10)
    {
        $builder = $this->db->table('pegawai p');
        $builder->select('p.id, p.nama, p.nip, p.jabatan, p.pangkat, p.golongan');
        $builder->where('p.status', 'aktif');
        $builder->groupStart();
        $builder->like('p.nama', $query);
        $builder->orLike('p.nip', $query);
        $builder->groupEnd();
        $builder->limit($limit);
        
        return $builder->get()->getResultArray();
    }


    /**
     * Mendapatkan track record kedisiplinan pegawai berdasarkan ID pegawai
     */
    public function getTrackRecordPegawai($pegawai_id, $tahun = null)
    {
        $builder = $this->db->table('kedisiplinan k');
        $builder->select('
            k.id,
            k.bulan,
            k.tahun,
            k.terlambat,
            k.tidak_absen_masuk,
            k.pulang_awal,
            k.tidak_absen_pulang,
            k.keluar_tidak_izin,
            k.tidak_masuk_tanpa_ket,
            k.tidak_masuk_sakit,
            k.tidak_masuk_kerja,
            k.bentuk_pembinaan,
            k.keterangan,
            COALESCE(s.nama, "Tidak tersedia") as satker_nama,
            p.nama as pegawai_nama,
            p.nip as pegawai_nip,
            p.jabatan as pegawai_jabatan
        ');
        $builder->join('pegawai p', 'p.id = k.pegawai_id', 'left');
        
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
        
        $builder->where('k.pegawai_id', $pegawai_id);
        
        if ($tahun) {
            $builder->where('k.tahun', $tahun);
        }
        
        $builder->orderBy('k.tahun', 'DESC');
        $builder->orderBy('k.bulan', 'DESC');
        
        $result = $builder->get()->getResultArray();
        
        // Debug logging
        log_message('debug', 'getTrackRecordPegawai - Query result count: ' . count($result));
        if (!empty($result)) {
            log_message('debug', 'getTrackRecordPegawai - First record: ' . json_encode($result[0]));
        }
        
        return $result;
    }

    /**
     * Mendapatkan ringkasan pelanggaran per bulan untuk pegawai
     */
    public function getRingkasanPelanggaran($pegawai_id, $tahun = null)
    {
        $builder = $this->db->table('kedisiplinan k');
        $builder->select('
            k.bulan,
            k.tahun,
            SUM(k.terlambat) as total_terlambat,
            SUM(k.tidak_absen_masuk) as total_tidak_absen_masuk,
            SUM(k.pulang_awal) as total_pulang_awal,
            SUM(k.tidak_absen_pulang) as total_tidak_absen_pulang,
            SUM(k.keluar_tidak_izin) as total_keluar_tidak_izin,
            SUM(k.tidak_masuk_tanpa_ket) as total_tidak_masuk_tanpa_ket,
            SUM(k.tidak_masuk_sakit) as total_tidak_masuk_sakit,
            SUM(k.tidak_masuk_kerja) as total_tidak_masuk_kerja,
            COALESCE(s.nama, "Tidak tersedia") as satker_nama
        ');
        
        // Strategy 1: Try to use riwayat_mutasi_id if available
        // Strategy 2: Fall back to period-based matching if riwayat_mutasi_id is null
        $builder->join('riwayat_mutasi rm', 
            '(k.riwayat_mutasi_id IS NOT NULL AND rm.id = k.riwayat_mutasi_id) 
            OR (k.riwayat_mutasi_id IS NULL AND rm.pegawai_id = k.pegawai_id 
                AND rm.tanggal_mulai <= CONCAT(k.tahun, "-", LPAD(k.bulan, 2, "0"), "-01")
                AND (rm.tanggal_selesai IS NULL OR rm.tanggal_selesai >= LAST_DAY(CONCAT(k.tahun, "-", LPAD(k.bulan, 2, "0"), "-01"))))', 
            'left');
        
        $builder->join('satker s', 's.id = rm.satker_id', 'left');
        $builder->where('k.pegawai_id', $pegawai_id);
        
        if ($tahun) {
            $builder->where('k.tahun', $tahun);
        }
        
        $builder->groupBy('k.bulan, k.tahun, s.nama');
        $builder->orderBy('k.tahun', 'DESC');
        $builder->orderBy('k.bulan', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Mendapatkan data pegawai berdasarkan ID
     */
    public function getPegawaiById($pegawai_id)
    {
        $builder = $this->db->table('pegawai p');
        $builder->select('p.*, s.nama as satker_nama');
        $builder->join('riwayat_mutasi rm', 'rm.pegawai_id = p.id AND rm.tanggal_selesai IS NULL', 'left');
        $builder->join('satker s', 's.id = rm.satker_id', 'left');
        $builder->where('p.id', $pegawai_id);
        $builder->where('p.status', 'aktif');
        
        return $builder->get()->getRowArray();
    }

    /**
     * Mendapatkan statistik pelanggaran per jenis
     */
    public function getStatistikPelanggaran($pegawai_id, $tahun = null)
    {
        $builder = $this->db->table('kedisiplinan k');
        $builder->select('
            SUM(k.terlambat) as terlambat,
            SUM(k.tidak_absen_masuk) as tidak_absen_masuk,
            SUM(k.pulang_awal) as pulang_awal,
            SUM(k.tidak_absen_pulang) as tidak_absen_pulang,
            SUM(k.keluar_tidak_izin) as keluar_tidak_izin,
            SUM(k.tidak_masuk_tanpa_ket) as tidak_masuk_tanpa_ket,
            SUM(k.tidak_masuk_sakit) as tidak_masuk_sakit,
            SUM(k.tidak_masuk_kerja) as tidak_masuk_kerja
        ');
        $builder->where('k.pegawai_id', $pegawai_id);
        
        if ($tahun) {
            $builder->where('k.tahun', $tahun);
        }
        
        return $builder->get()->getRowArray();
    }

    /**
     * Mendapatkan daftar tahun yang tersedia untuk tracking
     */
    public function getTahunTersedia($pegawai_id = null)
    {
        $builder = $this->db->table('kedisiplinan k');
        $builder->select('k.tahun');
        $builder->distinct();
        $builder->orderBy('k.tahun', 'DESC');
        
        if ($pegawai_id) {
            $builder->where('k.pegawai_id', $pegawai_id);
        }
        
        return $builder->get()->getResultArray();
    }
}
