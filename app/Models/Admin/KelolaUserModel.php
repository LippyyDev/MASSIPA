<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class KelolaUserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['username', 'password', 'nama_lengkap', 'email', 'role', 'satker_id'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Get all users with satker info
    public function getAllUsersWithSatker()
    {
        return $this->select('users.*, satker.nama as nama_satker')
            ->join('satker', 'satker.id = users.satker_id', 'left')
            ->findAll();
    }

    // Get user by ID
    public function getUserById($id)
    {
        return $this->find($id);
    }

    // Add new user
    public function addUser($data)
    {
        return $this->insert($data);
    }

    // Update user
    public function updateUser($id, $data)
    {
        return $this->update($id, $data);
    }

    // Delete user
    public function deleteUser($id)
    {
        return $this->delete($id);
    }

    // Check if username exists (excluding current user for update)
    public function isUsernameUnique($username, $excludeId = null)
    {
        $builder = $this->where('username', $username);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->first() === null;
    }

    // Check if email exists (excluding current user for update)
    public function isEmailUnique($email, $excludeId = null)
    {
        $builder = $this->where('email', $email);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->first() === null;
    }

    // Check if satker is already used by another user
    public function isSatkerUsedByOtherUser($satkerId, $excludeUserId = null)
    {
        $builder = $this->where('satker_id', $satkerId);
        if ($excludeUserId) {
            $builder->where('id !=', $excludeUserId);
        }
        return $builder->first();
    }

    // Hash password
    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    // Get user by satker ID
    public function getUserBySatkerId($satkerId, $excludeUserId = null)
    {
        $builder = $this->where('satker_id', $satkerId);
        if ($excludeUserId) {
            $builder->where('id !=', $excludeUserId);
        }
        return $builder->first();
    }

    // Count total admin accounts
    public function countAdmins()
    {
        return $this->where('role', 'admin')->countAllResults();
    }
} 