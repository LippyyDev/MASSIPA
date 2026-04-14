<?php
namespace App\Models\Api;

use CodeIgniter\Model;

class AllowedOriginModel extends Model
{
    protected $table = 'allowed_origins';
    protected $primaryKey = 'id';
    protected $allowedFields = ['origin', 'is_active', 'created_at'];
    public $timestamps = false;

    public function isAllowed($origin)
    {
        return $this->where('origin', $origin)->where('is_active', 1)->first() !== null;
    }

    public function isAllowedOrigin($origin)
    {
        if (empty($origin)) {
            return false;
        }
        
        $normalizedOrigin = rtrim(trim($origin), '/');
        
        if (empty($normalizedOrigin)) {
            return false;
        }
        
        $allOrigins = $this->where('is_active', 1)->findAll();
        foreach ($allOrigins as $allowed) {
            $allowedOrigin = rtrim(trim($allowed['origin']), '/');
            if (!empty($allowedOrigin) && $allowedOrigin === $normalizedOrigin) {
                return true;
            }
        }
        
        return false;
    }

    public function getAllActive()
    {
        return $this->where('is_active', 1)->findAll();
    }
}