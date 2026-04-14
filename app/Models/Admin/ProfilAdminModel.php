<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class ProfilAdminModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['username', 'nama_lengkap', 'email', 'password', 'foto_profil'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validasi untuk update profil
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[20]',
        'nama_lengkap' => 'required|min_length[3]|max_length[100]',
        'email' => 'required|valid_email',
    ];

    // Get user by ID
    public function getUserById($id)
    {
        return $this->find($id);
    }

    // Update user profile
    public function updateProfile($id, $data)
    {
        return $this->update($id, $data);
    }

    // Update profile photo
    public function updateProfilePhoto($id, $foto_profil)
    {
        return $this->update($id, ['foto_profil' => $foto_profil]);
    }

    // Check if username exists (excluding current user)
    public function isUsernameUnique($username, $excludeId = null)
    {
        $builder = $this->where('username', $username);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->first() === null;
    }

    // Check if email exists (excluding current user)
    public function isEmailUnique($email, $excludeId = null)
    {
        $builder = $this->where('email', $email);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->first() === null;
    }

    // Verify password
    public function verifyPassword($id, $password)
    {
        $user = $this->find($id);
        if (!$user) {
            return false;
        }
        return password_verify($password, $user['password']);
    }

    // Hash password
    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
} 