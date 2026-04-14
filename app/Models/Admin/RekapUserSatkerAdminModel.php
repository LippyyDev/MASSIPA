<?php
namespace App\Models\Admin;

use CodeIgniter\Model;

class RekapUserSatkerAdminModel extends Model
{
    protected $table = 'kedisiplinan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'pegawai_id', 'bulan', 'tahun', 'terlambat', 'tidak_absen_masuk', 'pulang_awal', 'tidak_absen_pulang', 'keluar_tidak_izin', 'tidak_masuk_tanpa_ket', 'tidak_masuk_sakit', 'tidak_masuk_kerja', 'bentuk_pembinaan', 'keterangan', 'created_by'
    ];

    // Method untuk rekap kedisiplinan per satker
    public function getRekapByPegawaiIdsBulanTahun($pegawai_ids, $bulan, $tahun)
    {
        if (empty($pegawai_ids)) return null;
        return $this->select(
            "COUNT(DISTINCT CASE WHEN terlambat > 0 THEN pegawai_id END) as total_t,
            COUNT(DISTINCT CASE WHEN keluar_tidak_izin > 0 THEN pegawai_id END) as total_kti,
            COUNT(DISTINCT CASE WHEN tidak_absen_masuk > 0 THEN pegawai_id END) as total_tam,
            COUNT(DISTINCT CASE WHEN tidak_masuk_tanpa_ket > 0 THEN pegawai_id END) as total_tk,
            COUNT(DISTINCT CASE WHEN pulang_awal > 0 THEN pegawai_id END) as total_pa,
            COUNT(DISTINCT CASE WHEN tidak_masuk_sakit > 0 THEN pegawai_id END) as total_tms,
            COUNT(DISTINCT CASE WHEN tidak_absen_pulang > 0 THEN pegawai_id END) as total_tap,
            COUNT(DISTINCT CASE WHEN tidak_masuk_kerja > 0 THEN pegawai_id END) as total_tmk,
            GROUP_CONCAT(DISTINCT bentuk_pembinaan) as bentuk_pembinaan,
            GROUP_CONCAT(DISTINCT keterangan) as keterangan"
        )
        ->whereIn('pegawai_id', $pegawai_ids)
        ->where('bulan', $bulan)
        ->where('tahun', $tahun)
        ->first();
    }
} 