<?php
namespace App\Models;
use CodeIgniter\Model;

class RiwayatMutasiModel extends Model
{
    protected $table = 'riwayat_mutasi';
    protected $primaryKey = 'id';
    protected $allowedFields = ['pegawai_id', 'satker_id', 'tanggal_mulai', 'tanggal_selesai'];
    public $timestamps = false;
}