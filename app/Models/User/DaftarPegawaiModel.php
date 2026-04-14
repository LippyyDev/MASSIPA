<?php
namespace App\Models\User;

use CodeIgniter\Model;

class DaftarPegawaiModel extends Model
{
    protected $table = 'pegawai';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nama', 'nip', 'pangkat', 'golongan', 'jabatan', 'status', 'satker_id', 'created_by'
    ];

    public function getPegawaiAktifBySatker($satker_id)
    {
        return $this->where('satker_id', $satker_id)->where('status', 'aktif')->findAll();
    }

    public function getPegawaiByIds($ids)
    {
        return $this->whereIn('id', $ids)->findAll();
    }

    public function getPegawaiByNip($nip)
    {
        return $this->where('nip', $nip)->first();
    }

    public function addPegawai($data)
    {
        return $this->insert($data);
    }

    public function updatePegawai($id, $data)
    {
        return $this->update($id, $data);
    }

    public function deletePegawai($id)
    {
        return $this->delete($id);
    }
} 