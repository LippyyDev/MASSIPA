<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class MutasiPegawaiModel extends Model
{
    protected $table = 'riwayat_mutasi';
    protected $primaryKey = 'id';
    protected $allowedFields = ['pegawai_id', 'satker_id', 'tanggal_mulai', 'tanggal_selesai'];
    protected $useTimestamps = false;
} 