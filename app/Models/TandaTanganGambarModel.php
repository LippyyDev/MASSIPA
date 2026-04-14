<?php

namespace App\Models;

use CodeIgniter\Model;

class TandaTanganGambarModel extends Model
{
    protected $table = 'tanda_tangan_gambar';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['tempat', 'tanggal', 'file_path', 'is_aktif', 'user_id', 'created_at', 'updated_at'];

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
     * Aktifkan tanda tangan gambar tertentu dan nonaktifkan yang lain (global/user_id jika ada)
     * Jika nanti ada user_id, tambahkan parameter dan where user_id
     */
    public function setActive($id)
    {
        // Nonaktifkan semua tanda tangan gambar
        $this->set(['is_aktif' => 0])->update();
        // Aktifkan tanda tangan gambar yang dipilih
        return $this->update($id, ['is_aktif' => 1]);
    }
}