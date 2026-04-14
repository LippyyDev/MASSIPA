<?php

namespace App\Models;

use CodeIgniter\Model;

class TandaTanganModel extends Model
{
    protected $table = 'tanda_tangan';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['lokasi', 'tanggal', 'nama_jabatan', 'nama_penandatangan', 'nip_penandatangan', 'user_id', 'is_aktif'];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Aktifkan tanda tangan tertentu dan nonaktifkan yang lain milik user yang sama
     */
    public function setActive($id, $user_id)
    {
        // Nonaktifkan semua tanda tangan milik user
        $this->where('user_id', $user_id)->set(['is_aktif' => 0])->update();
        // Aktifkan tanda tangan yang dipilih
        return $this->update($id, ['is_aktif' => 1]);
    }
}


