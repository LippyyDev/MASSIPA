<?php

namespace App\Models;

use CodeIgniter\Model;

class SatkerModel extends Model
{
    protected $table = 'satker';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama', 'alamat'];
    protected $useTimestamps = false;
}