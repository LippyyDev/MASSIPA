<?php
namespace App\Models;
use CodeIgniter\Model;

class LandingPageModel extends Model
{
    // Tidak perlu $table karena hanya untuk query statistik agregat

    public function getStatistik()
    {
        $db = \Config\Database::connect();
        $stat = [];
        $stat['satker'] = $db->table('satker')->countAllResults();
        $stat['pegawai'] = $db->table('pegawai')->countAllResults();
        $stat['laporan'] = $db->table('kedisiplinan')->countAllResults();
        $stat['mutasi'] = $db->table('riwayat_mutasi')
            ->select('pegawai_id')
            ->groupBy('pegawai_id')
            ->having('COUNT(pegawai_id) >=', 2)
            ->countAllResults();
        return $stat;
    }
} 