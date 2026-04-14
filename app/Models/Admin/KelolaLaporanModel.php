<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class KelolaLaporanModel extends Model
{
    protected $table = 'laporan_file';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nama_laporan', 'file_path', 'bulan', 'tahun', 'created_by', 'keterangan', 'link_drive', 'kategori', 'created_at', 'status', 'feedback', 'updated_at', 'rejected_at'
    ];
    protected $useTimestamps = false;

    // Untuk AJAX DataTables (filter, search, dsb)
    public function getFilteredLaporan($search = '', $user_id = '', $bulan = '', $tahun = '', $kategori = '', $start = 0, $length = 10)
    {
        $builder = $this->select('laporan_file.*, users.nama_lengkap')
            ->join('users', 'users.id = laporan_file.created_by', 'left')
            ->whereNotIn('laporan_file.status', ['diterima']); // Exclude laporan yang sudah diterima
        
        if (!empty($search)) {
            $builder->groupStart()
                ->like('laporan_file.nama_laporan', $search)
                ->orLike('users.nama_lengkap', $search)
                ->orLike('laporan_file.keterangan', $search)
                ->groupEnd();
        }
        
        if (!empty($user_id)) {
            $builder->where('laporan_file.created_by', $user_id);
        }
        
        if (!empty($bulan)) {
            $builder->where('laporan_file.bulan', $bulan);
        }
        
        if (!empty($tahun)) {
            $builder->where('laporan_file.tahun', $tahun);
        }
        
        if (!empty($kategori)) {
            $builder->where('laporan_file.kategori', $kategori);
        }
        
        $recordsFiltered = $builder->countAllResults(false);
        $data = $builder->orderBy('laporan_file.created_at', 'DESC')
            ->limit($length, $start)
            ->findAll();
        
        return [$data, $recordsFiltered];
    }
} 