<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\KelolaSatkerModel;

class KelolaSatkerController extends BaseController
{
    protected $satkerModel;

    public function __construct()
    {
        $this->satkerModel = new KelolaSatkerModel();
        helper(["form,url, sion", "app_helper"]);
    }

    public function kelolaSatker()
    {
        $list_satker = $this->satkerModel->getAllSatker();
        $edit_satker = null;
        if ($this->request->getGet('edit')) {
            $edit_satker = $this->satkerModel->getSatkerById($this->request->getGet('edit'));
        }
        return view('admin/KelolaSatker', [
            'list_satker' => $list_satker,
            'edit_satker' => $edit_satker
        ]);
    }

    public function simpanSatker()
    {
        $id = $this->request->getPost('id');
        $nama = $this->request->getPost('nama');
        $alamat = $this->request->getPost('alamat');
        
        // Validasi nama unik
        $exists = $this->satkerModel->checkSatkerExists($nama, $id);
        if ($exists) {
            return redirect()->to(base_url('admin/kelola_satker'))->with('error', 'Nama satker sudah ada!');
        }
        
        $data = [
            'nama' => $nama,
            'alamat' => $alamat
        ];
        
        if ($id) {
            $this->satkerModel->updateSatker($id, $data);
            return redirect()->to(base_url('admin/kelola_satker'))->with('success', 'Satker berhasil diupdate!');
        } else {
            $this->satkerModel->addSatker($data);
            return redirect()->to(base_url('admin/kelola_satker'))->with('success', 'Satker berhasil ditambah!');
        }
    }

    public function hapusSatker($id)
    {
        if ($this->satkerModel->deleteSatker($id)) {
            return redirect()->to(base_url('admin/kelola_satker'))->with('success', 'Satker berhasil dihapus!');
        } else {
            return redirect()->to(base_url('admin/kelola_satker'))->with('error', 'Gagal menghapus satker!');
        }
    }
} 