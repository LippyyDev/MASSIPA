<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\MutasiPegawaiModel;
use App\Models\SatkerModel;
use App\Models\PegawaiModel;

class MutasiPegawaiController extends BaseController
{
    protected $mutasiModel;
    protected $satkerModel;
    protected $pegawaiModel;

    public function __construct()
    {
        $this->mutasiModel = new MutasiPegawaiModel();
        $this->satkerModel = new SatkerModel();
        $this->pegawaiModel = new PegawaiModel();
        helper(["form", "url", "session", "app_helper"]);
    }

    public function mutasiPegawai($id)
    {
        $pegawai = $this->pegawaiModel->find($id);
        $list_satker = $this->satkerModel->findAll();
        $riwayat = $this->mutasiModel->where('pegawai_id', $id)->orderBy('tanggal_mulai', 'ASC')->findAll();
        $data = [
            'pegawai' => $pegawai,
            'list_satker' => $list_satker,
            'riwayat' => $riwayat,
            'active' => 'admin/pegawai',
            'notif_count' => null, // opsional, bisa diisi jika perlu
        ];
        return view('admin/MutasiPegawai', $data);
    }

    public function prosesMutasiPegawai()
    {
        $pegawai_id = $this->request->getPost('pegawai_id');
        $satker_id = $this->request->getPost('satker_id');
        $tanggal_mutasi = $this->request->getPost('tanggal_mutasi');
        // Tutup mutasi lama
        $this->mutasiModel->where('pegawai_id', $pegawai_id)->where('tanggal_selesai', null)->set(['tanggal_selesai' => $tanggal_mutasi])->update();
        // Tambah mutasi baru
        $this->mutasiModel->insert([
            'pegawai_id' => $pegawai_id,
            'satker_id' => $satker_id,
            'tanggal_mulai' => $tanggal_mutasi
        ]);
        return redirect()->to(base_url('admin/mutasiPegawai/' . $pegawai_id))->with('msg', 'Mutasi pegawai berhasil!')->with('msg_type', 'success');
    }

    public function updateMutasiPegawai()
    {
        $mutasi_id = $this->request->getPost('mutasi_id');
        $pegawai_id = $this->request->getPost('pegawai_id');
        $satker_id = $this->request->getPost('satker_id');
        $tanggal_mulai = $this->request->getPost('tanggal_mulai');
        $tanggal_selesai = $this->request->getPost('tanggal_selesai');
        $data = [
            'satker_id' => $satker_id,
            'tanggal_mulai' => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai ? $tanggal_selesai : null,
        ];
        $this->mutasiModel->update($mutasi_id, $data);
        return redirect()->to(base_url('admin/mutasiPegawai/' . $pegawai_id))->with('msg', 'Data mutasi berhasil diupdate!')->with('msg_type', 'success');
    }

    public function deleteMutasiPegawai($id)
    {
        $mutasi = $this->mutasiModel->find($id);
        if (!$mutasi) {
            return redirect()->back()->with('msg', 'Data mutasi tidak ditemukan!')->with('msg_type', 'danger');
        }
        // Cegah hapus mutasi pertama
        $first = $this->mutasiModel->where('pegawai_id', $mutasi['pegawai_id'])->orderBy('tanggal_mulai', 'ASC')->first();
        if ($first && $first['id'] == $id) {
            return redirect()->back()->with('msg', 'Mutasi pertama tidak boleh dihapus!')->with('msg_type', 'danger');
        }
        // Simpan pegawai_id dan tanggal_mulai sebelum hapus
        $pegawai_id = $mutasi['pegawai_id'];
        $tanggal_mulai = $mutasi['tanggal_mulai'];
        // Hapus mutasi
        $this->mutasiModel->delete($id);
        // Cari mutasi sebelumnya (tanggal_mulai < mutasi yang dihapus, urut DESC)
        $mutasi_sebelumnya = $this->mutasiModel
            ->where('pegawai_id', $pegawai_id)
            ->where('tanggal_mulai <', $tanggal_mulai)
            ->orderBy('tanggal_mulai', 'DESC')
            ->first();
        if ($mutasi_sebelumnya) {
            $this->mutasiModel->update($mutasi_sebelumnya['id'], ['tanggal_selesai' => null]);
        }
        return redirect()->to(base_url('admin/mutasiPegawai/' . $pegawai_id))->with('msg', 'Riwayat mutasi berhasil dihapus!')->with('msg_type', 'success');
    }
} 