<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * EmailQueuePumpFilter — DINONAKTIFKAN
 *
 * Fallback ini dimatikan karena email queue sekarang diproses
 * sepenuhnya oleh Cron Job:
 *   php spark email:send-queue 50
 *
 * Aktifkan kembali fallback ini hanya jika cron job tidak tersedia
 * di server Anda.
 */
class EmailQueuePumpFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // DINONAKTIFKAN — email diproses oleh cron job
        return;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}

