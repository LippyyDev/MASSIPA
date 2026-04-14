<?php
namespace App\Models\User;

use CodeIgniter\Model;

class BerandaUserModel extends Model
{
    protected $table = 'pegawai'; // default, bisa diubah sesuai kebutuhan statistik
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nama', 'nip', 'pangkat', 'golongan', 'jabatan', 'status', 'satker_id'
    ];
} 