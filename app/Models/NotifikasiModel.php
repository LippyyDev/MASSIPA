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
     */
    protected function queueEmailNotification(int $userId, string $subject, string $message): void
    {
        try {
            $userModel = new \App\Models\UserModel();
            $user = $userModel->find($userId);

            if (! $user || empty($user['email'])) {
                return;
            }

            $queueModel = new \App\Models\EmailQueueModel();
            $body = view('emails/notification', [
                'recipient' => $user['nama_lengkap'] ?? $user['username'] ?? 'Pengguna',
                'subject'   => $subject,
                'message'   => $message,
            ]);

            $queueModel->insert([
                'recipient'  => $user['email'],
                'subject'    => $subject,
                'body'       => $body,
                'is_sent'    => 0,
                'fail_count' => 0,
            ]);
        } catch (\Throwable $th) {
            log_message('error', 'Exception queue email notifikasi: ' . $th->getMessage());
        }
    }
}