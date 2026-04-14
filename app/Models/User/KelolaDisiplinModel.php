<?php
namespace App\Models\User;

use CodeIgniter\Model;

class KelolaDisiplinModel extends Model
{
    protected $table = 'kedisiplinan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'pegawai_id', 'bulan', 'tahun', 'terlambat', 'tidak_absen_masuk', 'pulang_awal', 'tidak_absen_pulang', 'keluar_tidak_izin', 'tidak_masuk_tanpa_ket', 'tidak_masuk_sakit', 'tidak_masuk_kerja', 'bentuk_pembinaan', 'keterangan', 'created_by'
    ];

    public function getKedisiplinanByPegawaiIds($pegawai_ids, $bulan, $tahun, $created_by)
    {
        return $this->whereIn('pegawai_id', $pegawai_ids)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->where('created_by', $created_by)
            ->findAll();
    }

    public function getKedisiplinanById($id)
    {
        return $this->find($id);
    }

    public function addKedisiplinan($data)
    {
        return $this->insert($data);
    }

    public function updateKedisiplinan($id, $data)
    {
        return $this->update($id, $data);
    }

    public function deleteKedisiplinan($id)
    {
        return $this->delete($id);
    }

    public function deleteKedisiplinanPeriode($bulan, $tahun, $created_by)
    {
        return $this->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->where('created_by', $created_by)
            ->delete();
    }
} 