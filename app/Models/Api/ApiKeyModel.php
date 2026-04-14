<?php
namespace App\Models\Api;

use CodeIgniter\Model;

class ApiKeyModel extends Model
{
    protected $table = 'api_keys';
    protected $primaryKey = 'id';
    protected $allowedFields = ['api_key', 'label', 'is_active', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    public function isValid($key)
    {
        return $this->where('api_key', $key)->where('is_active', 1)->first() !== null;
    }

    public function getAllActive()
    {
        return $this->where('is_active', 1)->findAll();
    }
}