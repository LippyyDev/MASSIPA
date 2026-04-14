<?php
namespace App\Models\User;

use CodeIgniter\Model;

class RekapLaporanDisiplinModel extends Model
{
    protected $table = 'kedisiplinan';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'pegawai_id', 'bulan', 'tahun', 'terlambat', 'tidak_absen_masuk', 'pulang_awal', 'tidak_absen_pulang', 'keluar_tidak_izin', 'tidak_masuk_tanpa_ket', 'tidak_masuk_sakit', 'tidak_masuk_kerja', 'bentuk_pembinaan', 'keterangan', 'created_by'
    ];
} 