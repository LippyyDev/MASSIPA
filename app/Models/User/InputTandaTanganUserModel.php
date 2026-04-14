<?php
namespace App\Models\User;

use CodeIgniter\Model;

class InputTandaTanganUserModel extends Model
{
    protected $table = 'tanda_tangan';
    protected $primaryKey = 'id';
    protected $allowedFields = ['lokasi', 'tanggal', 'nama_jabatan', 'nama_penandatangan', 'nip_penandatangan', 'user_id', 'is_aktif'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getTandaTanganByUser($user_id)
    {
        return $this->where('user_id', $user_id)->orderBy('id', 'DESC')->findAll();
    }

    public function setActive($id, $user_id)
    {
        $this->where('user_id', $user_id)->set(['is_aktif' => 0])->update();
        return $this->update($id, ['is_aktif' => 1]);
    }
} 