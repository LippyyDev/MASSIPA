<?php

namespace App\Models;

use CodeIgniter\Model;

class KedisiplinanModel extends Model
{
    protected $table = 'kedisiplinan';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'pegawai_id',
        'riwayat_mutasi_id',
        'bulan',
        'tahun',
        'terlambat',
        'tidak_absen_masuk',
        'pulang_awal',
        'tidak_absen_pulang',
        'keluar_tidak_izin',
        'tidak_masuk_tanpa_ket',
        'tidak_masuk_sakit',
        'tidak_masuk_kerja',
        'bentuk_pembinaan',
        'keterangan',
        'created_by'
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];
}


