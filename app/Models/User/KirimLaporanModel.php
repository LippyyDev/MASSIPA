<?php
namespace App\Models\User;

use CodeIgniter\Model;

class KirimLaporanModel extends Model
{
    protected $table = 'laporan_file';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nama_laporan', 'bulan', 'tahun', 'keterangan', 'link_drive', 'kategori', 'file_path', 'original_filename', 'status', 'created_by', 'feedback', 'is_hidden_by_user', 'created_at'
    ];
    
    // Untuk AJAX DataTables (filter, search, dsb)
    public function getFilteredLaporan($user_id, $search = '', $bulan = '', $tahun = '', $kategori = '', $start = 0, $length = 10)
    {
        $builder = $this->select('laporan_file.*')
            ->where('laporan_file.created_by', $user_id)
            ->where('laporan_file.is_hidden_by_user', 0);
        
        if (!empty($search)) {
            $builder->groupStart()
                ->like('laporan_file.nama_laporan', $search)
                ->orLike('laporan_file.keterangan', $search)
                ->groupEnd();
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