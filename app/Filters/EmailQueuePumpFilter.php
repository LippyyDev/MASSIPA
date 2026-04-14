<?php

namespace App\Filters;

use App\Libraries\EmailQueueProcessor;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class EmailQueuePumpFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Jangan jalan di CLI/testing
        if (is_cli() || ENVIRONMENT === 'testing') {
            return;
        }

        // Throttle sederhana supaya tidak jalan di setiap request secara paralel
        $cache = cache();
        $lockKey = 'email_queue_pump_lock';
        if ($cache && $cache->get($lockKey)) {
            return;
        }
        if ($cache) {
            $cache->save($lockKey, 1, 15); // 15 detik
        }

        // Jalankan setelah response selesai agar tidak memperlambat UX.
        register_shutdown_function(static function () {
            try {
                if (function_exists('fastcgi_finish_request')) {
                    @fastcgi_finish_request();
                }

                $processor = new EmailQueueProcessor();
                // Kirim sedikit per request, sisanya akan diproses request berikutnya
                $processor->process(10);
            } catch (\Throwable $th) {
                log_message('error', 'EmailQueuePumpFilter gagal: ' . $th->getMessage());
            }
        });
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}


