<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

class EmailQueueProcessor
{
    private BaseConnection $db;
    private int $purgeAfterSeconds = 3600; // 1 jam

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * Ambil batch email pending secara aman (claim) lalu kirim.
     * Menggunakan kolom processing_token/processing_at untuk mencegah double-send.
     */
    public function process(int $limit = 10): int
    {
        $limit = $limit > 0 ? $limit : 10;
        $token = bin2hex(random_bytes(12));

        // Claim batch: tandai sebagai processing (is_sent=2) dan beri token.
        // Jika ada job "stuck" (>10 menit), boleh dire-claim.
        $sql = "
            UPDATE email_queue
            SET is_sent = 2,
                processing_token = ?,
                processing_at = NOW(),
                updated_at = NOW()
            WHERE is_sent = 0
              AND fail_count < 5
              AND (processing_token IS NULL OR processing_at < (NOW() - INTERVAL 10 MINUTE))
            ORDER BY id ASC
            LIMIT {$limit}
        ";
        $this->db->query($sql, [$token]);

        $rows = $this->db->table('email_queue')
            ->where('processing_token', $token)
            ->where('is_sent', 2)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($rows)) {
            return 0;
        }

        $sentCount = 0;
        $emailService = service('email');
        $config = config('Email');

        foreach ($rows as $row) {
            try {
                $emailService->clear(true);
                $emailService->setFrom(
                    $config->fromEmail ?: ($config->SMTPUser ?? 'no-reply@example.com'),
                    $config->fromName ?: 'MASSIPA'
                );
                $emailService->setTo($row['recipient']);
                $emailService->setSubject($row['subject']);
                $emailService->setMessage($row['body']);

                if ($emailService->send()) {
                    $this->db->table('email_queue')
                        ->where('id', $row['id'])
                        ->where('processing_token', $token)
                        ->update([
                            'is_sent'          => 1,
                            'sent_at'          => date('Y-m-d H:i:s'),
                            'last_error'       => null,
                            'processing_token' => null,
                            'processing_at'    => null,
                            'updated_at'       => date('Y-m-d H:i:s'),
                        ]);
                    $sentCount++;
                } else {
                    $this->markFailed((int) $row['id'], $token, (string) $emailService->printDebugger(['headers']));
                }
            } catch (\Throwable $th) {
                $this->markFailed((int) $row['id'], $token, $th->getMessage());
            }
        }

        // Bersihkan email yang sudah terkirim lebih dari 1 jam agar tabel tidak penuh
        $this->purgeSentOlderThan($this->purgeAfterSeconds);

        return $sentCount;
    }

    /**
     * Hapus email yang sudah terkirim dan berumur lebih dari $seconds.
     */
    public function purgeSentOlderThan(int $seconds = 3600): int
    {
        $seconds = $seconds > 0 ? $seconds : 3600;
        $cutoff = date('Y-m-d H:i:s', time() - $seconds);

        $this->db->table('email_queue')
            ->where('is_sent', 1)
            ->where('sent_at IS NOT NULL', null, false)
            ->where('sent_at <', $cutoff)
            ->delete();

        return (int) $this->db->affectedRows();
    }

    private function markFailed(int $id, string $token, string $error): void
    {
        // Reset ke pending, naikkan fail_count.
        $this->db->query(
            "UPDATE email_queue
             SET is_sent = 0,
                 fail_count = fail_count + 1,
                 last_error = ?,
                 processing_token = NULL,
                 processing_at = NULL,
                 updated_at = NOW()
             WHERE id = ? AND processing_token = ?",
            [$error, $id, $token]
        );
    }
}


