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
            log_message('error', 'Exception queue email notifikasi (admin): ' . $th->getMessage());
        }
    }
} 