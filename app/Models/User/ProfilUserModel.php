<?php
namespace App\Models\User;

use CodeIgniter\Model;

class ProfilUserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nama_lengkap', 'email', 'username', 'foto_profil', 'password'
    ];

    public function getUserById($user_id)
    {
        return $this->find($user_id);
    }

    public function updateProfil($user_id, $data)
    {
        return $this->update($user_id, $data);
    }

    public function updateFotoProfil($user_id, $foto)
    {
        return $this->update($user_id, ['foto_profil' => $foto]);
    }

    public function updatePassword($user_id, $password_hash)
    {
        return $this->update($user_id, ['password' => $password_hash]);
    }
} 