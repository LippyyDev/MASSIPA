<?php
namespace App\Models\User;

use CodeIgniter\Model;

class InputTandaTanganGambarUserModel extends Model
{
    protected $table = 'tanda_tangan_gambar';
    protected $primaryKey = 'id';
    protected $allowedFields = ['tempat', 'tanggal', 'file_path', 'is_aktif', 'user_id', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getTandaTanganGambarByUser($user_id)
    {
        return $this->where('user_id', $user_id)->orderBy('id', 'DESC')->findAll();
    }

    public function setActive($id, $user_id)
    {
        $this->where('user_id', $user_id)->set(['is_aktif' => 0])->update();
        return $this->update($id, ['is_aktif' => 1]);
    }
} 