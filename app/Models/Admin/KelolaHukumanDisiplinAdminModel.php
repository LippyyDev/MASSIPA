<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class KelolaHukumanDisiplinAdminModel extends Model
{
    protected $table = 'hukuman_disiplin';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'pegawai_id', 'user_id', 'jabatan', 'no_sk', 'tanggal_mulai', 'tanggal_berakhir', 'hukuman_dijatuhkan', 'peraturan_dilanggar', 'keterangan', 'file_sk', 'status'
    ];
    protected $useTimestamps = false;
} 