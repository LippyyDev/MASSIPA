<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailQueueModel extends Model
{
    protected $table = 'email_queue';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'recipient',
        'subject',
        'body',
        'is_sent',
        'processing_token',
        'processing_at',
        'fail_count',
        'last_error',
        'sent_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}

