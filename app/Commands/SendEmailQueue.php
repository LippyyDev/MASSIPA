<?php

namespace App\Commands;

use App\Libraries\EmailQueueProcessor;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Spark Command: email:send-queue
 *
 * Dijalankan oleh Cron Job setiap menit untuk memproses email_queue.
 * Menggunakan lock file untuk mencegah overlap jika run sebelumnya
 * belum selesai (SMTP lambat / banyak email).
 *
 * Cron Job (Linux/cPanel) — setiap menit:
 *   * * * * * /usr/bin/php /path/to/spark email:send-queue >> /path/to/writable/logs/cron_email_queue.log 2>&1
 *
 * Manual:
 *   php spark email:send-queue        → proses 50 email
 *   php spark email:send-queue 50 1   → verbose
 */
class SendEmailQueue extends BaseCommand
{
    protected $group       = 'Email';
    protected $name        = 'email:send-queue';
    protected $description = 'Proses email_queue via cron job. Argumen: [limit=50] [verbose=0]';
    protected $usage       = 'email:send-queue [limit] [verbose]';
    protected $arguments   = [
        'limit'   => 'Jumlah email yang diproses per run (default: 50)',
        'verbose' => 'Tampilkan detail: 1=ya, 0=tidak (default: 0)',
    ];

    /** Maksimum waktu lock file dianggap valid (detik). */
    private const LOCK_TTL_SECONDS = 300; // 5 menit

    /** Path lock file — di dalam writable agar tidak ter-commit ke git. */
    private string $lockFile;

    public function __construct($logger, $commands)
    {
        parent::__construct($logger, $commands);
        $this->lockFile = WRITEPATH . 'email_queue.lock';
    }

    public function run(array $params): void
    {
        $limit   = (int) ($params[0] ?? 50);
        $verbose = (bool) ($params[1] ?? false);
        $limit   = $limit > 0 ? $limit : 50;

        $timestamp = date('Y-m-d H:i:s');

        // ── Cek dan pasang lock file ──────────────────────────────────
        if (! $this->acquireLock($timestamp)) {
            if ($verbose) {
                CLI::write("[{$timestamp}] SKIP — run sebelumnya masih berjalan (lock aktif).", 'yellow');
            }
            log_message('info', '[CronJob email:send-queue] SKIP — lock aktif, proses sebelumnya belum selesai.');
            return;
        }

        $startTime = microtime(true);

        if ($verbose) {
            CLI::write("[{$timestamp}] email:send-queue START (limit={$limit})", 'cyan');
        }

        try {
            // Cek apakah ada email pending sebelum proses
            $db      = db_connect();
            $pending = $db->table('email_queue')
                ->where('is_sent', 0)
                ->where('fail_count <', 5)
                ->countAllResults();

            if ($pending === 0) {
                if ($verbose) {
                    CLI::write("[{$timestamp}] Antrian kosong, tidak ada yang diproses.", 'yellow');
                }
                return;
            }

            if ($verbose) {
                CLI::write("[{$timestamp}] Ditemukan {$pending} email pending. Memproses...", 'white');
            }

            $processor = new EmailQueueProcessor();
            $sent      = $processor->process($limit);

            // ── Heartbeat: beri tahu filter bahwa cron masih aktif ────────
            cache()->save('email_queue_last_cron_run', time(), 300); // 5 menit

            $elapsed = round(microtime(true) - $startTime, 2);

            if ($sent > 0) {
                $msg = "[{$timestamp}] Selesai. Terkirim: {$sent} email dalam {$elapsed}s.";
                CLI::write($msg, 'green');
                log_message('info', '[CronJob email:send-queue] ' . $msg);
            } else {
                if ($verbose) {
                    CLI::write("[{$timestamp}] Tidak ada email terkirim (mungkin sedang diklaim run lain).", 'yellow');
                }
            }
        } catch (\Throwable $th) {
            $errMsg = "[{$timestamp}] ERROR: " . $th->getMessage();
            CLI::error($errMsg);
            log_message('error', '[CronJob email:send-queue] ' . $errMsg);
        } finally {
            // ── Selalu lepas lock, bahkan jika error ─────────────────────
            $this->releaseLock();
        }
    }

    /**
     * Coba pasang lock file.
     * Return true jika berhasil (tidak ada proses lain yang jalan).
     * Return false jika ada proses lain yang sedang aktif.
     */
    private function acquireLock(string $timestamp): bool
    {
        if (file_exists($this->lockFile)) {
            $lockedAt = (int) file_get_contents($this->lockFile);
            $age      = time() - $lockedAt;

            if ($age < self::LOCK_TTL_SECONDS) {
                // Lock masih valid → ada proses lain yang sedang jalan
                return false;
            }

            // Lock sudah terlalu lama (stale) → anggap proses sebelumnya crash
            log_message('warning', "[CronJob email:send-queue] [{$timestamp}] Lock stale ({$age}s) → override.");
        }

        // Tulis timestamp ke lock file
        file_put_contents($this->lockFile, time(), LOCK_EX);
        return true;
    }

    /** Hapus lock file setelah selesai. */
    private function releaseLock(): void
    {
        if (file_exists($this->lockFile)) {
            @unlink($this->lockFile);
        }
    }
}
