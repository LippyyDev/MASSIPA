<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class StatusDisiplinSatkerModel extends Model
{
    protected $table = 'laporan_file';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nama_laporan', 'file_path', 'bulan', 'tahun', 'created_by', 'keterangan', 'link_drive', 'kategori', 'created_at', 'status', 'feedback', 'updated_at', 'rejected_at'
    ];
    protected $useTimestamps = false;
} 