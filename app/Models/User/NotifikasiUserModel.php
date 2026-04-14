<?php
namespace App\Models\User;

use CodeIgniter\Model;

class NotifikasiUserModel extends Model
{
    protected $table = 'notifikasi';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'judul', 'pesan', 'jenis', 'referensi_id', 'is_read', 'created_at'
    ];
} 