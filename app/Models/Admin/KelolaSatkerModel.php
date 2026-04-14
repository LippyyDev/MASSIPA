<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class KelolaSatkerModel extends Model
{
    protected $table = 'satker';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama', 'alamat'];
    protected $useTimestamps = false;
    // protected $createdField = 'created_at';
    // protected $updatedField = 'updated_at';

    public function getAllSatker()
    {
        return $this->orderBy('nama', 'ASC')->findAll();
    }

    public function getSatkerById($id)
    {
        return $this->find($id);
    }

    public function addSatker($data)
    {
        return $this->insert($data);
    }

    public function updateSatker($id, $data)
    {
        return $this->update($id, $data);
    }

    public function deleteSatker($id)
    {
        return $this->delete($id);
    }

    public function checkSatkerExists($nama, $excludeId = null)
    {
        $query = $this->where('nama', $nama);
        if ($excludeId) {
            $query->where('id !=', $excludeId);
        }
        return $query->first();
    }
} 