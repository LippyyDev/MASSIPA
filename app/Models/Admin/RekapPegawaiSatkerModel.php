<?php
namespace App\Models\Admin;

use CodeIgniter\Model;

class RekapPegawaiSatkerModel extends Model
{
    protected $table = 'kedisiplinan'; // default, bisa diubah sesuai kebutuhan
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'pegawai_id', 'bulan', 'tahun', 'terlambat', 'keluar_tidak_izin', 'tidak_absen_masuk', 'tidak_masuk_tanpa_ket', 'pulang_awal', 'tidak_masuk_sakit', 'tidak_absen_pulang', 'tidak_masuk_kerja', 'bentuk_pembinaan', 'keterangan'
    ];
    // Tambahkan konfigurasi lain jika diperlukan
} 