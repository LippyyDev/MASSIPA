<?php

namespace App\Commands;

use App\Libraries\EmailQueueProcessor;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SendEmailQueue extends BaseCommand
{
    protected $group       = 'Email';
    protected $name        = 'email:send-queue';
    protected $description = 'Kirim email yang ada di antrean email_queue.';

    public function run(array $params)
    {
        $limit = (int) ($params[0] ?? 20);
        $limit = $limit > 0 ? $limit : 20;

        $processor = new EmailQueueProcessor();
        $sent = $processor->process($limit);

        if ($sent <= 0) {
            CLI::write('Tidak ada email yang dikirim (antrean kosong atau masih diproses).', 'yellow');
            return;
        }

        CLI::write("Selesai. Terkirim: {$sent}", 'green');
    }
}

