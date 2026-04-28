<?php

namespace App\Models;

use CodeIgniter\Model;

class NotifikasiModel extends Model
{
    protected $table = 'notifikasi';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['user_id', 'judul', 'pesan', 'jenis', 'referensi_id', 'is_read'];

    protected bool $allowEmptyInserts = false;

    // Aktifkan pengelolaan timestamp otomatis
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

    public function createNotification($user_id, $judul, $pesan, $jenis, $referensi_id)
    {
        $data = [
            'user_id'       => $user_id,
            'judul'         => $judul,
            'pesan'         => $pesan,
            'jenis'         => $jenis,
            'referensi_id'  => $referensi_id,
            'is_read'       => 0,
        ];

        $result = $this->save($data);

        // Kirim email ke pemilik akun jika ada email terdaftar
        if ($result) {
            $this->queueEmailNotification($user_id, $judul, $pesan);
        }

        return $result;
    }

    /**
     * Masukkan email notifikasi ke antrean.
     * Didelegasikan ke EmailQueueService agar tidak ada duplikasi kode.
     */
    protected function queueEmailNotification(int $userId, string $subject, string $message): void
    {
        \App\Libraries\EmailQueueService::queueForUser($userId, $subject, $message);
    }
}