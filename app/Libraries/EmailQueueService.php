<?php

namespace App\Libraries;

/**
 * EmailQueueService
 *
 * Service bersama (shared) untuk memasukkan email notifikasi
 * ke tabel email_queue. Dipanggil oleh NotifikasiModel,
 * NotifikasiAdminModel, dan controller manapun yang butuh
 * mengirim email transaksional melalui mekanisme queue.
 *
 * Email yang sudah masuk queue akan diproses secara asinkron
 * oleh EmailQueuePumpFilter + EmailQueueProcessor di setiap
 * HTTP request berikutnya (register_shutdown_function pattern).
 */
class EmailQueueService
{
    /**
     * Antrikan email notifikasi untuk user tertentu.
     *
     * @param  int    $userId   ID user penerima (dari tabel users)
     * @param  string $subject  Judul/subjek email
     * @param  string $message  Isi pesan (plain text, akan di-render ke HTML)
     * @return bool   True jika berhasil dimasukkan ke antrian, false jika tidak
     */
    public static function queueForUser(int $userId, string $subject, string $message): bool
    {
        try {
            $userModel = new \App\Models\UserModel();
            $user      = $userModel->find($userId);

            if (! $user || empty($user['email'])) {
                return false;
            }

            return self::queueToEmail(
                $user['email'],
                $user['nama_lengkap'] ?? $user['username'] ?? 'Pengguna',
                $subject,
                $message
            );
        } catch (\Throwable $th) {
            log_message('error', '[EmailQueueService] queueForUser gagal: ' . $th->getMessage());
            return false;
        }
    }

    /**
     * Antrikan email langsung ke alamat email tertentu
     * (tanpa lookup user — digunakan untuk lupa password, dll).
     *
     * @param  string $email      Alamat email penerima
     * @param  string $recipient  Nama tampilan penerima (untuk salam di template)
     * @param  string $subject    Subjek email
     * @param  string $body       HTML body email (sudah di-render sepenuhnya)
     * @return bool
     */
    public static function queueRaw(string $email, string $subject, string $body): bool
    {
        try {
            $queueModel = new \App\Models\EmailQueueModel();
            $result = $queueModel->insert([
                'recipient'  => $email,
                'subject'    => $subject,
                'body'       => $body,
                'is_sent'    => 0,
                'fail_count' => 0,
            ]);

            return (bool) $result;
        } catch (\Throwable $th) {
            log_message('error', '[EmailQueueService] queueRaw gagal: ' . $th->getMessage());
            return false;
        }
    }

    /**
     * Internal helper: render template notifikasi lalu insert ke email_queue.
     */
    private static function queueToEmail(
        string $email,
        string $recipientName,
        string $subject,
        string $message
    ): bool {
        $queueModel = new \App\Models\EmailQueueModel();
        $body = view('emails/notification', [
            'recipient' => $recipientName,
            'subject'   => $subject,
            'message'   => $message,
        ]);

        $result = $queueModel->insert([
            'recipient'  => $email,
            'subject'    => $subject,
            'body'       => $body,
            'is_sent'    => 0,
            'fail_count' => 0,
        ]);

        return (bool) $result;
    }
}
