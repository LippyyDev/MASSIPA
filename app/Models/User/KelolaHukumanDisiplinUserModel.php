<?php

namespace App\Models\User;

use CodeIgniter\Model;

class KelolaHukumanDisiplinUserModel extends Model
{
    protected $table = 'hukuman_disiplin';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'pegawai_id', 'user_id', 'jabatan', 'no_sk', 'tanggal_mulai', 'tanggal_berakhir', 'hukuman_dijatuhkan', 'peraturan_dilanggar', 'keterangan', 'file_sk', 'status'
    ];
    protected $useTimestamps = false;
} 