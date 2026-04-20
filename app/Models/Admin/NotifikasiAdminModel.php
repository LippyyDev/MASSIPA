<?php
namespace App\Models\Admin;

use CodeIgniter\Model;

class NotifikasiAdminModel extends Model
{
    protected $table = 'notifikasi';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'judul', 'pesan', 'jenis', 'referensi_id', 'is_read', 'created_at'];
    public $timestamps = false;

    // Tambahkan method custom jika perlu
    public function insertNotifikasi($user_id, $judul, $pesan, $jenis, $referensi_id = null)
    {
        $result = $this->insert([
            'user_id'      => $user_id,
            'judul'        => $judul,
            'pesan'        => $pesan,
            'jenis'        => $jenis,
            'referensi_id' => $referensi_id,
            'is_read'      => 0,
            'created_at'   => date('Y-m-d H:i:s')
        ]);

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