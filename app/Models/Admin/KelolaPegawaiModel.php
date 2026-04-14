<?php

namespace App\Models\Admin;

use CodeIgniter\Model;

class KelolaPegawaiModel extends Model
{
    protected $table = 'pegawai';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama', 'nip', 'pangkat', 'golongan', 'jabatan', 'status'];
    protected $useTimestamps = false;

    // Ambil semua pegawai dengan join satker aktif
    public function getAllWithSatker()
    {
        return $this->select('pegawai.*, satker.nama as satker_nama')
            ->join('riwayat_mutasi', 'riwayat_mutasi.pegawai_id = pegawai.id AND riwayat_mutasi.tanggal_selesai IS NULL', 'left')
            ->join('satker', 'satker.id = riwayat_mutasi.satker_id', 'left')
            ->orderBy('pegawai.nama', 'ASC')
            ->findAll();
    }

    // Untuk AJAX DataTables (filter, search, dsb)
    public function getFilteredPegawai($search = '', $satker = '', $golongan = '', $jabatan = '', $start = 0, $length = 10)
    {
        $builder = $this->select('pegawai.*, satker.nama as satker_nama, riwayat_mutasi.tanggal_mulai')
            ->join('riwayat_mutasi', 'riwayat_mutasi.pegawai_id = pegawai.id AND riwayat_mutasi.tanggal_selesai IS NULL', 'left')
            ->join('satker', 'satker.id = riwayat_mutasi.satker_id', 'left');
        if (!empty($search)) {
            $builder->groupStart()
                ->like('pegawai.nama', $search)
                ->orLike('pegawai.nip', $search)
                ->groupEnd();
        }
        if (!empty($satker)) {
            $builder->where('satker.nama', $satker);
        }
        if (!empty($golongan)) {
            $builder->where('pegawai.golongan', $golongan);
        }
        if (!empty($jabatan)) {
            $builder->where('pegawai.jabatan', $jabatan);
        }
        $recordsFiltered = $builder->countAllResults(false);
        $data = $builder->limit($length, $start)->findAll();
        return [$data, $recordsFiltered];
    }

    // Tambah pegawai
    public function addPegawai($data)
    {
        return $this->insert($data);
    }

    // Update pegawai
    public function updatePegawai($id, $data)
    {
        return $this->update($id, $data);
    }

    // Hapus pegawai
    public function deletePegawai($id)
    {
        return $this->delete($id);
    }

    // Ambil satu pegawai
    public function getPegawai($id)
    {
        return $this->find($id);
    }

    // Ubah status pegawai
    public function setStatus($id, $status)
    {
        return $this->update($id, ['status' => $status]);
    }

    // Cek NIP unik
    public function isNipUnique($nip, $excludeId = null)
    {
        $builder = $this->where('nip', $nip);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        return $builder->first() === null;
    }
} 