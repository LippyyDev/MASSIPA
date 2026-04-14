<?php
namespace App\Models\Admin;

use CodeIgniter\Model;

class PengaturanAdminModel extends Model
{
    // --- API KEY ---
    protected $apiKeyTable = 'api_keys';
    protected $allowedOriginTable = 'allowed_origins';

    // API KEY CRUD
    public function getAllApiKeys() {
        return $this->db->table($this->apiKeyTable)->get()->getResultArray();
    }
    public function insertApiKey($data) {
        return $this->db->table($this->apiKeyTable)->insert($data);
    }
    public function deleteApiKey($id) {
        return $this->db->table($this->apiKeyTable)->delete(['id' => $id]);
    }
    public function toggleApiKey($id) {
        $row = $this->db->table($this->apiKeyTable)->where('id', $id)->get()->getRowArray();
        if ($row) {
            $newStatus = $row['is_active'] ? 0 : 1;
            $this->db->table($this->apiKeyTable)->where('id', $id)->update(['is_active' => $newStatus]);
            return $newStatus;
        }
        return false;
    }
    // --- ALLOWED ORIGIN ---
    public function getAllOrigins() {
        return $this->db->table($this->allowedOriginTable)->get()->getResultArray();
    }
    public function insertOrigin($data) {
        return $this->db->table($this->allowedOriginTable)->insert($data);
    }
    public function deleteOrigin($id) {
        return $this->db->table($this->allowedOriginTable)->delete(['id' => $id]);
    }
    public function toggleOrigin($id) {
        $row = $this->db->table($this->allowedOriginTable)->where('id', $id)->get()->getRowArray();
        if ($row) {
            $newStatus = $row['is_active'] ? 0 : 1;
            $this->db->table($this->allowedOriginTable)->where('id', $id)->update(['is_active' => $newStatus]);
            return $newStatus;
        }
        return false;
    }
} 