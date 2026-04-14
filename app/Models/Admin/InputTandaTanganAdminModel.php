<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class InputTandaTanganAdminModel extends Model
{
    protected $table;
    protected $primaryKey = 'id';
    protected $allowedFields;
    protected $useTimestamps = false;

    public function setTableTandaTangan()
    {
        $this->table = 'tanda_tangan';
        $this->allowedFields = ['lokasi', 'tanggal', 'nama_jabatan', 'nama_penandatangan', 'nip_penandatangan', 'is_aktif', 'user_id'];
    }
    public function setTableTandaTanganGambar()
    {
        $this->table = 'tanda_tangan_gambar';
        $this->allowedFields = ['tempat', 'tanggal', 'file_path', 'is_aktif', 'user_id'];
    }
} 